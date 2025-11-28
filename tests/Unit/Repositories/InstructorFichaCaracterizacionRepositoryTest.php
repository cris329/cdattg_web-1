<?php

namespace Tests\Unit\Repositories;

use App\Models\Instructor;
use App\Models\InstructorFichaCaracterizacion;
use App\Repositories\InstructorFichaCaracterizacionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstructorFichaCaracterizacionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private InstructorFichaCaracterizacionRepository $repository;

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

        $this->repository = new InstructorFichaCaracterizacionRepository;
    }

    #[Test]
    public function obtiene_fichas_por_instructor(): void
    {
        $instructor = Instructor::factory()->create();
        InstructorFichaCaracterizacion::factory()->count(2)->create([
            'instructor_id' => $instructor->id,
        ]);

        $resultado = $this->repository->getInstructorFichaCaracterizacion($instructor->id);

        $this->assertGreaterThanOrEqual(2, $resultado->count());
    }

    #[Test]
    public function retorna_coleccion_vacia_si_no_hay_fichas(): void
    {
        $instructor = Instructor::factory()->create();

        $resultado = $this->repository->getInstructorFichaCaracterizacion($instructor->id);

        $this->assertCount(0, $resultado);
    }
}

