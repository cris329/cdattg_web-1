<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\DebugListadoAprendices;
use App\Models\Aprendiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DebugListadoAprendicesCommandTest extends TestCase
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
        $command = new DebugListadoAprendices;

        $this->assertEquals(
            'aprendices:debug-listado',
            $command->getName()
        );
    }

    #[Test]
    public function ejecuta_comando_sin_errores(): void
    {
        Artisan::call('aprendices:debug-listado');

        $this->assertEquals(0, Artisan::exitCode());
    }

    #[Test]
    public function muestra_informacion_cuando_no_hay_aprendices(): void
    {
        Artisan::call('aprendices:debug-listado');

        $output = Artisan::output();
        $this->assertStringContainsString('Simulando el listado del controlador', $output);
        $this->assertStringContainsString('Total de aprendices: 0', $output);
        $this->assertEquals(0, Artisan::exitCode());
    }

    #[Test]
    public function muestra_tabla_de_aprendices(): void
    {
        Aprendiz::factory()->count(3)->create();

        Artisan::call('aprendices:debug-listado');

        $output = Artisan::output();
        $this->assertStringContainsString('Total de aprendices:', $output);
        $this->assertEquals(0, Artisan::exitCode());
    }

    #[Test]
    public function detecta_aprendices_sin_persona(): void
    {
        $aprendiz = Aprendiz::factory()->create(['persona_id' => null]);

        Artisan::call('aprendices:debug-listado');

        $output = Artisan::output();
        $this->assertStringContainsString('sin persona cargada', $output);
        $this->assertEquals(0, Artisan::exitCode());
    }

    #[Test]
    public function confirma_todos_los_aprendices_tienen_persona(): void
    {
        Aprendiz::factory()->count(3)->create();

        Artisan::call('aprendices:debug-listado');

        $output = Artisan::output();
        $this->assertStringContainsString('Todos los aprendices tienen persona cargada correctamente', $output);
        $this->assertEquals(0, Artisan::exitCode());
    }
}
