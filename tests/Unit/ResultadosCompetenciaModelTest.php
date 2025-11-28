<?php

namespace Tests\Unit;

use App\Models\Competencia;
use App\Models\ResultadosAprendizaje;
use App\Models\ResultadosCompetencia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResultadosCompetenciaModelTest extends TestCase
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
    public function tiene_relacion_con_competencia(): void
    {
        $competencia = Competencia::factory()->create();
        $relacion = ResultadosCompetencia::factory()->create([
            'competencia_id' => $competencia->id,
        ]);

        $this->assertInstanceOf(Competencia::class, $relacion->competencia);
        $this->assertEquals($competencia->id, $relacion->competencia->id);
    }

    #[Test]
    public function tiene_relacion_con_rap(): void
    {
        $rap = ResultadosAprendizaje::factory()->create();
        $relacion = ResultadosCompetencia::factory()->create([
            'rap_id' => $rap->id,
        ]);

        $this->assertInstanceOf(ResultadosAprendizaje::class, $relacion->rap);
        $this->assertEquals($rap->id, $relacion->rap->id);
    }
}

