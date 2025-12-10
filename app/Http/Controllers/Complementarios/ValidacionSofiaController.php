<?php

namespace App\Http\Controllers\Complementarios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Complementarios\AspiranteComplementario;
use App\Models\Complementarios\SofiaValidationProgress;
use App\Jobs\Complementarios\ValidarSofiaJob;
use Illuminate\Support\Facades\Log;

class ValidacionSofiaController extends Controller
{
    private const PROGRAMA_NO_ENCONTRADO = 'Programa no encontrado.';

    /**
     * Iniciar validación SOFIA para un programa complementario
     */
    public function validarSofia($complementarioId)
    {
        try {
            Log::info("Iniciando solicitud de validación SenaSofiaPlus", [
                'complementario_id' => $complementarioId,
                'user_id' => auth()->id(),
                'timestamp' => now()
            ]);

            $programa = ComplementarioOfertado::findOrFail($complementarioId);
            Log::info("Programa encontrado: {$programa->nombre}");

            $validationResult = $this->performValidations($complementarioId);
            if ($validationResult['error'] !== null) {
                return $validationResult['error'];
            }

            $aspirantesCount = $validationResult['aspirantes_count'];
            $progress = $this->createProgressRecord($complementarioId, $aspirantesCount);
            $this->dispatchValidationJob($complementarioId, $progress->id);

            return response()->json([
                'success' => true,
                'message' => "Validación iniciada para {$aspirantesCount} aspirantes. El proceso se ejecutará en segundo plano.",
                'aspirantes_count' => $aspirantesCount,
                'progress_id' => $progress->id
            ]);

        } catch (\Exception $e) {
            return $this->handleException($complementarioId, $e);
        }
    }

    /**
     * Realizar todas las validaciones necesarias antes de iniciar el proceso
     */
    private function performValidations($complementarioId): array
    {
        $aspirantesCount = $this->countAspirantesNeedingValidation($complementarioId);
        
        $errorResponse = $this->checkAspirantesCount($aspirantesCount, $complementarioId);
        if ($errorResponse !== null) {
            return ['error' => $errorResponse, 'aspirantes_count' => 0];
        }

        $errorResponse = $this->checkExistingProgress($complementarioId);
        if ($errorResponse !== null) {
            return ['error' => $errorResponse, 'aspirantes_count' => 0];
        }

        return ['error' => null, 'aspirantes_count' => $aspirantesCount];
    }

    /**
     * Contar aspirantes que necesitan validación
     */
    private function countAspirantesNeedingValidation($complementarioId): int
    {
        $count = AspiranteComplementario::with('persona')
            ->where('complementario_id', $complementarioId)
            ->whereHas('persona', function ($query) {
                $query->whereIn('estado_sofia', [277, 279]); // NO REGISTRADO (277) o REQUIERE CAMBIO (279)
            })
            ->count();

        Log::info("Aspirantes que necesitan validación: {$count}");
        return $count;
    }

    /**
     * Verificar si hay aspirantes para validar
     */
    private function checkAspirantesCount(int $aspirantesCount, $complementarioId): ?\Illuminate\Http\JsonResponse
    {
        if ($aspirantesCount === 0) {
            Log::warning("No hay aspirantes que necesiten validación para programa {$complementarioId}");
            return response()->json([
                'success' => false,
                'message' => 'No hay aspirantes que necesiten validación en este programa.'
            ]);
        }
        return null;
    }

    /**
     * Verificar si ya hay una validación en progreso
     */
    private function checkExistingProgress($complementarioId): ?\Illuminate\Http\JsonResponse
    {
        $existingProgress = SofiaValidationProgress::where('complementario_id', $complementarioId)
            ->whereIn('status', [284, 285]) // PENDING (284) o PROCESSING (285)
            ->first();

        if ($existingProgress) {
            Log::warning("Ya existe una validación en progreso para programa {$complementarioId}", [
                'progress_id' => $existingProgress->id,
                'status' => $existingProgress->status
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Ya hay una validación en progreso para este programa. Espere a que termine.'
            ]);
        }
        return null;
    }

    /**
     * Crear registro de progreso
     */
    private function createProgressRecord($complementarioId, int $aspirantesCount): SofiaValidationProgress
    {
        $progress = SofiaValidationProgress::create([
            'complementario_id' => $complementarioId,
            'user_id' => auth()->id(),
            'status' => 284, // PENDING = 284 según ParametroSeeder
            'total_aspirantes' => $aspirantesCount,
            'processed_aspirantes' => 0,
            'successful_validations' => 0,
            'failed_validations' => 0,
        ]);

        Log::info("Registro de progreso creado", [
            'progress_id' => $progress->id,
            'total_aspirantes' => $aspirantesCount
        ]);

        return $progress;
    }

    /**
     * Despachar job de validación
     */
    private function dispatchValidationJob($complementarioId, int $progressId): void
    {
        ValidarSofiaJob::dispatch($complementarioId, auth()->id(), $progressId)
            ->onQueue('sofia-validation');

        Log::info("Job despachado a la cola", [
            'job_class' => ValidarSofiaJob::class,
            'queue' => 'sofia-validation',
            'delay' => 2
        ]);
    }

    /**
     * Manejar todas las excepciones
     */
    private function handleException($complementarioId, \Exception $e): \Illuminate\Http\JsonResponse
    {
        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            Log::error("Programa no encontrado: {$complementarioId}", ['exception' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => self::PROGRAMA_NO_ENCONTRADO
            ], 404);
        }

        Log::error("Error iniciando validación SenaSofiaPlus", [
            'complementario_id' => $complementarioId,
            'user_id' => auth()->id(),
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error interno del servidor: ' . $e->getMessage()
        ], 500);
    }

    /**
     * Obtener el progreso de una validación
     */
    public function getValidationProgress($progressId)
    {
        try {
            $progress = SofiaValidationProgress::with('complementario')->findOrFail($progressId);

            return response()->json([
                'success' => true,
                'progress' => [
                    'id' => $progress->id,
                    'status' => $progress->status,
                    'status_label' => $progress->status_label,
                    'total_aspirantes' => $progress->total_aspirantes,
                    'processed_aspirantes' => $progress->processed_aspirantes,
                    'successful_validations' => $progress->successful_validations,
                    'failed_validations' => $progress->failed_validations,
                    'progress_percentage' => $progress->progress_percentage,
                    'started_at' => $progress->started_at?->format('d/m/Y H:i:s'),
                    'completed_at' => $progress->completed_at?->format('d/m/Y H:i:s'),
                    'errors' => $progress->errors,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el progreso: ' . $e->getMessage()
            ], 500);
        }
    }
}

