<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\ValidarFichasCaracterizacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidarFichasCaracterizacionCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function command_existe(): void
    {
        $this->artisan('fichas:validar --all')
            ->assertExitCode(0);
    }

    #[Test]
    public function tiene_signature_correcto(): void
    {
        $command = new ValidarFichasCaracterizacion;

        $this->assertStringContainsString('fichas:validar', $command->getSignature());
    }
}
