<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\AsignarTipoDocumentoPorDefecto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AsignarTipoDocumentoPorDefectoCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function command_existe(): void
    {
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->artisan('aprendices:asignar-tipo-documento --dry-run')
            ->assertExitCode(0);
    }

    #[Test]
    public function tiene_signature_correcto(): void
    {
        $command = new AsignarTipoDocumentoPorDefecto;

        $this->assertStringContainsString('aprendices:asignar-tipo-documento', $command->getSignature());
    }
}
