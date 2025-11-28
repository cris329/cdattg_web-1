<?php

namespace App\Services\Sofia;

use App\Models\SofiaValidationProgress;
use Illuminate\Support\Facades\Log;

class SofiaValidationProcessor
{
    private SofiaValidationService $validationService;
    private int $batchSize;
    private int $batchDelay;

    public function __construct(SofiaValidationService $validationService)
    {
        $this->validationService = $validationService;
        $this->batchSize = 5; // Procesar de 5 en 5
        $this->batchDelay = 3; // 3 segundos entre lotes
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
        Log::info("📋 Iniciando validación de {$totalAspirantes} aspirantes...");

        $exitosos = 0;
        $errores = 0;
        $errores_detalle = [];
        $procesados = 0;

        $batches = $aspirantes->chunk($this->batchSize);

        foreach ($batches as $batchIndex => $batch) {
            Log::info("🔄 Procesando lote " . ($batchIndex + 1) . "/" . $batches->count() . " ({$batch->count()} aspirantes)");

            foreach ($batch as $aspirante) {
                $procesados++;
                Log::info("🔍 Validando cédula {$aspirante->persona->numero_documento} ({$procesados}/{$totalAspirantes})");

                $result = $this->validationService->validateAspirante(
                    $aspirante,
                    $complementarioId,
                    $progress
                );

                if ($result['success']) {
                    $estado = $result['estado'];
                    if ($estado === 1 || $estado === 0 || $estado === 2) {
                        $exitosos++;
                    }
                } else {
                    $errores++;
                    $errores_detalle[] = $result['error'];
                }

                // Delay optimizado entre validaciones
                $delay = $this->calculateDelay($procesados, $totalAspirantes);
                if ($delay > 0) {
                    Log::debug("⏳ Esperando {$delay}ms antes de siguiente validación...");
                    usleep($delay * 1000);
                }
            }

            // Delay adicional entre lotes
            if ($batches->count() > 1 && $batchIndex < $batches->count() - 1) {
                Log::info("🔄 Cambio de lote - Esperando {$this->batchDelay} segundos...");
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
        $progress = $procesados / $total;

        // Delay inicial alto, luego se reduce
        if ($progress < 0.2) {
            return 3000; // 3 segundos al inicio
        } elseif ($progress < 0.5) {
            return 2000; // 2 segundos en la mitad
        } else {
            return 1000; // 1 segundo al final
        }
    }
}

