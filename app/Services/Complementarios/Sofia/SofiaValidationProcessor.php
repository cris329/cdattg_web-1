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

        $stats = [
            'exitosos' => 0,
            'errores' => 0,
            'errores_detalle' => [],
            'procesados' => 0
        ];

        $batches = $aspirantes->chunk($this->batchSize);
        $totalBatches = $batches->count();

        foreach ($batches as $batchIndex => $batch) {
            $this->logBatchStart($batchIndex, $totalBatches, $batch->count());
            $this->processBatchItems($batch, $complementarioId, $progress, $totalAspirantes, $stats);
            $this->waitBetweenBatches($batchIndex, $totalBatches);
        }

        return [
            'total' => $totalAspirantes,
            'exitosos' => $stats['exitosos'],
            'errores' => $stats['errores'],
            'errores_detalle' => $stats['errores_detalle']
        ];
    }

    /**
     * Procesar items de un lote
     */
    private function processBatchItems(
        $batch,
        int $complementarioId,
        ?SofiaValidationProgress $progress,
        int $totalAspirantes,
        array &$stats
    ): void {
        foreach ($batch as $aspirante) {
            $stats['procesados']++;
            $this->logAspiranteValidation($aspirante, $stats['procesados'], $totalAspirantes);

            $result = $this->validationService->validateAspirante(
                $aspirante,
                $complementarioId,
                $progress
            );

            $this->updateProgress($progress, $result);
            $this->updateStats($result, $stats);
            $this->applyDelayIfNeeded($stats['procesados'], $totalAspirantes);
        }
    }

    /**
     * Registrar inicio de procesamiento de lote
     */
    private function logBatchStart(int $batchIndex, int $totalBatches, int $batchSize): void
    {
        $batchNumber = $batchIndex + 1;
        Log::info('Procesando lote', [
            'lote' => $batchNumber,
            'total_lotes' => $totalBatches,
            'aspirantes_lote' => $batchSize
        ]);
    }

    /**
     * Registrar validación de aspirante
     */
    private function logAspiranteValidation($aspirante, int $procesados, int $totalAspirantes): void
    {
        $cedula = $aspirante->persona->numero_documento;
        Log::info('Validando cedula', [
            'cedula' => $cedula,
            'progreso' => "{$procesados}/{$totalAspirantes}"
        ]);
    }

    /**
     * Actualizar progreso si existe
     */
    private function updateProgress(?SofiaValidationProgress $progress, array $result): void
    {
        if ($progress) {
            $isSuccessful = isset($result['success']) && $result['success'] === true;
            $progress->incrementProcessed($isSuccessful);
        }
    }

    /**
     * Actualizar estadísticas de procesamiento
     */
    private function updateStats(array $result, array &$stats): void
    {
        if ($result['success']) {
            $estado = $result['estado'] ?? null;
            if ($this->isValidState($estado)) {
                $stats['exitosos']++;
            }
        } else {
            $stats['errores']++;
            $stats['errores_detalle'][] = $result['error'] ?? 'Error desconocido';
        }
    }

    /**
     * Verificar si el estado es válido
     */
    private function isValidState(?int $estado): bool
    {
        return $estado !== null && in_array($estado, [0, 1, 2], true);
    }

    /**
     * Aplicar delay si es necesario
     */
    private function applyDelayIfNeeded(int $procesados, int $totalAspirantes): void
    {
        $delay = $this->calculateDelay($procesados, $totalAspirantes);
        if ($delay > 0) {
            Log::debug('Esperando antes de siguiente validacion', ['delay_ms' => $delay]);
            usleep($delay * 1000);
        }
    }

    /**
     * Esperar entre lotes si es necesario
     */
    private function waitBetweenBatches(int $batchIndex, int $totalBatches): void
    {
        if ($totalBatches > 1 && $batchIndex < $totalBatches - 1) {
            Log::info('Cambio de lote - esperando', ['segundos' => $this->batchDelay]);
            sleep($this->batchDelay);
        }
    }

    /**
     * Calcular delay dinámico basado en el progreso
     */
    private function calculateDelay(int $procesados, int $total): int
    {
        $delay = 0;

        if ($total === 0) {
            return $delay;
        }

        $progress = $procesados / $total;

        if ($progress < self::PROGRESS_THRESHOLD_LOW) {
            $delay = self::DELAY_INITIAL_MS;
        } elseif ($progress < self::PROGRESS_THRESHOLD_MID) {
            $delay = self::DELAY_MID_MS;
        } else {
            $delay = self::DELAY_FINAL_MS;
        }

        return $delay;
    }
}

