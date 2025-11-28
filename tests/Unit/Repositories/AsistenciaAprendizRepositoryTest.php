<?php

namespace Tests\Unit\Repositories;

use App\Models\AsistenciaAprendiz;
use App\Repositories\AsistenciaAprendizRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AsistenciaAprendizRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected AsistenciaAprendizRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->repository = new AsistenciaAprendizRepository;
    }

    #[Test]
    public function puede_obtener_asistencias_por_ficha(): void
    {
        $instructorFicha = \App\Models\InstructorFichaCaracterizacion::factory()->create();
        $aprendizFicha = \App\Models\AprendizFicha::factory()->create();
        $fichaId = $aprendizFicha->ficha_id;

        AsistenciaAprendiz::factory()->create([
            'instructor_ficha_id' => $instructorFicha->id,
            'aprendiz_ficha_id' => $aprendizFicha->id,
        ]);

        $resultado = $this->repository->obtenerPorFicha($fichaId);

        $this->assertIsIterable($resultado);
    }

    #[Test]
    public function puede_obtener_asistencias_por_documento(): void
    {
        $instructorFicha = \App\Models\InstructorFichaCaracterizacion::factory()->create();
        $aprendizFicha = \App\Models\AprendizFicha::factory()->create();
        $numeroDoc = '1234567890';

        AsistenciaAprendiz::factory()->create([
            'instructor_ficha_id' => $instructorFicha->id,
            'aprendiz_ficha_id' => $aprendizFicha->id,
            'numero_identificacion' => $numeroDoc,
        ]);

        $resultado = $this->repository->obtenerPorDocumento($numeroDoc);

        $this->assertIsIterable($resultado);
    }
}
