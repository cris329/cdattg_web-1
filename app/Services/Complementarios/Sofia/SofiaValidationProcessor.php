<?php

namespace App\Services\Complementarios\Sofia;

use App\Models\Complementarios\SofiaValidationProgress;
use Illuminate\Support\Facades\Log;

class SofiaValidationProcessor
{
    private const BATCH_SIZE = 5;
    private const BATCH_DELAY_SECONDS = 3;
    private const DELAY_INITIAL_MS = 3000;
    private const DELAY_MID_MS = 2000;
    private const DELAY_FINAL_MS = 1000;
    private const PROGRESS_THRESHOLD_LOW = 0.2;
    private const PROGRESS_THRESHOLD_MID = 0.5;

    private SofiaValidationService $validationService;
    private int $batchSize;
    private int $batchDelay;

    public function __construct(SofiaValidationService $validationService)
    {
        $this->validationService = $validationService;
        $this->batchSize = self::BATCH_SIZE;
        $this->batchDelay = self::BATCH_DELAY_SECONDS;
    }

    /**
     * Procesar validaciones en lotes
     */
    public function processBatch(
        $aspirantes,
        int $complementarioId,
        ?SofiaValidationProgress $progress = null
    ): array {
        $totalAspirantes = $aspirantes->count();
        Log::info('Iniciando validacion de aspirantes', ['total' => $totalAspirantes]);

        $exitosos = 0;
        $errores = 0;
        $errores_detalle = [];
        $procesados = 0;

        $batches = $aspirantes->chunk($this->batchSize);
        $totalBatches = $batches->count();

        foreach ($batches as $batchIndex => $batch) {
            $batchNumber = $batchIndex + 1;
            Log::info('Procesando lote', [
                'lote' => $batchNumber,
                'total_lotes' => $totalBatches,
                'aspirantes_lote' => $batch->count()
            ]);

            foreach ($batch as $aspirante) {
                $procesados++;
                $cedula = $aspirante->persona->numero_documento;
                Log::info('Validando cedula', [
                    'cedula' => $cedula,
                    'progreso' => "{$procesados}/{$totalAspirantes}"
                ]);

                $result = $this->validationService->validateAspirante(
                    $aspirante,
                    $complementarioId,
                    $progress
                );

                if ($progress) {
                    $isSuccessful = isset($result['success']) && $result['success'] === true;
                    $progress->incrementProcessed($isSuccessful);
                }

                if ($result['success']) {
                    $estado = $result['estado'];
                    if (in_array($estado, [0, 1, 2], true)) {
                        $exitosos++;
                    }
                } else {
                    $errores++;
                    $errores_detalle[] = $result['error'];
                }

                $delay = $this->calculateDelay($procesados, $totalAspirantes);
                if ($delay > 0) {
                    Log::debug('Esperando antes de siguiente validacion', ['delay_ms' => $delay]);
                    usleep($delay * 1000);
                }
            }

            if ($totalBatches > 1 && $batchIndex < $totalBatches - 1) {
                Log::info('Cambio de lote - esperando', ['segundos' => $this->batchDelay]);
                sleep($this->batchDelay);
            }
        }

        return [
            'total' => $totalAspirantes,
            'exitosos' => $exitosos,
            'errores' => $errores,
            'errores_detalle' => $errores_detalle
        ];
    }

    /**
     * Calcular delay dinámico basado en el progreso
     */
    private function calculateDelay(int $procesados, int $total): int
    {
        if ($total === 0) {
            return 0;
        }

        $progress = $procesados / $total;

        if ($progress < self::PROGRESS_THRESHOLD_LOW) {
            return self::DELAY_INITIAL_MS;
        }

        if ($progress < self::PROGRESS_THRESHOLD_MID) {
            return self::DELAY_MID_MS;
        }

        return self::DELAY_FINAL_MS;
    }
}

