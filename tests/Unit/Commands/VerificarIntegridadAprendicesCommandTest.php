<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\VerificarIntegridadAprendices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VerificarIntegridadAprendicesCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function command_existe(): void
    {
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->artisan('aprendices:verificar-integridad')
            ->assertExitCode(0);
    }

    #[Test]
    public function tiene_signature_correcto(): void
    {
        $command = new VerificarIntegridadAprendices;

        $this->assertStringContainsString('aprendices:verificar-integridad', $command->getSignature());
    }
}
