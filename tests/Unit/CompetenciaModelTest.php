<?php

namespace Tests\Unit;

use App\Models\Competencia;
use App\Models\ProgramaFormacion;
use App\Models\ResultadosAprendizaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompetenciaModelTest extends TestCase
{
    use RefreshDatabase;

    protected Competencia $competencia;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);

        $this->competencia = Competencia::factory()->create();
    }

    #[Test]
    public function tiene_relacion_con_resultados_aprendizaje(): void
    {
        $resultado = ResultadosAprendizaje::factory()->create();
        $this->competencia->resultadosAprendizaje()->attach($resultado->id);

        $this->assertTrue($this->competencia->resultadosAprendizaje()->exists());
    }

    #[Test]
    public function tiene_relacion_con_programas_formacion(): void
    {
        $programa = ProgramaFormacion::factory()->create();
        $this->competencia->programasFormacion()->attach($programa->id);

        $this->assertTrue($this->competencia->programasFormacion()->exists());
    }

    #[Test]
    public function puede_verificar_si_esta_activo(): void
    {
        $competencia = Competencia::factory()->create(['status' => true]);

        $this->assertTrue($competencia->status);
    }

    #[Test]
    public function castea_duracion_correctamente(): void
    {
        $competencia = Competencia::factory()->create(['duracion' => 100]);

        $this->assertIsNumeric($competencia->duracion);
        $this->assertEquals(100, $competencia->duracion);
    }
}
