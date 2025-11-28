<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\EliminarPersonasDespuesDe;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EliminarPersonasDespuesDeCommandTest extends TestCase
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
        $command = new EliminarPersonasDespuesDe;

        $this->assertEquals(
            'personas:eliminar-despues-de',
            $command->getName()
        );
    }

    #[Test]
    public function valida_id_invalido_negativo(): void
    {
        Artisan::call('personas:eliminar-despues-de', ['id' => -1]);

        $this->assertEquals(1, Artisan::exitCode());
    }

    #[Test]
    public function valida_id_invalido_cero(): void
    {
        Artisan::call('personas:eliminar-despues-de', ['id' => 0]);

        $this->assertEquals(1, Artisan::exitCode());
    }

    #[Test]
    public function muestra_informacion_cuando_no_hay_personas(): void
    {
        Artisan::call('personas:eliminar-despues-de', ['id' => 100]);

        $output = Artisan::output();
        $this->assertStringContainsString('No hay registros para eliminar', $output);
        $this->assertEquals(0, Artisan::exitCode());
    }

    #[Test]
    public function muestra_informacion_cuando_hay_personas(): void
    {
        $personas = Persona::factory()->count(5)->create();
        $ultimoId = $personas->max('id');
        $idLimite = $ultimoId - 3;

        Artisan::call('personas:eliminar-despues-de', [
            'id' => $idLimite,
            '--force' => true,
        ]);

        $output = Artisan::output();
        $this->assertStringContainsString('ELIMINACIÓN DE PERSONAS', $output);
        $this->assertStringContainsString('Total de registros a eliminar', $output);
        $this->assertEquals(0, Artisan::exitCode());
    }

    #[Test]
    public function elimina_personas_con_force(): void
    {
        $personas = Persona::factory()->count(5)->create();
        $ultimoId = $personas->max('id');
        $idLimite = $ultimoId - 3;
        $totalEsperado = $personas->where('id', '>', $idLimite)->count();

        Artisan::call('personas:eliminar-despues-de', [
            'id' => $idLimite,
            '--force' => true,
        ]);

        $personasRestantes = Persona::where('id', '>', $idLimite)->count();
        $this->assertEquals(0, $personasRestantes);

        $output = Artisan::output();
        $this->assertStringContainsString('Eliminación completada', $output);
        $this->assertEquals(0, Artisan::exitCode());
    }

    #[Test]
    public function muestra_ejemplos_de_registros_a_eliminar(): void
    {
        $personas = Persona::factory()->count(5)->create();
        $ultimoId = $personas->max('id');
        $idLimite = $ultimoId - 3;

        Artisan::call('personas:eliminar-despues-de', [
            'id' => $idLimite,
            '--force' => true,
        ]);

        $output = Artisan::output();
        $this->assertStringContainsString('Ejemplos de registros a eliminar', $output);
    }
}
