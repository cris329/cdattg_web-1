<?php

namespace App\Services\Complementarios\Sofia;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class SofiaHttpClient
{
    private const DEFAULT_TIMEOUT = 90;
    private const HEALTH_CHECK_TIMEOUT = 5;
    private const STATUS_OK = 'ok';
    private const STATUS_ERROR = 'error';
    private const RESPONSE_FIELD_STATUS = 'status';
    private const RESPONSE_FIELD_RESULTADO = 'resultado';
    private const RESPONSE_FIELD_MESSAGE = 'message';
    private const RESPONSE_FIELD_DETAIL = 'detail';

    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.playwright.url', 'http://playwright:3000'), '/');
        $this->timeout = self::DEFAULT_TIMEOUT;
    }

    /**
     * Validar una cédula a través del servicio Playwright
     */
    public function validate(string $cedula): string
    {
        $validateUrl = $this->baseUrl . '/validate';

        Log::info('Enviando peticion HTTP al servicio Playwright', [
            'url' => $validateUrl,
            'cedula' => $cedula
        ]);

        try {
            $this->checkHealth();

            Log::info('Iniciando validacion HTTP', ['cedula' => $cedula]);
            $startTime = microtime(true);

            $response = Http::timeout($this->timeout)
                ->post($validateUrl, [
                    'cedula' => $cedula
                ]);

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            Log::info('Respuesta recibida del servicio Playwright', [
                'cedula' => $cedula,
                'status_code' => $response->status(),
                'duration' => $duration
            ]);

            return $this->parseResponse($response, $cedula, $duration);

        } catch (ConnectionException $e) {
            $this->logConnectionError($e, $validateUrl, $cedula);
            throw new \RuntimeException(
                "No se pudo conectar al servicio Playwright en {$validateUrl}: " . $e->getMessage(),
                0,
                $e
            );
        } catch (RequestException $e) {
            $this->logRequestError($e, $validateUrl, $cedula);
            throw new \RuntimeException(
                "Error en la peticion al servicio Playwright: " . $e->getMessage(),
                0,
                $e
            );
        } catch (\Exception $e) {
            $this->logValidationError($e, $validateUrl, $cedula);
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
            $healthResponse = Http::timeout(self::HEALTH_CHECK_TIMEOUT)->get($healthUrl);
            if (!$healthResponse->successful()) {
                Log::warning('Servicio Playwright no responde al health check', [
                    'health_url' => $healthUrl,
                    'status' => $healthResponse->status()
                ]);
            } else {
                Log::debug('Servicio Playwright esta disponible (health check OK)');
            }
        } catch (\Exception $e) {
            Log::warning('No se pudo verificar health del servicio Playwright', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Parsear y validar la respuesta del servicio
     */
    private function parseResponse($response, string $cedula, float $duration): string
    {
        if (!$response->successful()) {
            $this->handleUnsuccessfulResponse($response, $cedula, $duration);
        }

        $responseData = $response->json();

        Log::debug('Respuesta JSON del servicio Playwright', [
            'cedula' => $cedula,
            'response_data' => $responseData
        ]);

        $this->validateResponseStructure($responseData, $response, $cedula);
        $this->validateResponseStatus($responseData, $cedula, $duration);

        $resultado = $responseData[self::RESPONSE_FIELD_RESULTADO] ?? null;

        if ($resultado === null) {
            Log::error('Respuesta del servicio Playwright sin campo resultado', [
                'cedula' => $cedula,
                'response' => $responseData,
                'raw_body' => $response->body()
            ]);
            throw new \RuntimeException('Respuesta sin resultado del servicio Playwright');
        }

        Log::info('Servicio Playwright completado exitosamente', [
            'cedula' => $cedula,
            'resultado' => $resultado,
            'duration' => $duration
        ]);

        return $resultado;
    }

    /**
     * Manejar respuesta no exitosa
     */
    private function handleUnsuccessfulResponse($response, string $cedula, float $duration): void
    {
        $statusCode = $response->status();
        $errorBody = $response->body();

        Log::error('Servicio Playwright retorno error HTTP', [
            'cedula' => $cedula,
            'status_code' => $statusCode,
            'response' => $errorBody,
            'duration' => $duration
        ]);

        throw new \RuntimeException("Error HTTP {$statusCode} del servicio Playwright: {$errorBody}");
    }

    /**
     * Validar estructura de respuesta
     */
    private function validateResponseStructure(array $responseData, $response, string $cedula): void
    {
        if (!isset($responseData[self::RESPONSE_FIELD_STATUS])) {
            Log::error('Respuesta del servicio Playwright sin campo status', [
                'cedula' => $cedula,
                'response' => $responseData,
                'raw_body' => $response->body()
            ]);
            throw new \RuntimeException('Respuesta invalida del servicio Playwright: falta campo status');
        }
    }

    /**
     * Validar status de respuesta
     */
    private function validateResponseStatus(array $responseData, string $cedula, float $duration): void
    {
        if ($responseData[self::RESPONSE_FIELD_STATUS] === self::STATUS_ERROR) {
            $errorMessage = $responseData[self::RESPONSE_FIELD_MESSAGE] ?? 'Error desconocido del servicio Playwright';
            Log::error('Servicio Playwright reporto error', [
                'cedula' => $cedula,
                'message' => $errorMessage,
                'detail' => $responseData[self::RESPONSE_FIELD_DETAIL] ?? null,
                'duration' => $duration
            ]);
            throw new \RuntimeException("Error del servicio Playwright: {$errorMessage}");
        }

        if ($responseData[self::RESPONSE_FIELD_STATUS] !== self::STATUS_OK) {
            Log::error('Respuesta del servicio Playwright con status inesperado', [
                'cedula' => $cedula,
                'status' => $responseData[self::RESPONSE_FIELD_STATUS],
                'response' => $responseData
            ]);
            throw new \RuntimeException(
                "Status inesperado del servicio Playwright: {$responseData[self::RESPONSE_FIELD_STATUS]}"
            );
        }
    }

    /**
     * Registrar error de conexión
     */
    private function logConnectionError(ConnectionException $e, string $url, string $cedula): void
    {
        Log::error('Error de conexion con servicio Playwright', [
            'cedula' => $cedula,
            'message' => $e->getMessage(),
            'url' => $url,
            'exception_type' => get_class($e)
        ]);
    }

    /**
     * Registrar error de petición
     */
    private function logRequestError(RequestException $e, string $url, string $cedula): void
    {
        Log::error('Error en la peticion HTTP al servicio Playwright', [
            'cedula' => $cedula,
            'message' => $e->getMessage(),
            'url' => $url,
            'exception_type' => get_class($e)
        ]);
    }

    /**
     * Registrar error de validación
     */
    private function logValidationError(\Exception $e, string $url, string $cedula): void
    {
        Log::error('Error al validar cedula con servicio Playwright', [
            'cedula' => $cedula,
            'message' => $e->getMessage(),
            'url' => $url,
            'exception' => get_class($e),
            'trace' => $e->getTraceAsString()
        ]);
    }
}

