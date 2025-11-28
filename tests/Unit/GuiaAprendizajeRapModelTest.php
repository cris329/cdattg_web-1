<?php

namespace Tests\Unit;

use App\Models\GuiasAprendizaje;
use App\Models\GuiaAprendizajeRap;
use App\Models\ResultadosAprendizaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GuiaAprendizajeRapModelTest extends TestCase
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
    public function tiene_relacion_con_guia_aprendizaje(): void
    {
        $guia = GuiasAprendizaje::factory()->create();
        $relacion = GuiaAprendizajeRap::factory()->create([
            'guia_aprendizaje_id' => $guia->id,
        ]);

        $this->assertInstanceOf(GuiasAprendizaje::class, $relacion->guiaAprendizaje);
        $this->assertEquals($guia->id, $relacion->guiaAprendizaje->id);
    }

    #[Test]
    public function tiene_relacion_con_rap(): void
    {
        $rap = ResultadosAprendizaje::factory()->create();
        $relacion = GuiaAprendizajeRap::factory()->create([
            'rap_id' => $rap->id,
        ]);

        $this->assertInstanceOf(ResultadosAprendizaje::class, $relacion->rap);
        $this->assertEquals($rap->id, $relacion->rap->id);
    }
}

