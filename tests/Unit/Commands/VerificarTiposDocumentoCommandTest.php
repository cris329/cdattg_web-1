<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\VerificarTiposDocumento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VerificarTiposDocumentoCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function command_existe(): void
    {
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->artisan('aprendices:verificar-tipos-documento')
            ->assertExitCode(0);
    }

    #[Test]
    public function tiene_signature_correcto(): void
    {
        $command = new VerificarTiposDocumento;

        $this->assertStringContainsString('aprendices:verificar-tipos-documento', $command->getSignature());
    }
}
