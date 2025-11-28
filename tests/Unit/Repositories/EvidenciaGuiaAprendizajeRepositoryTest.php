<?php

namespace Tests\Unit\Repositories;

use App\Models\Aprendiz;
use App\Models\EvidenciaGuiaAprendizaje;
use App\Models\Evidencias;
use App\Models\GuiasAprendizaje;
use App\Repositories\EvidenciaGuiaAprendizajeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EvidenciaGuiaAprendizajeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EvidenciaGuiaAprendizajeRepository $repository;

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

        $this->repository = new EvidenciaGuiaAprendizajeRepository;
    }

    #[Test]
    public function obtiene_evidencias_por_guia(): void
    {
        $guia = GuiasAprendizaje::factory()->create();
        EvidenciaGuiaAprendizaje::factory()->count(2)->create([
            'guia_aprendizaje_id' => $guia->id,
        ]);

        $resultado = $this->repository->obtenerPorGuia($guia->id);

        $this->assertGreaterThanOrEqual(2, $resultado->count());
    }

    #[Test]
    public function obtiene_evidencias_por_aprendiz(): void
    {
        $aprendiz = Aprendiz::factory()->create();
        EvidenciaGuiaAprendizaje::factory()->count(2)->create([
            'aprendiz_id' => $aprendiz->id,
        ]);

        $resultado = $this->repository->obtenerPorAprendiz($aprendiz->id);

        $this->assertGreaterThanOrEqual(2, $resultado->count());
    }

    #[Test]
    public function crea_evidencia_guia(): void
    {
        $guia = GuiasAprendizaje::factory()->create();
        $aprendiz = Aprendiz::factory()->create();
        $evidencia = Evidencias::factory()->create();

        $datos = [
            'guia_aprendizaje_id' => $guia->id,
            'aprendiz_id' => $aprendiz->id,
            'evidencia_id' => $evidencia->id,
        ];

        $evidenciaGuia = $this->repository->crear($datos);

        $this->assertDatabaseHas('evidencia_guia_aprendizajes', [
            'guia_aprendizaje_id' => $guia->id,
            'aprendiz_id' => $aprendiz->id,
        ]);
        $this->assertEquals($guia->id, $evidenciaGuia->guia_aprendizaje_id);
    }

    #[Test]
    public function califica_evidencia(): void
    {
        $evidenciaGuia = EvidenciaGuiaAprendizaje::factory()->create();

        $calificado = $this->repository->calificar($evidenciaGuia->id, 4.5, 'Muy bien');

        $this->assertTrue($calificado);
        $this->assertDatabaseHas('evidencia_guia_aprendizajes', [
            'id' => $evidenciaGuia->id,
            'calificacion' => 4.5,
        ]);
    }
}

