<?php

namespace Tests\Unit\Repositories;

use App\Models\Competencia;
use App\Repositories\CompetenciaRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompetenciaRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected CompetenciaRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->repository = new CompetenciaRepository;
    }

    #[Test]
    public function puede_obtener_competencias_vigentes(): void
    {
        Competencia::factory()->create([
            'fecha_inicio' => now()->addDays(10),
            'fecha_fin' => now()->addDays(20),
        ]);

        $resultado = $this->repository->getCompetenciasVigentes();

        $this->assertIsIterable($resultado);
    }

    #[Test]
    public function puede_obtener_proximas_competencias(): void
    {
        Competencia::factory()->create([
            'fecha_inicio' => now()->addDays(5),
        ]);

        $resultado = $this->repository->getProximasCompetencias();

        $this->assertIsIterable($resultado);
    }
}
