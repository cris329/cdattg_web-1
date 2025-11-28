<?php

namespace Tests\Unit\Repositories;

use App\Models\ResultadosAprendizaje;
use App\Repositories\ResultadosAprendizajeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResultadosAprendizajeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected ResultadosAprendizajeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->repository = new ResultadosAprendizajeRepository;
    }

    #[Test]
    public function puede_obtener_resultados_vigentes(): void
    {
        ResultadosAprendizaje::factory()->create([
            'fecha_inicio' => now()->addDays(10),
            'fecha_fin' => now()->addDays(20),
        ]);

        $resultado = $this->repository->getResultadosVigentes();

        $this->assertIsIterable($resultado);
    }

    #[Test]
    public function puede_obtener_resultados_por_competencia(): void
    {
        $competencia = \App\Models\Competencia::factory()->create();

        $resultado = $this->repository->getResultadosAprendizajePorCompetencia($competencia->id);

        $this->assertIsIterable($resultado);
    }
}
