<?php

namespace Tests\Unit\Repositories;

use App\Models\FichaCaracterizacion;
use App\Models\Instructor;
use App\Models\RegistroActividades;
use App\Repositories\RegistroActividadesRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistroActividadesRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private RegistroActividadesRepository $repository;

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

        $this->repository = new RegistroActividadesRepository;
    }

    #[Test]
    public function obtiene_actividades_por_instructor(): void
    {
        $instructor = Instructor::factory()->create();
        RegistroActividades::factory()->count(3)->create([
            'instructor_id' => $instructor->id,
        ]);

        $resultado = $this->repository->obtenerPorInstructor($instructor->id, 10);

        $this->assertGreaterThanOrEqual(3, $resultado->total());
    }

    #[Test]
    public function obtiene_actividades_por_ficha(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        RegistroActividades::factory()->count(2)->create([
            'ficha_caracterizacion_id' => $ficha->id,
        ]);

        $resultado = $this->repository->obtenerPorFicha($ficha->id);

        $this->assertGreaterThanOrEqual(2, $resultado->count());
    }

    #[Test]
    public function obtiene_actividades_por_rango_fechas(): void
    {
        $fechaInicio = Carbon::today()->format('Y-m-d');
        $fechaFin = Carbon::today()->addDays(7)->format('Y-m-d');

        RegistroActividades::factory()->create([
            'fecha' => Carbon::today()->addDays(2),
        ]);

        $resultado = $this->repository->obtenerPorFechas($fechaInicio, $fechaFin);

        $this->assertGreaterThanOrEqual(1, $resultado->count());
    }

    #[Test]
    public function crea_registro_actividad(): void
    {
        $instructor = Instructor::factory()->create();
        $ficha = FichaCaracterizacion::factory()->create();

        $datos = [
            'instructor_id' => $instructor->id,
            'ficha_caracterizacion_id' => $ficha->id,
            'fecha' => Carbon::today(),
            'actividad' => 'Test actividad',
        ];

        $registro = $this->repository->crear($datos);

        $this->assertDatabaseHas('registro_actividades', [
            'instructor_id' => $instructor->id,
        ]);
        $this->assertEquals($instructor->id, $registro->instructor_id);
    }

    #[Test]
    public function actualiza_registro(): void
    {
        $registro = RegistroActividades::factory()->create();

        $actualizado = $this->repository->actualizar($registro->id, ['actividad' => 'Actualizada']);

        $this->assertTrue($actualizado);
    }

    #[Test]
    public function elimina_registro(): void
    {
        $registro = RegistroActividades::factory()->create();

        $eliminado = $this->repository->eliminar($registro->id);

        $this->assertTrue($eliminado);
        $this->assertDatabaseMissing('registro_actividades', [
            'id' => $registro->id,
        ]);
    }
}

