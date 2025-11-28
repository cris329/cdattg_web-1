<?php

namespace Tests\Unit;

use App\Models\AsignacionInstructor;
use App\Models\Competencia;
use App\Models\FichaCaracterizacion;
use App\Models\Instructor;
use App\Models\ResultadosAprendizaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AsignacionInstructorModelTest extends TestCase
{
    use RefreshDatabase;

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
    }

    #[Test]
    public function tiene_relacion_con_ficha(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        $asignacion = AsignacionInstructor::factory()->create(['ficha_id' => $ficha->id]);

        $this->assertInstanceOf(FichaCaracterizacion::class, $asignacion->ficha);
        $this->assertEquals($ficha->id, $asignacion->ficha->id);
    }

    #[Test]
    public function tiene_relacion_con_instructor(): void
    {
        $instructor = Instructor::factory()->create();
        $asignacion = AsignacionInstructor::factory()->create(['instructor_id' => $instructor->id]);

        $this->assertInstanceOf(Instructor::class, $asignacion->instructor);
        $this->assertEquals($instructor->id, $asignacion->instructor->id);
    }

    #[Test]
    public function tiene_relacion_con_competencia(): void
    {
        $competencia = Competencia::factory()->create();
        $asignacion = AsignacionInstructor::factory()->create(['competencia_id' => $competencia->id]);

        $this->assertInstanceOf(Competencia::class, $asignacion->competencia);
        $this->assertEquals($competencia->id, $asignacion->competencia->id);
    }

    #[Test]
    public function tiene_relacion_muchos_a_muchos_con_resultados_aprendizaje(): void
    {
        $asignacion = AsignacionInstructor::factory()->create();
        $resultados = ResultadosAprendizaje::factory()->count(2)->create();

        $asignacion->resultadosAprendizaje()->attach($resultados->pluck('id')->toArray());

        $this->assertCount(2, $asignacion->resultadosAprendizaje);
    }
}

