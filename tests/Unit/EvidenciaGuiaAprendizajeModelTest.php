<?php

namespace Tests\Unit;

use App\Models\EvidenciaGuiaAprendizaje;
use App\Models\Evidencias;
use App\Models\GuiasAprendizaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EvidenciaGuiaAprendizajeModelTest extends TestCase
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
    public function puede_crear_relacion(): void
    {
        $evidencia = Evidencias::factory()->create();
        $guia = GuiasAprendizaje::factory()->create();

        $relacion = EvidenciaGuiaAprendizaje::create([
            'evidencia_id' => $evidencia->id,
            'guia_aprendizaje_id' => $guia->id,
            'user_create_id' => 1,
        ]);

        $this->assertDatabaseHas('evidencia_guia_aprendizaje', [
            'id' => $relacion->id,
            'evidencia_id' => $evidencia->id,
            'guia_aprendizaje_id' => $guia->id,
        ]);
    }
}

