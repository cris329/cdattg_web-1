<?php

namespace Tests\Unit;

use App\Models\Evidencias;
use App\Models\GuiasAprendizaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EvidenciasModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);
    }

    #[Test]
    public function tiene_relacion_muchos_a_muchos_con_guias_aprendizaje(): void
    {
        $evidencia = Evidencias::factory()->create();
        $guias = GuiasAprendizaje::factory()->count(2)->create();

        $evidencia->guiasAprendizaje()->attach($guias->pluck('id')->toArray());

        $this->assertCount(2, $evidencia->guiasAprendizaje);
    }

    #[Test]
    public function puede_terminar_actividad(): void
    {
        $evidencia = Evidencias::factory()->create();

        Evidencias::terminarActividad($evidencia->id);

        $evidencia->refresh();

        $this->assertEquals(27, $evidencia->id_estado);
    }
}

