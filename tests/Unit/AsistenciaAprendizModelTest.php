<?php

namespace Tests\Unit;

use App\Models\AprendizFicha;
use App\Models\AsistenciaAprendiz;
use App\Models\Evidencias;
use App\Models\InstructorFichaCaracterizacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AsistenciaAprendizModelTest extends TestCase
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
    public function tiene_relacion_con_instructor_ficha(): void
    {
        $instructorFicha = InstructorFichaCaracterizacion::factory()->create();
        $asistencia = AsistenciaAprendiz::factory()->create([
            'instructor_ficha_id' => $instructorFicha->id,
        ]);

        $this->assertInstanceOf(InstructorFichaCaracterizacion::class, $asistencia->instructorFichaCaracterizacion);
        $this->assertEquals($instructorFicha->id, $asistencia->instructorFichaCaracterizacion->id);
    }

    #[Test]
    public function tiene_relacion_con_aprendiz_ficha(): void
    {
        $aprendizFicha = AprendizFicha::factory()->create();
        $asistencia = AsistenciaAprendiz::factory()->create([
            'aprendiz_ficha_id' => $aprendizFicha->id,
        ]);

        $this->assertInstanceOf(AprendizFicha::class, $asistencia->aprendizFicha);
        $this->assertEquals($aprendizFicha->id, $asistencia->aprendizFicha->id);
    }

    #[Test]
    public function tiene_relacion_con_evidencia(): void
    {
        $evidencia = Evidencias::factory()->create();
        $asistencia = AsistenciaAprendiz::factory()->create([
            'evidencia_id' => $evidencia->id,
        ]);

        $this->assertInstanceOf(Evidencias::class, $asistencia->evidencia);
        $this->assertEquals($evidencia->id, $asistencia->evidencia->id);
    }

    #[Test]
    public function puede_crear_asistencia_con_horas(): void
    {
        $asistencia = AsistenciaAprendiz::factory()->create([
            'hora_ingreso' => '08:00:00',
            'hora_salida' => '18:00:00',
        ]);

        $this->assertDatabaseHas('asistencia_aprendices', [
            'id' => $asistencia->id,
            'hora_ingreso' => '08:00:00',
        ]);
    }
}

