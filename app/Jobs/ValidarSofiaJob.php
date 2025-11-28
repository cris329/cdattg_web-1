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
        Log::info('Iniciando validacion SenaSofiaPlus', [
            'complementario_id' => $this->complementarioId
        ]);

        $progress = $this->initializeProgress();

        $aspirantes = $validationService->getAspirantesToValidate($this->complementarioId);

        if ($aspirantes->isEmpty()) {
            Log::info('No hay aspirantes que necesiten validacion');
            if ($progress) {
                $progress->markAsCompleted();
            }
            return;
        }

        $resultado = $processor->processBatch($aspirantes, $this->complementarioId, $progress);

        $this->finalizeProgress($progress, $resultado);
        $this->logFinalSummary($resultado);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('ValidarSofiaJob fallo', [
            'complementario_id' => $this->complementarioId,
            'user_id' => $this->userId,
            'exception_message' => $exception->getMessage(),
            'exception' => $exception
        ]);
    }

    /**
     * Inicializar progreso
     */
    private function initializeProgress(): ?SofiaValidationProgress
    {
        if (!$this->progressId) {
            return null;
        }

        $progress = SofiaValidationProgress::find($this->progressId);
        if ($progress) {
            $progress->markAsStarted();
            Log::info('Progreso inicializado', ['progress_id' => $this->progressId]);
        }

        return $progress;
    }

    /**
     * Finalizar progreso
     */
    private function finalizeProgress(?SofiaValidationProgress $progress, array $resultado): void
    {
        if (!$progress) {
            return;
        }

        if ($resultado['errores'] > 0) {
            Log::warning('Validacion completada con errores', [
                'errores' => $resultado['errores']
            ]);
            $progress->markAsFailed($resultado['errores_detalle']);
        } else {
            Log::info('Validacion completada exitosamente');
            $progress->markAsCompleted();
        }
    }

    /**
     * Registrar resumen final
     */
    private function logFinalSummary(array $resultado): void
    {
        $tasaExito = $resultado['total'] > 0
            ? round(($resultado['exitosos'] / $resultado['total']) * 100, 1)
            : 0;

        Log::info('Resumen final de validacion', [
            'total' => $resultado['total'],
            'exitosos' => $resultado['exitosos'],
            'errores' => $resultado['errores'],
            'tasa_exito' => $tasaExito
        ]);
    }
}
