<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\TestWebSocket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TestWebSocketCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function command_existe(): void
    {
        $command = new TestWebSocket;

        $this->assertEquals(
            'websocket:test',
            $command->getName()
        );
    }

    #[Test]
    public function ejecuta_tipo_qr_por_defecto(): void
    {
        Artisan::call('websocket:test');

        $output = Artisan::output();
        $this->assertStringContainsString('Evento de prueba enviado correctamente', $output);
        $this->assertEquals(0, Artisan::exitCode());
    }

    #[Test]
    public function ejecuta_tipo_qr_explicito(): void
    {
        Artisan::call('websocket:test', ['type' => 'qr']);

        $output = Artisan::output();
        $this->assertStringContainsString('Evento QrScanned enviado', $output);
        $this->assertEquals(0, Artisan::exitCode());
    }

    #[Test]
    public function ejecuta_tipo_asistencia(): void
    {
        Artisan::call('websocket:test', ['type' => 'asistencia']);

        $output = Artisan::output();
        $this->assertStringContainsString('Evento NuevaAsistenciaRegistrada enviado', $output);
        $this->assertEquals(0, Artisan::exitCode());
    }

    #[Test]
    public function muestra_error_tipo_invalido(): void
    {
        Artisan::call('websocket:test', ['type' => 'invalido']);

        $output = Artisan::output();
        $this->assertStringContainsString('Tipo no válido', $output);
        $this->assertEquals(1, Artisan::exitCode());
    }
}
