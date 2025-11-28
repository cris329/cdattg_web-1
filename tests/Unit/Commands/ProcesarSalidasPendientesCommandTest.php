<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\ProcesarSalidasPendientesCommand;
use App\Services\PersonaIngresoSalidaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProcesarSalidasPendientesCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function command_existe(): void
    {
        $this->artisan('ingreso-salida:procesar-salidas-pendientes')
            ->assertExitCode(0);
    }

    #[Test]
    public function tiene_signature_correcto(): void
    {
        $command = new ProcesarSalidasPendientesCommand(
            app(PersonaIngresoSalidaService::class)
        );

        $this->assertEquals(
            'ingreso-salida:procesar-salidas-pendientes',
            $command->getName()
        );
    }
}
