<?php

namespace Tests\Unit\Repositories;

use App\Models\AsignacionInstructorLog;
use App\Models\FichaCaracterizacion;
use App\Models\Instructor;
use App\Repositories\AsignacionInstructorLogRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AsignacionInstructorLogRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AsignacionInstructorLogRepository $repository;

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

        $this->repository = new AsignacionInstructorLogRepository;
    }

    #[Test]
    public function registra_log_de_asignacion(): void
    {
        $instructor = Instructor::factory()->create();
        $ficha = FichaCaracterizacion::factory()->create();

        $datos = [
            'instructor_id' => $instructor->id,
            'ficha_caracterizacion_id' => $ficha->id,
            'accion' => 'asignar',
            'user_id' => 1,
        ];

        $log = $this->repository->registrar($datos);

        $this->assertDatabaseHas('asignacion_instructor_logs', [
            'instructor_id' => $instructor->id,
            'ficha_caracterizacion_id' => $ficha->id,
        ]);
        $this->assertEquals($instructor->id, $log->instructor_id);
    }

    #[Test]
    public function obtiene_logs_por_instructor(): void
    {
        $instructor = Instructor::factory()->create();
        AsignacionInstructorLog::factory()->count(2)->create([
            'instructor_id' => $instructor->id,
        ]);

        $resultado = $this->repository->obtenerPorInstructor($instructor->id);

        $this->assertGreaterThanOrEqual(2, $resultado->count());
    }

    #[Test]
    public function obtiene_logs_por_ficha(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        AsignacionInstructorLog::factory()->count(2)->create([
            'ficha_caracterizacion_id' => $ficha->id,
        ]);

        $resultado = $this->repository->obtenerPorFicha($ficha->id);

        $this->assertGreaterThanOrEqual(2, $resultado->count());
    }

    #[Test]
    public function obtiene_auditoria_por_rango_fechas(): void
    {
        $fechaInicio = Carbon::today()->format('Y-m-d');
        $fechaFin = Carbon::today()->addDays(7)->format('Y-m-d');

        AsignacionInstructorLog::factory()->create([
            'created_at' => Carbon::today()->addDays(2),
        ]);

        $resultado = $this->repository->obtenerAuditoria($fechaInicio, $fechaFin);

        $this->assertIsIterable($resultado);
    }
}

