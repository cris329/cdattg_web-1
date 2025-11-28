<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\ListarAprendicesProblematicos;
use App\Models\Aprendiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListarAprendicesProblematicosCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
        ]);
    }

    #[Test]
    public function command_existe(): void
    {
        $command = new ListarAprendicesProblematicos;

        $this->assertEquals(
            'aprendices:listar-problematicos',
            $command->getName()
        );
    }

    #[Test]
    public function ejecuta_comando_sin_errores(): void
    {
        Artisan::call('aprendices:listar-problematicos');

        $this->assertEquals(0, Artisan::exitCode());
    }

    #[Test]
    public function no_encontrados_cuando_no_hay_problemas(): void
    {
        Aprendiz::factory()->count(3)->create();

        Artisan::call('aprendices:listar-problematicos');

        $output = Artisan::output();
        $this->assertStringContainsString('No se encontraron aprendices con problemas', $output);
        $this->assertEquals(0, Artisan::exitCode());
    }

    #[Test]
    public function detecta_aprendices_sin_persona(): void
    {
        $aprendiz = Aprendiz::factory()->create(['persona_id' => null]);

        Artisan::call('aprendices:listar-problematicos');

        $output = Artisan::output();
        $this->assertStringContainsString('Encontrados', $output);
        $this->assertStringContainsString('aprendices con problemas', $output);
        $this->assertEquals(0, Artisan::exitCode());
    }

    #[Test]
    public function muestra_tabla_de_problematicos(): void
    {
        $aprendiz = Aprendiz::factory()->create(['persona_id' => null]);

        Artisan::call('aprendices:listar-problematicos');

        $output = Artisan::output();
        $this->assertStringContainsString('ID Aprendiz', $output);
        $this->assertEquals(0, Artisan::exitCode());
    }
}
