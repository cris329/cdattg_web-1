<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\AspiranteComplementario;
use App\Models\Persona;
use App\Services\AuditoriaService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ValidarSofiaJob implements ShouldQueue
{
    use Queueable;

    protected $complementarioId;
    protected $userId;
    protected $progressId;
    protected AuditoriaService $auditoriaService;

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
    public function handle(AuditoriaService $auditoriaService): void
    {
        $this->auditoriaService = $auditoriaService;

        Log::info("🚀 Iniciando validación SenaSofiaPlus para programa: {$this->complementarioId}");

        // Obtener registro de progreso si existe
        $progress = null;
        if ($this->progressId) {
            $progress = \App\Models\SofiaValidationProgress::find($this->progressId);
            if ($progress) {
                $progress->markAsStarted();
                Log::info("📊 Progreso inicializado con ID: {$this->progressId}");
            }
        }

        // Obtener aspirantes que necesitan validación
        $aspirantes = AspiranteComplementario::with('persona')
            ->where('complementario_id', $this->complementarioId)
            ->whereHas('persona', function($query) {
                $query->whereIn('estado_sofia', [0, 2]);
            })
            ->get();

        if ($aspirantes->isEmpty()) {
            Log::info('ℹ️ No hay aspirantes que necesiten validación.');
            if ($progress) {
                $progress->markAsCompleted();
            }
            return;
        }

        $totalAspirantes = $aspirantes->count();
        Log::info("📋 Iniciando validación de {$totalAspirantes} aspirantes...");

        $exitosos = 0;
        $errores = 0;
        $errores_detalle = [];
        $procesados = 0;

        // Procesar en lotes para mejor control de memoria y rate limiting
        $batchSize = 5; // Procesar de 5 en 5 para optimizar
        $batches = $aspirantes->chunk($batchSize);

        foreach ($batches as $batchIndex => $batch) {
            Log::info("🔄 Procesando lote " . ($batchIndex + 1) . "/" . $batches->count() . " ({$batch->count()} aspirantes)");

            foreach ($batch as $aspirante) {
                $procesados++;
                $cedula = $aspirante->persona->numero_documento;

                try {
                    Log::info("🔍 Validando cédula {$cedula} ({$procesados}/{$totalAspirantes})");

                    $startTime = microtime(true);
                    $resultado = $this->validarAspirante($cedula);
                    $endTime = microtime(true);
                    $duration = round($endTime - $startTime, 2);

                    // Guardar estado anterior para auditoría
                    $estadoAnterior = $aspirante->persona->estado_sofia;

                    // Actualizar estado basado en resultado
                    $nuevoEstado = $this->determinarEstadoSofia($resultado);
                    $aspirante->persona->update(['estado_sofia' => $nuevoEstado]);

                    $estadoLabel = $this->getEstadoLabel($nuevoEstado);
                    Log::info("✅ Cédula {$cedula}: {$resultado} -> Estado: {$estadoLabel} (Tiempo: {$duration}s)");

                    // Registrar en auditoría
                    $resultadoAuditoria = $nuevoEstado === 1 ? 'exitoso' : ($nuevoEstado === 0 ? 'advertencia' : 'exitoso'); // Considerar cualquier validación completada como exitosa
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
                            'complementario_id' => $this->complementarioId
                        ]
                    );

                    if ($nuevoEstado === 1) {
                        $exitosos++;
                    } elseif ($nuevoEstado === 0 || $nuevoEstado === 2) {
                        // Considerar como exitoso cualquier validación que no sea error
                        $exitosos++;
                    }

                    // Actualizar progreso
                    if ($progress) {
                        $progress->incrementProcessed($nuevoEstado === 1 || $nuevoEstado === 0 || $nuevoEstado === 2);
                    }

                } catch (\Exception $e) {
                    $errorMsg = "❌ Error con cédula {$cedula}: {$e->getMessage()}";
                    Log::error($errorMsg, [
                        'aspirante_id' => $aspirante->id,
                        'persona_id' => $aspirante->persona_id,
                        'complementario_id' => $this->complementarioId,
                        'exception' => $e->getTraceAsString()
                    ]);

                    // Registrar error en auditoría
                    $this->auditoriaService->registrarValidacionSenasofiaplus(
                        $aspirante->id,
                        'error',
                        $errorMsg,
                        [
                            'cedula' => $cedula,
                            'tiempo_respuesta' => $duration ?? null,
                            'complementario_id' => $this->complementarioId,
                            'exception_message' => $e->getMessage(),
                            'exception_type' => get_class($e)
                        ]
                    );

                    $errores++;
                    $errores_detalle[] = $errorMsg;

                    // Actualizar progreso con error
                    if ($progress) {
                        $progress->incrementProcessed(false);
                    }
                }

                // Delay optimizado entre validaciones para evitar rate limiting
                $delay = $this->calculateDelay($procesados, $totalAspirantes);
                if ($delay > 0) {
                    Log::debug("⏳ Esperando {$delay}ms antes de siguiente validación...");
                    usleep($delay * 1000); // usleep usa microsegundos
                }
            }

            // Delay adicional entre lotes
            if ($batches->count() > 1 && $batchIndex < $batches->count() - 1) {
                Log::info("🔄 Cambio de lote - Esperando 3 segundos...");
                sleep(3);
            }
        }

        // Marcar como completado
        if ($progress) {
            if ($errores > 0) {
                Log::warning("⚠️ Validación completada con {$errores} errores");
                $progress->markAsFailed($errores_detalle);
            } else {
                Log::info("🎉 Validación completada exitosamente");
                $progress->markAsCompleted();
            }
        }

        Log::info("📊 Resumen final - Total: {$totalAspirantes}, Exitosos: {$exitosos}, Errores: {$errores}, Tasa de éxito: " . round(($exitosos / $totalAspirantes) * 100, 1) . "%");
    }

    /**
     * Calcular delay dinámico basado en el progreso
     */
    private function calculateDelay($procesados, $total)
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

    /**
     * Obtener etiqueta legible del estado
     */
    private function getEstadoLabel($estado)
    {
        return match($estado) {
            0 => 'No registrado',
            1 => 'Registrado',
            2 => 'Requiere cambio',
            default => 'Desconocido'
        };
    }

    private function validarAspirante($cedula)
    {
        // Obtener URL del servicio de Playwright desde variable de entorno
        $playwrightUrl = env('PLAYWRIGHT_SERVICE_URL', 'http://playwright:3000');
        $validateUrl = rtrim($playwrightUrl, '/') . '/validate';

        Log::info("🌐 Enviando petición HTTP al servicio Playwright para cédula: {$cedula}", [
            'url' => $validateUrl,
            'cedula' => $cedula
        ]);

        try {
            // Verificar primero si el servicio está disponible (health check)
            $healthUrl = rtrim($playwrightUrl, '/') . '/health';
            try {
                $healthResponse = Http::timeout(5)->get($healthUrl);
                if (!$healthResponse->successful()) {
                    Log::warning("⚠️ Servicio Playwright no responde al health check", [
                        'health_url' => $healthUrl,
                        'status' => $healthResponse->status()
                    ]);
                } else {
                    Log::debug("✅ Servicio Playwright está disponible (health check OK)");
                }
            } catch (\Exception $e) {
                Log::warning("⚠️ No se pudo verificar health del servicio Playwright: " . $e->getMessage());
            }

            // Hacer petición POST al servicio de Playwright
            Log::info("📤 Iniciando validación HTTP para cédula: {$cedula}");
            $startTime = microtime(true);

            $response = Http::timeout(90) // 90 segundos de timeout (las validaciones pueden tardar 15-20 segundos)
                ->post($validateUrl, [
                    'cedula' => $cedula
                ]);

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            Log::info("📥 Respuesta recibida del servicio Playwright para cédula {$cedula} (duración: {$duration}s)", [
                'status_code' => $response->status(),
                'duration' => $duration
            ]);

            // Verificar si la petición fue exitosa
            if (!$response->successful()) {
                $statusCode = $response->status();
                $errorBody = $response->body();

                Log::error("❌ Servicio Playwright retornó error HTTP para cédula {$cedula}", [
                    'status_code' => $statusCode,
                    'response' => $errorBody,
                    'url' => $validateUrl,
                    'duration' => $duration
                ]);

                throw new \Exception("Error HTTP {$statusCode} del servicio Playwright: {$errorBody}");
            }

            // Obtener respuesta JSON
            $responseData = $response->json();

            Log::debug("📋 Respuesta JSON del servicio Playwright para cédula {$cedula}", [
                'response_data' => $responseData
            ]);

            // Verificar estructura de respuesta
            if (!isset($responseData['status'])) {
                Log::error("❌ Respuesta del servicio Playwright sin campo 'status' para cédula {$cedula}", [
                    'response' => $responseData,
                    'raw_body' => $response->body()
                ]);
                throw new \Exception("Respuesta inválida del servicio Playwright: falta campo 'status'");
            }

            // Si hay error en la respuesta
            if ($responseData['status'] === 'error') {
                $errorMessage = $responseData['message'] ?? 'Error desconocido del servicio Playwright';
                Log::error("❌ Servicio Playwright reportó error para cédula {$cedula}", [
                    'message' => $errorMessage,
                    'detail' => $responseData['detail'] ?? null,
                    'duration' => $duration
                ]);
                throw new \Exception("Error del servicio Playwright: {$errorMessage}");
            }

            // Verificar que el status sea 'ok'
            if ($responseData['status'] !== 'ok') {
                Log::error("❌ Respuesta del servicio Playwright con status inesperado para cédula {$cedula}", [
                    'status' => $responseData['status'],
                    'response' => $responseData
                ]);
                throw new \Exception("Status inesperado del servicio Playwright: {$responseData['status']}");
            }

            // Extraer resultado de la respuesta
            $resultado = $responseData['resultado'] ?? null;

            if ($resultado === null) {
                Log::error("❌ Respuesta del servicio Playwright sin campo 'resultado' para cédula {$cedula}", [
                    'response' => $responseData,
                    'raw_body' => $response->body()
                ]);
                throw new \Exception("Respuesta sin resultado del servicio Playwright");
            }

            Log::info("✅ Servicio Playwright completado exitosamente para cédula {$cedula}", [
                'resultado' => $resultado,
                'duration' => $duration
            ]);

            return $resultado;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("❌ Error de conexión con servicio Playwright para cédula {$cedula}", [
                'message' => $e->getMessage(),
                'url' => $validateUrl,
                'exception_type' => get_class($e)
            ]);
            throw new \Exception("No se pudo conectar al servicio Playwright en {$validateUrl}: " . $e->getMessage());
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error("❌ Error en la petición HTTP al servicio Playwright para cédula {$cedula}", [
                'message' => $e->getMessage(),
                'url' => $validateUrl,
                'exception_type' => get_class($e)
            ]);
            throw new \Exception("Error en la petición al servicio Playwright: " . $e->getMessage());
        } catch (\Exception $e) {
            Log::error("❌ Error al validar cédula {$cedula} con servicio Playwright", [
                'message' => $e->getMessage(),
                'url' => $validateUrl,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function determinarEstadoSofia($resultado)
    {
        $resultadoLower = strtolower($resultado);

        // PRIMERO: Verificar si es error
        if ($resultado === 'ERROR' || str_contains($resultadoLower, 'error')) {
            Log::warning("Error en validación de SenaSofiaPlus: '{$resultado}'");
            return 0; // No registrado - error se trata como no registrado
        }

        // SEGUNDO: Verificar respuestas directas del script
        if ($resultado === 'YA_EXISTE') {
            return 1; // Registrado
        }

        if ($resultado === 'NO_REGISTRADO') {
            return 0; // No registrado
        }

        if ($resultado === 'REQUIERE_CAMBIO') {
            return 2; // Requiere cambio de cédula
        }

        if ($resultado === 'DESCONOCIDO') {
            return 0; // No registrado
        }

        // TERCERO: Verificar si requiere cambio de documento (texto largo)
        if (str_contains($resultadoLower, 'requiere_cambio') ||
            str_contains($resultadoLower, 'actualizar tu documento') ||
            str_contains($resultadoLower, 'cambiar tu documento') ||
            str_contains($resultadoLower, 'tarjeta de identidad')) {
            return 2; // Requiere cambio de cédula
        }

        // CUARTO: Verificar si está registrado correctamente (texto largo)
        if (str_contains($resultadoLower, 'ya existe') ||
            str_contains($resultadoLower, 'ya cuentas con un registro') ||
            str_contains($resultadoLower, 'cuenta registrada')) {
            return 1; // Registrado
        }

        // QUINTO: Verificar si NO está registrado (puede registrarse)
        if (str_contains($resultadoLower, 'no_registrado') ||
            str_contains($resultadoLower, 'desconocido') ||
            trim($resultado) === '') {
            return 0; // No registrado - puede crear cuenta
        }

        // SEXTO: Cualquier otro resultado se considera como no registrado
        Log::warning("Respuesta no reconocida de SenaSofiaPlus: '{$resultado}' - tratando como no registrado");
        return 0; // Por defecto, asumir no registrado
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
