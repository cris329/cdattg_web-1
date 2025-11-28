<?php

namespace Tests\Unit\Repositories;

use App\Models\GuiasAprendizaje;
use App\Models\GuiasResultados;
use App\Models\ResultadosAprendizaje;
use App\Repositories\GuiasResultadosRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GuiasResultadosRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private GuiasResultadosRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->repository = new GuiasResultadosRepository;
    }

    #[Test]
    public function obtiene_guias_por_resultado(): void
    {
        $resultado = ResultadosAprendizaje::factory()->create();
        $guias = GuiasAprendizaje::factory()->count(2)->create();

        foreach ($guias as $guia) {
            GuiasResultados::factory()->create([
                'resultado_aprendizaje_id' => $resultado->id,
                'guia_aprendizaje_id' => $guia->id,
            ]);
        }

        $resultado = $this->repository->obtenerPorResultado($resultado->id);

        $this->assertGreaterThanOrEqual(2, $resultado->count());
    }

    #[Test]
    public function crea_relacion_guia_resultado(): void
    {
        $guia = GuiasAprendizaje::factory()->create();
        $resultado = ResultadosAprendizaje::factory()->create();

        $datos = [
            'guia_aprendizaje_id' => $guia->id,
            'resultado_aprendizaje_id' => $resultado->id,
        ];

        $relacion = $this->repository->crear($datos);

        $this->assertDatabaseHas('guias_resultados', [
            'guia_aprendizaje_id' => $guia->id,
            'resultado_aprendizaje_id' => $resultado->id,
        ]);
        $this->assertEquals($guia->id, $relacion->guia_aprendizaje_id);
    }
}

