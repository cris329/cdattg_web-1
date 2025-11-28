<?php

namespace Tests\Unit\Repositories;

use App\Models\GuiaAprendizajeRap;
use App\Models\GuiasAprendizaje;
use App\Models\ResultadosAprendizaje;
use App\Repositories\GuiaAprendizajeRapRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GuiaAprendizajeRapRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private GuiaAprendizajeRapRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->repository = new GuiaAprendizajeRapRepository;
    }

    #[Test]
    public function obtiene_guias_por_rap(): void
    {
        $rap = ResultadosAprendizaje::factory()->create();
        $guias = GuiasAprendizaje::factory()->count(2)->create();

        foreach ($guias as $guia) {
            GuiaAprendizajeRap::factory()->create([
                'resultado_aprendizaje_id' => $rap->id,
                'guia_aprendizaje_id' => $guia->id,
            ]);
        }

        $resultado = $this->repository->obtenerPorRap($rap->id);

        $this->assertGreaterThanOrEqual(2, $resultado->count());
    }

    #[Test]
    public function obtiene_guias_por_guia_aprendizaje(): void
    {
        $guia = GuiasAprendizaje::factory()->create();
        $raps = ResultadosAprendizaje::factory()->count(2)->create();

        foreach ($raps as $rap) {
            GuiaAprendizajeRap::factory()->create([
                'guia_aprendizaje_id' => $guia->id,
                'resultado_aprendizaje_id' => $rap->id,
            ]);
        }

        $resultado = $this->repository->obtenerPorGuia($guia->id);

        $this->assertGreaterThanOrEqual(2, $resultado->count());
    }

    #[Test]
    public function crea_relacion_guia_rap(): void
    {
        $guia = GuiasAprendizaje::factory()->create();
        $rap = ResultadosAprendizaje::factory()->create();

        $datos = [
            'guia_aprendizaje_id' => $guia->id,
            'resultado_aprendizaje_id' => $rap->id,
        ];

        $relacion = $this->repository->crear($datos);

        $this->assertDatabaseHas('guia_aprendizaje_raps', [
            'guia_aprendizaje_id' => $guia->id,
            'resultado_aprendizaje_id' => $rap->id,
        ]);
        $this->assertEquals($guia->id, $relacion->guia_aprendizaje_id);
    }
}

