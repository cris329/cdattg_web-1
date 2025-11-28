<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\VerificarRelacionPersona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VerificarRelacionPersonaCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function command_existe(): void
    {
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->artisan('aprendices:verificar-relacion-persona')
            ->assertExitCode(0);
    }

    #[Test]
    public function tiene_signature_correcto(): void
    {
        $command = new VerificarRelacionPersona;

        $this->assertStringContainsString('aprendices:verificar-relacion-persona', $command->getSignature());
    }
}
