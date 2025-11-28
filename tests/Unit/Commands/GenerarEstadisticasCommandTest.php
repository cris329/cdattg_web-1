<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\GenerarEstadisticasCommand;
use App\Services\EstadisticasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GenerarEstadisticasCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function command_existe(): void
    {
        $this->artisan('estadisticas:generar')
            ->assertExitCode(0);
    }

    #[Test]
    public function tiene_signature_correcto(): void
    {
        $command = new GenerarEstadisticasCommand(app(EstadisticasService::class));

        $this->assertStringContainsString('estadisticas:generar', $command->getSignature());
    }
}
