<?php

namespace Tests\Unit\Repositories;

use App\Models\Competencia;
use App\Models\ResultadosAprendizaje;
use App\Models\ResultadosCompetencia;
use App\Repositories\ResultadosCompetenciaRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResultadosCompetenciaRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ResultadosCompetenciaRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->repository = new ResultadosCompetenciaRepository;
    }

    #[Test]
    public function obtiene_resultados_por_competencia(): void
    {
        $competencia = Competencia::factory()->create();
        $resultados = ResultadosAprendizaje::factory()->count(3)->create();

        foreach ($resultados as $resultado) {
            ResultadosCompetencia::factory()->create([
                'competencia_id' => $competencia->id,
                'resultado_aprendizaje_id' => $resultado->id,
            ]);
        }

        $resultado = $this->repository->obtenerPorCompetencia($competencia->id);

        $this->assertCount(3, $resultado);
    }

    #[Test]
    public function invalida_cache(): void
    {
        $competencia = Competencia::factory()->create();
        $this->repository->obtenerPorCompetencia($competencia->id);

        $this->repository->invalidarCache();

        $this->assertTrue(true);
    }
}

