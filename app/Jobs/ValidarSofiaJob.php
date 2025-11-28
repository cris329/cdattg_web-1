<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\Sofia\SofiaValidationService;
use App\Services\Sofia\SofiaValidationProcessor;
use App\Models\SofiaValidationProgress;
use Illuminate\Support\Facades\Log;

class ValidarSofiaJob implements ShouldQueue
{
    use Queueable;

    protected $complementarioId;
    protected $userId;
    protected $progressId;

    /**
     * Create a new job instance.
     */
    public function __construct($complementarioId, $userId = null, $progressId = null)
    {
        $this->complementarioId = $complementarioId;
        $this->userId = $userId;
        $this->progressId = $progressId;
    }

    /**
     * Execute the job.
     */
    public function handle(
        SofiaValidationService $validationService,
        SofiaValidationProcessor $processor
    ): void {
        Log::info("🚀 Iniciando validación SenaSofiaPlus para programa: {$this->complementarioId}");

        // Obtener registro de progreso si existe
        $progress = null;
        if ($this->progressId) {
            $progress = SofiaValidationProgress::find($this->progressId);
            if ($progress) {
                $progress->markAsStarted();
                Log::info("📊 Progreso inicializado con ID: {$this->progressId}");
            }
        }

        // Obtener aspirantes que necesitan validación
        $aspirantes = $validationService->getAspirantesToValidate($this->complementarioId);

        if ($aspirantes->isEmpty()) {
            Log::info('ℹ️ No hay aspirantes que necesiten validación.');
            if ($progress) {
                $progress->markAsCompleted();
            }
            return;
        }

        // Procesar validaciones
        $resultado = $processor->processBatch($aspirantes, $this->complementarioId, $progress);

        // Marcar como completado
        if ($progress) {
            if ($resultado['errores'] > 0) {
                Log::warning("⚠️ Validación completada con {$resultado['errores']} errores");
                $progress->markAsFailed($resultado['errores_detalle']);
            } else {
                Log::info("🎉 Validación completada exitosamente");
                $progress->markAsCompleted();
            }
        }

        $tasaExito = $resultado['total'] > 0 
            ? round(($resultado['exitosos'] / $resultado['total']) * 100, 1) 
            : 0;

        Log::info("📊 Resumen final - Total: {$resultado['total']}, Exitosos: {$resultado['exitosos']}, Errores: {$resultado['errores']}, Tasa de éxito: {$tasaExito}%");
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error("ValidarSofiaJob falló: {$exception->getMessage()}", [
            'complementario_id' => $this->complementarioId,
            'user_id' => $this->userId,
            'exception' => $exception
        ]);
    }
}
