<?php

namespace Tests\Unit;

use App\Models\Competencia;
use App\Models\CompetenciaPrograma;
use App\Models\ProgramaFormacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompetenciaProgramaModelTest extends TestCase
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
        $relacion = CompetenciaPrograma::factory()->create([
            'competencia_id' => $competencia->id,
        ]);

        $this->assertInstanceOf(Competencia::class, $relacion->competencia);
        $this->assertEquals($competencia->id, $relacion->competencia->id);
    }

    #[Test]
    public function tiene_relacion_con_programa(): void
    {
        $programa = ProgramaFormacion::factory()->create();
        $relacion = CompetenciaPrograma::factory()->create([
            'programa_id' => $programa->id,
        ]);

        $this->assertInstanceOf(ProgramaFormacion::class, $relacion->programa);
        $this->assertEquals($programa->id, $relacion->programa->id);
    }
}

