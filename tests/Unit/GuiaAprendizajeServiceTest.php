<?php

namespace Tests\Unit;

use App\Models\GuiasAprendizaje;
use App\Models\ProgramaFormacion;
use App\Services\GuiaAprendizajeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GuiaAprendizajeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GuiaAprendizajeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->service = app(GuiaAprendizajeService::class);
    }

    #[Test]
    public function puede_obtener_guias_por_programa(): void
    {
        $programa = ProgramaFormacion::factory()->create();
        $guia = GuiasAprendizaje::factory()->create();

        $resultado = $this->service->obtenerPorPrograma($programa->id);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $resultado);
    }

    #[Test]
    public function puede_obtener_progreso_aprendiz(): void
    {
        $aprendiz = \App\Models\Aprendiz::factory()->create();

        $resultado = $this->service->obtenerProgresoAprendiz($aprendiz->id);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('total_evidencias', $resultado);
        $this->assertArrayHasKey('porcentaje_completado', $resultado);
    }
}
