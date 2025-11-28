<?php

namespace App\Services\Sofia;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class SofiaHttpClient
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('PLAYWRIGHT_SERVICE_URL', 'http://playwright:3000'), '/');
        $this->timeout = 90; // 90 segundos de timeout
    }

    /**
     * Validar una cédula a través del servicio Playwright
     */
    public function validate(string $cedula): string
    {
        $validateUrl = $this->baseUrl . '/validate';

        Log::info("🌐 Enviando petición HTTP al servicio Playwright para cédula: {$cedula}", [
            'url' => $validateUrl,
            'cedula' => $cedula
        ]);

        try {
            $this->checkHealth();

            Log::info("📤 Iniciando validación HTTP para cédula: {$cedula}");
            $startTime = microtime(true);

            $response = Http::timeout($this->timeout)
                ->post($validateUrl, [
                    'cedula' => $cedula
                ]);

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            Log::info("📥 Respuesta recibida del servicio Playwright para cédula {$cedula} (duración: {$duration}s)", [
                'status_code' => $response->status(),
                'duration' => $duration
            ]);

            return $this->parseResponse($response, $cedula, $duration);

        } catch (ConnectionException $e) {
            Log::error("❌ Error de conexión con servicio Playwright para cédula {$cedula}", [
                'message' => $e->getMessage(),
                'url' => $validateUrl,
                'exception_type' => get_class($e)
            ]);
            throw new \Exception("No se pudo conectar al servicio Playwright en {$validateUrl}: " . $e->getMessage());
        } catch (RequestException $e) {
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

    /**
     * Verificar que el servicio esté disponible
     */
    private function checkHealth(): void
    {
        $healthUrl = $this->baseUrl . '/health';
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
    }

    /**
     * Parsear y validar la respuesta del servicio
     */
    private function parseResponse($response, string $cedula, float $duration): string
    {
        // Verificar si la petición fue exitosa
        if (!$response->successful()) {
            $statusCode = $response->status();
            $errorBody = $response->body();

            Log::error("❌ Servicio Playwright retornó error HTTP para cédula {$cedula}", [
                'status_code' => $statusCode,
                'response' => $errorBody,
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
    }
}

