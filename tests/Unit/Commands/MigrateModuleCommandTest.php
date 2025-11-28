<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\MigrateModule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MigrateModuleCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function puede_listar_modulos(): void
    {
        $this->artisan('migrate:module --list')
            ->expectsOutput('Módulos de migración disponibles:')
            ->assertExitCode(0);
    }

    #[Test]
    public function tiene_signature_correcto(): void
    {
        $command = new MigrateModule;

        $this->assertStringContainsString('migrate:module', $command->getSignature());
    }
}
