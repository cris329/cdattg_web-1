<?php

namespace Tests\Unit\Repositories;

use App\Models\FichaCaracterizacion;
use App\Models\Instructor;
use App\Models\InstructorFichaCaracterizacion;
use App\Repositories\InstructorFichaRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstructorFichaRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private InstructorFichaRepository $repository;

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

        $this->repository = new InstructorFichaRepository;
    }

    #[Test]
    public function obtiene_fichas_por_instructor(): void
    {
        $instructor = Instructor::factory()->create();
        $fichas = FichaCaracterizacion::factory()->count(2)->create();

        foreach ($fichas as $ficha) {
            InstructorFichaCaracterizacion::factory()->create([
                'instructor_id' => $instructor->id,
                'ficha_id' => $ficha->id,
            ]);
        }

        $resultado = $this->repository->obtenerPorInstructor($instructor->id);

        $this->assertGreaterThanOrEqual(2, $resultado->count());
    }

    #[Test]
    public function obtiene_instructores_por_ficha(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        $instructor = Instructor::factory()->create();

        InstructorFichaCaracterizacion::factory()->create([
            'instructor_id' => $instructor->id,
            'ficha_id' => $ficha->id,
        ]);

        $resultado = $this->repository->obtenerPorFicha($ficha->id);

        $this->assertGreaterThanOrEqual(1, $resultado->count());
    }

    #[Test]
    public function crea_asignacion_instructor_ficha(): void
    {
        $instructor = Instructor::factory()->create();
        $ficha = FichaCaracterizacion::factory()->create();

        $datos = [
            'instructor_id' => $instructor->id,
            'ficha_id' => $ficha->id,
            'status' => true,
        ];

        $instructorFicha = $this->repository->crear($datos);

        $this->assertDatabaseHas('instructor_fichas_caracterizacion', [
            'instructor_id' => $instructor->id,
        ]);
        $this->assertEquals($instructor->id, $instructorFicha->instructor_id);
    }

    #[Test]
    public function actualiza_asignacion(): void
    {
        $instructorFicha = InstructorFichaCaracterizacion::factory()->create();

        $actualizado = $this->repository->actualizar($instructorFicha->id, ['status' => false]);

        $this->assertTrue($actualizado);
    }

    #[Test]
    public function verifica_si_instructor_esta_asignado(): void
    {
        $instructor = Instructor::factory()->create();
        $ficha = FichaCaracterizacion::factory()->create();

        InstructorFichaCaracterizacion::factory()->create([
            'instructor_id' => $instructor->id,
            'ficha_id' => $ficha->id,
            'status' => true,
        ]);

        $estaAsignado = $this->repository->estaAsignado($instructor->id, $ficha->id);

        $this->assertIsBool($estaAsignado);
    }

    #[Test]
    public function obtiene_carga_semanal(): void
    {
        $instructor = Instructor::factory()->create();

        $carga = $this->repository->obtenerCargaSemanal($instructor->id, now()->format('Y-m-d'));

        $this->assertIsInt($carga);
    }
}

