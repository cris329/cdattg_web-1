<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\RegistrarAsistenciaPrueba;
use App\Models\AprendizFicha;
use App\Models\AsistenciaAprendiz;
use App\Models\InstructorFichaCaracterizacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrarAsistenciaPruebaCommandTest extends TestCase
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
        $command = new RegistrarAsistenciaPrueba;

        $this->assertEquals(
            'asistencia:registrar',
            $command->getName()
        );
    }

    #[Test]
    public function valida_tipo_invalido(): void
    {
        Artisan::call('asistencia:registrar', ['tipo' => 'invalido']);

        $this->assertEquals(1, Artisan::exitCode());

        $output = Artisan::output();
        $this->assertStringContainsString('Tipo no válido', $output);
    }

    #[Test]
    public function muestra_error_si_no_hay_aprendices(): void
    {
        Artisan::call('asistencia:registrar', ['tipo' => 'entrada']);

        $this->assertEquals(1, Artisan::exitCode());

        $output = Artisan::output();
        $this->assertStringContainsString('No se encontró ningún aprendiz', $output);
    }

    #[Test]
    public function muestra_error_si_no_hay_instructores_asignados(): void
    {
        AprendizFicha::factory()->create();

        Artisan::call('asistencia:registrar', ['tipo' => 'entrada']);

        $this->assertEquals(1, Artisan::exitCode());

        $output = Artisan::output();
        $this->assertStringContainsString('No se encontró ningún instructor asignado', $output);
    }

    #[Test]
    public function registra_asistencia_entrada(): void
    {
        $aprendizFicha = AprendizFicha::factory()->create();
        InstructorFichaCaracterizacion::factory()->create();

        Artisan::call('asistencia:registrar', ['tipo' => 'entrada']);

        $output = Artisan::output();
        $this->assertStringContainsString('Asistencia de ENTRADA registrada', $output);
        $this->assertEquals(0, Artisan::exitCode());

        $this->assertDatabaseHas('asistencia_aprendices', [
            'hora_ingreso' => '!=',
        ]);
    }

    #[Test]
    public function muestra_error_salida_sin_entrada(): void
    {
        $aprendizFicha = AprendizFicha::factory()->create();
        InstructorFichaCaracterizacion::factory()->create();

        Artisan::call('asistencia:registrar', ['tipo' => 'salida']);

        $this->assertEquals(1, Artisan::exitCode());

        $output = Artisan::output();
        $this->assertStringContainsString('No se encontró una asistencia de entrada', $output);
    }

    #[Test]
    public function registra_asistencia_salida(): void
    {
        $aprendizFicha = AprendizFicha::factory()->create();
        $instructorFicha = InstructorFichaCaracterizacion::factory()->create();

        AsistenciaAprendiz::factory()->create([
            'aprendiz_ficha_id' => $aprendizFicha->id,
            'instructor_ficha_id' => $instructorFicha->id,
            'hora_ingreso' => now()->format('H:i:s'),
            'hora_salida' => null,
        ]);

        Artisan::call('asistencia:registrar', ['tipo' => 'salida']);

        $output = Artisan::output();
        $this->assertStringContainsString('Asistencia de SALIDA registrada', $output);
        $this->assertEquals(0, Artisan::exitCode());
    }
}
