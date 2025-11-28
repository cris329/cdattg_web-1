<?php

namespace Tests\Unit\Repositories;

use App\Models\InstructorFichaCaracterizacion;
use App\Models\InstructorFichaDias;
use App\Models\Parametro;
use App\Repositories\InstructorFichaDiasRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstructorFichaDiasRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private InstructorFichaDiasRepository $repository;

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

        $this->repository = new InstructorFichaDiasRepository;
    }

    #[Test]
    public function obtiene_dias_por_instructor_ficha(): void
    {
        $instructorFicha = InstructorFichaCaracterizacion::factory()->create();
        $parametro = Parametro::firstOrCreate(['name' => 'LUNES']);

        InstructorFichaDias::factory()->create([
            'instructor_ficha_caracterizacion_id' => $instructorFicha->id,
            'dia_formacion_id' => $parametro->id,
        ]);

        $resultado = $this->repository->obtenerPorInstructorFicha($instructorFicha->id);

        $this->assertGreaterThanOrEqual(1, $resultado->count());
    }

    #[Test]
    public function crea_relacion_instructor_ficha_dia(): void
    {
        $instructorFicha = InstructorFichaCaracterizacion::factory()->create();
        $parametro = Parametro::firstOrCreate(['name' => 'MARTES']);

        $datos = [
            'instructor_ficha_caracterizacion_id' => $instructorFicha->id,
            'dia_formacion_id' => $parametro->id,
        ];

        $instructorFichaDia = $this->repository->crear($datos);

        $this->assertDatabaseHas('instructor_ficha_dias', [
            'instructor_ficha_caracterizacion_id' => $instructorFicha->id,
        ]);
        $this->assertEquals($instructorFicha->id, $instructorFichaDia->instructor_ficha_caracterizacion_id);
    }

    #[Test]
    public function asigna_multiples_dias_a_instructor_ficha(): void
    {
        $instructorFicha = InstructorFichaCaracterizacion::factory()->create();
        $parametro1 = Parametro::firstOrCreate(['name' => 'LUNES']);
        $parametro2 = Parametro::firstOrCreate(['name' => 'MARTES']);
        $parametro3 = Parametro::firstOrCreate(['name' => 'MIERCOLES']);

        $diasIds = [$parametro1->id, $parametro2->id, $parametro3->id];
        $count = $this->repository->asignarDias($instructorFicha->id, $diasIds);

        $this->assertEquals(3, $count);
    }
}

