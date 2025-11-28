<?php

namespace Tests\Unit;

use App\Models\Competencia;
use App\Models\ProgramaFormacion;
use App\Services\CompetenciaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompetenciaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CompetenciaService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);

        $this->service = app(CompetenciaService::class);
    }

    #[Test]
    public function puede_obtener_competencias_por_programa(): void
    {
        $programa = ProgramaFormacion::factory()->create();
        $competencia = Competencia::factory()->create();
        $competencia->programasFormacion()->attach($programa->id);

        $resultado = $this->service->obtenerPorPrograma($programa->id);

        $this->assertNotEmpty($resultado);
        $this->assertTrue($resultado->contains($competencia));
    }

    #[Test]
    public function puede_obtener_competencia_con_resultados(): void
    {
        $competencia = Competencia::factory()->create();
        $resultado = \App\Models\ResultadosAprendizaje::factory()->create();
        $competencia->resultadosAprendizaje()->attach($resultado->id);

        $resultado = $this->service->obtenerConResultados($competencia->id);

        $this->assertArrayHasKey('competencia', $resultado);
        $this->assertArrayHasKey('resultados', $resultado);
        $this->assertGreaterThan(0, $resultado['total_resultados']);
    }

    #[Test]
    public function puede_obtener_arbol_competencias(): void
    {
        $programa = ProgramaFormacion::factory()->create();
        $competencia = Competencia::factory()->create();
        $competencia->programasFormacion()->attach($programa->id);

        $arbol = $this->service->obtenerArbolCompetencias($programa->id);

        $this->assertIsArray($arbol);
        $this->assertNotEmpty($arbol);
    }
}
