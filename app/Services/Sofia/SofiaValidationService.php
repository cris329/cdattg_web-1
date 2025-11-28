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
            Log::info("🔍 Validando cédula {$cedula}");

            $startTime = microtime(true);
            $resultado = $this->httpClient->validate($cedula);
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            // Actualizar estado basado en resultado
            $nuevoEstado = $this->stateMapper->mapToState($resultado);
            $aspirante->persona->update(['estado_sofia' => $nuevoEstado]);

            $estadoLabel = $this->stateMapper->getStateLabel($nuevoEstado);
            Log::info("✅ Cédula {$cedula}: {$resultado} -> Estado: {$estadoLabel} (Tiempo: {$duration}s)");

            // Registrar en auditoría
            $resultadoAuditoria = $nuevoEstado === 1 ? 'exitoso' : ($nuevoEstado === 0 ? 'advertencia' : 'exitoso');
            $this->auditoriaService->registrarValidacionSenasofiaplus(
                $aspirante->id,
                $resultadoAuditoria,
                "Validación completada: {$resultado} -> {$estadoLabel}",
                [
                    'cedula' => $cedula,
                    'resultado_api' => $resultado,
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $nuevoEstado,
                    'tiempo_respuesta' => $duration,
                    'complementario_id' => $complementarioId
                ]
            );

            // Actualizar progreso
            if ($progress) {
                $isSuccessful = $nuevoEstado === 1 || $nuevoEstado === 0 || $nuevoEstado === 2;
                $progress->incrementProcessed($isSuccessful);
            }

            return [
                'success' => true,
                'cedula' => $cedula,
                'resultado' => $resultado,
                'estado' => $nuevoEstado,
                'duration' => $duration
            ];

        } catch (\Exception $e) {
            $errorMsg = "❌ Error con cédula {$cedula}: {$e->getMessage()}";
            Log::error($errorMsg, [
                'aspirante_id' => $aspirante->id,
                'persona_id' => $aspirante->persona_id,
                'complementario_id' => $complementarioId,
                'exception' => $e->getTraceAsString()
            ]);

            // Registrar error en auditoría
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

            // Actualizar progreso con error
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
}

