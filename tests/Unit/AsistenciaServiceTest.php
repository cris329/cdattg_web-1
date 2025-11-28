<?php

namespace Tests\Unit;

use App\Models\AsistenciaAprendiz;
use App\Models\FichaCaracterizacion;
use App\Services\AsistenciaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AsistenciaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AsistenciaService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->service = app(AsistenciaService::class);
    }

    #[Test]
    public function puede_obtener_asistencias_por_ficha(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        $instructorFicha = \App\Models\InstructorFichaCaracterizacion::factory()->create(['ficha_id' => $ficha->id]);
        $aprendizFicha = \App\Models\AprendizFicha::factory()->create(['ficha_id' => $ficha->id]);

        AsistenciaAprendiz::factory()->count(3)->create([
            'instructor_ficha_id' => $instructorFicha->id,
            'aprendiz_ficha_id' => $aprendizFicha->id,
        ]);

        $resultado = $this->service->obtenerPorFicha($ficha->id);

        $this->assertCount(3, $resultado);
    }

    #[Test]
    public function puede_registrar_asistencia(): void
    {
        $instructorFicha = \App\Models\InstructorFichaCaracterizacion::factory()->create();
        $aprendizFicha = \App\Models\AprendizFicha::factory()->create();

        $datos = [
            'instructor_ficha_id' => $instructorFicha->id,
            'aprendiz_ficha_id' => $aprendizFicha->id,
            'hora_ingreso' => now()->format('H:i:s'),
        ];

        $asistencia = $this->service->registrarAsistencia($datos);

        $this->assertInstanceOf(AsistenciaAprendiz::class, $asistencia);
        $this->assertDatabaseHas('asistencia_aprendices', [
            'id' => $asistencia->id,
        ]);
    }
}
