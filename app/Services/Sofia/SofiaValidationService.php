<?php

namespace App\Services\Sofia;

use App\Models\AspiranteComplementario;
use App\Services\AuditoriaService;
use App\Models\SofiaValidationProgress;
use Illuminate\Support\Facades\Log;

class SofiaValidationService
{
    private SofiaHttpClient $httpClient;
    private SofiaStateMapper $stateMapper;
    private AuditoriaService $auditoriaService;

    public function __construct(
        SofiaHttpClient $httpClient,
        SofiaStateMapper $stateMapper,
        AuditoriaService $auditoriaService
    ) {
        $this->httpClient = $httpClient;
        $this->stateMapper = $stateMapper;
        $this->auditoriaService = $auditoriaService;
    }

    /**
     * Validar un aspirante y actualizar su estado
     */
    public function validateAspirante(
        AspiranteComplementario $aspirante,
        int $complementarioId,
        ?SofiaValidationProgress $progress = null
    ): array {
        $cedula = $aspirante->persona->numero_documento;
        $estadoAnterior = $aspirante->persona->estado_sofia;

        try {
            Log::info('Validando cedula', ['cedula' => $cedula]);

            $startTime = microtime(true);
            $resultado = $this->httpClient->validate($cedula);
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            $nuevoEstado = $this->stateMapper->mapToState($resultado);
            $aspirante->persona->update(['estado_sofia' => $nuevoEstado]);

            $estadoLabel = $this->stateMapper->getStateLabel($nuevoEstado);
            Log::info('Cedula validada exitosamente', [
                'cedula' => $cedula,
                'resultado' => $resultado,
                'estado' => $estadoLabel,
                'duration' => $duration
            ]);

            $this->registerAuditSuccess(
                $aspirante,
                $cedula,
                $resultado,
                $estadoAnterior,
                $nuevoEstado,
                $estadoLabel,
                $duration,
                $complementarioId
            );

            $this->updateProgress($progress, $nuevoEstado);

            return [
                'success' => true,
                'cedula' => $cedula,
                'resultado' => $resultado,
                'estado' => $nuevoEstado,
                'duration' => $duration
            ];

        } catch (\Exception $e) {
            return $this->handleValidationError(
                $e,
                $aspirante,
                $cedula,
                $complementarioId,
                $progress
            );
        }
    }

    /**
     * Obtener aspirantes que necesitan validación
     */
    public function getAspirantesToValidate(int $complementarioId)
    {
        return AspiranteComplementario::with('persona')
            ->where('complementario_id', $complementarioId)
            ->whereHas('persona', function($query) {
                $query->whereIn('estado_sofia', [0, 2]);
            })
            ->get();
    }

    /**
     * Registrar auditoría de éxito
     */
    private function registerAuditSuccess(
        AspiranteComplementario $aspirante,
        string $cedula,
        string $resultado,
        int $estadoAnterior,
        int $nuevoEstado,
        string $estadoLabel,
        float $duration,
        int $complementarioId
    ): void {
        $resultadoAuditoria = $this->getAuditResult($nuevoEstado);
        $this->auditoriaService->registrarValidacionSenasofiaplus(
            $aspirante->id,
            $resultadoAuditoria,
            "Validacion completada: {$resultado} -> {$estadoLabel}",
            [
                'cedula' => $cedula,
                'resultado_api' => $resultado,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $nuevoEstado,
                'tiempo_respuesta' => $duration,
                'complementario_id' => $complementarioId
            ]
        );
    }

    /**
     * Obtener resultado de auditoría
     */
    private function getAuditResult(int $estado): string
    {
        return match($estado) {
            1 => 'exitoso',
            0 => 'advertencia',
            2 => 'exitoso',
            default => 'advertencia'
        };
    }

    /**
     * Actualizar progreso
     */
    private function updateProgress(?SofiaValidationProgress $progress, int $nuevoEstado): void
    {
        if ($progress) {
            $isSuccessful = in_array($nuevoEstado, [0, 1, 2], true);
            $progress->incrementProcessed($isSuccessful);
        }
    }

    /**
     * Manejar error de validación
     */
    private function handleValidationError(
        \Exception $e,
        AspiranteComplementario $aspirante,
        string $cedula,
        int $complementarioId,
        ?SofiaValidationProgress $progress
    ): array {
        $errorMsg = "Error con cedula {$cedula}: {$e->getMessage()}";
        Log::error('Error validando cedula', [
            'aspirante_id' => $aspirante->id,
            'persona_id' => $aspirante->persona_id,
            'complementario_id' => $complementarioId,
            'cedula' => $cedula,
            'exception' => $e->getTraceAsString()
        ]);

        $this->auditoriaService->registrarValidacionSenasofiaplus(
            $aspirante->id,
            'error',
            $errorMsg,
            [
                'cedula' => $cedula,
                'complementario_id' => $complementarioId,
                'exception_message' => $e->getMessage(),
                'exception_type' => get_class($e)
            ]
        );

        if ($progress) {
            $progress->incrementProcessed(false);
        }

        return [
            'success' => false,
            'cedula' => $cedula,
            'error' => $errorMsg
        ];
    }
}

