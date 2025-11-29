<?php

namespace Tests\Complementarios\Unit\Services\Sofia;

use Tests\TestCase;
use App\Services\Sofia\SofiaHttpClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use PHPUnit\Framework\Attributes\Test;

class SofiaHttpClientTest extends TestCase
{
    private SofiaHttpClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar URL base para tests
        config(['services.playwright.url' => 'http://test-playwright:3000']);
        
        $this->client = new SofiaHttpClient();
    }

    #[Test]
    public function puede_instanciar_cliente(): void
    {
        $this->assertInstanceOf(SofiaHttpClient::class, $this->client);
    }

    #[Test]
    public function valida_cedula_exitosa(): void
    {
        Http::fake([
            'test-playwright:3000/health' => Http::response(['status' => 'ok'], 200),
            'test-playwright:3000/validate' => Http::response([
                'status' => 'ok',
                'resultado' => 'YA_EXISTE'
            ], 200),
        ]);

        $resultado = $this->client->validate('1234567890');

        $this->assertEquals('YA_EXISTE', $resultado);
    }

    #[Test]
    public function valida_cedula_con_resultado_no_registrado(): void
    {
        Http::fake([
            'test-playwright:3000/health' => Http::response(['status' => 'ok'], 200),
            'test-playwright:3000/validate' => Http::response([
                'status' => 'ok',
                'resultado' => 'NO_REGISTRADO'
            ], 200),
        ]);

        $resultado = $this->client->validate('1234567890');

        $this->assertEquals('NO_REGISTRADO', $resultado);
    }

    #[Test]
    public function valida_cedula_con_resultado_requiere_cambio(): void
    {
        Http::fake([
            'test-playwright:3000/health' => Http::response(['status' => 'ok'], 200),
            'test-playwright:3000/validate' => Http::response([
                'status' => 'ok',
                'resultado' => 'REQUIERE_CAMBIO'
            ], 200),
        ]);

        $resultado = $this->client->validate('1234567890');

        $this->assertEquals('REQUIERE_CAMBIO', $resultado);
    }

    #[Test]
    public function lanza_excepcion_si_servicio_responde_con_error(): void
    {
        Http::fake([
            'test-playwright:3000/health' => Http::response(['status' => 'ok'], 200),
            'test-playwright:3000/validate' => Http::response([
                'status' => 'error',
                'message' => 'Error de validación'
            ], 200),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error del servicio Playwright');

        $this->client->validate('1234567890');
    }

    #[Test]
    public function lanza_excepcion_si_respuesta_no_tiene_status(): void
    {
        Http::fake([
            'test-playwright:3000/health' => Http::response(['status' => 'ok'], 200),
            'test-playwright:3000/validate' => Http::response([
                'resultado' => 'YA_EXISTE'
            ], 200),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('falta campo status');

        $this->client->validate('1234567890');
    }

    #[Test]
    public function lanza_excepcion_si_respuesta_no_tiene_resultado(): void
    {
        Http::fake([
            'test-playwright:3000/health' => Http::response(['status' => 'ok'], 200),
            'test-playwright:3000/validate' => Http::response([
                'status' => 'ok'
            ], 200),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('sin resultado');

        $this->client->validate('1234567890');
    }

    #[Test]
    public function lanza_excepcion_si_respuesta_http_no_exitosa(): void
    {
        Http::fake([
            'test-playwright:3000/health' => Http::response(['status' => 'ok'], 200),
            'test-playwright:3000/validate' => Http::response(['error' => 'Internal Server Error'], 500),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error HTTP 500');

        $this->client->validate('1234567890');
    }

    #[Test]
    public function maneja_error_de_conexion(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection refused');
        });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No se pudo conectar');

        $this->client->validate('1234567890');
    }

    #[Test]
    public function maneja_error_de_peticion(): void
    {
        Http::fake(function () {
            throw new RequestException('Request failed');
        });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error en la peticion');

        $this->client->validate('1234567890');
    }

    #[Test]
    public function verifica_health_check_antes_de_validar(): void
    {
        Http::fake([
            'test-playwright:3000/health' => Http::response(['status' => 'ok'], 200),
            'test-playwright:3000/validate' => Http::response([
                'status' => 'ok',
                'resultado' => 'YA_EXISTE'
            ], 200),
        ]);

        $resultado = $this->client->validate('1234567890');

        Http::assertSent(function ($request) {
            return $request->url() === 'http://test-playwright:3000/health' &&
                   $request->method() === 'GET';
        });

        $this->assertEquals('YA_EXISTE', $resultado);
    }

    #[Test]
    public function maneja_health_check_fallido_gracefully(): void
    {
        Http::fake([
            'test-playwright:3000/health' => Http::response(['error' => 'Service unavailable'], 503),
            'test-playwright:3000/validate' => Http::response([
                'status' => 'ok',
                'resultado' => 'YA_EXISTE'
            ], 200),
        ]);

        // El health check falla pero no debería detener la validación
        $resultado = $this->client->validate('1234567890');

        $this->assertEquals('YA_EXISTE', $resultado);
    }
}

