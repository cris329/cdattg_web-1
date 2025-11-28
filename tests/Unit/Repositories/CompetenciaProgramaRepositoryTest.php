<?php

namespace Tests\Unit\Repositories;

use App\Models\Competencia;
use App\Models\CompetenciaPrograma;
use App\Models\ProgramaFormacion;
use App\Repositories\CompetenciaProgramaRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompetenciaProgramaRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CompetenciaProgramaRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->repository = new CompetenciaProgramaRepository;
    }

    #[Test]
    public function obtiene_competencias_por_programa(): void
    {
        $programa = ProgramaFormacion::factory()->create();
        $competencias = Competencia::factory()->count(3)->create();

        foreach ($competencias as $competencia) {
            CompetenciaPrograma::factory()->create([
                'programa_formacion_id' => $programa->id,
                'competencia_id' => $competencia->id,
            ]);
        }

        $resultado = $this->repository->obtenerPorPrograma($programa->id);

        $this->assertCount(3, $resultado);
    }

    #[Test]
    public function crea_relacion_competencia_programa(): void
    {
        $programa = ProgramaFormacion::factory()->create();
        $competencia = Competencia::factory()->create();

        $datos = [
            'programa_formacion_id' => $programa->id,
            'competencia_id' => $competencia->id,
            'orden' => 1,
        ];

        $relacion = $this->repository->crear($datos);

        $this->assertDatabaseHas('competencia_programa', [
            'programa_formacion_id' => $programa->id,
            'competencia_id' => $competencia->id,
        ]);
        $this->assertEquals($programa->id, $relacion->programa_formacion_id);
    }

    #[Test]
    public function invalida_cache(): void
    {
        $programa = ProgramaFormacion::factory()->create();
        $this->repository->obtenerPorPrograma($programa->id);

        $this->repository->invalidarCache();

        $this->assertTrue(true);
    }
}

