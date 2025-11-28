<?php

namespace Tests\Unit;

use App\Models\AsignacionInstructorLog;
use App\Models\FichaCaracterizacion;
use App\Models\Instructor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AsignacionInstructorLogModelTest extends TestCase
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
    public function tiene_relacion_con_instructor(): void
    {
        $instructor = Instructor::factory()->create();
        $log = AsignacionInstructorLog::factory()->create([
            'instructor_id' => $instructor->id,
        ]);

        $this->assertInstanceOf(Instructor::class, $log->instructor);
        $this->assertEquals($instructor->id, $log->instructor->id);
    }

    #[Test]
    public function tiene_relacion_con_ficha(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        $log = AsignacionInstructorLog::factory()->create([
            'ficha_id' => $ficha->id,
        ]);

        $this->assertInstanceOf(FichaCaracterizacion::class, $log->ficha);
        $this->assertEquals($ficha->id, $log->ficha->id);
    }

    #[Test]
    public function tiene_relacion_con_user(): void
    {
        $user = User::factory()->create();
        $log = AsignacionInstructorLog::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $log->user);
        $this->assertEquals($user->id, $log->user->id);
    }

    #[Test]
    public function puede_crear_log(): void
    {
        $instructor = Instructor::factory()->create();
        $ficha = FichaCaracterizacion::factory()->create();
        $user = User::factory()->create();

        $log = AsignacionInstructorLog::crearLog(
            $instructor->id,
            $ficha->id,
            'asignar',
            'exitoso',
            'Asignación exitosa',
            $user->id,
            ['detalle' => 'test'],
            null,
            null
        );

        $this->assertDatabaseHas('asignacion_instructor_logs', [
            'id' => $log->id,
            'accion' => 'asignar',
            'resultado' => 'exitoso',
        ]);
    }

    #[Test]
    public function scope_exitoso_filtra_logs_exitosos(): void
    {
        AsignacionInstructorLog::factory()->create(['resultado' => 'exitoso']);
        AsignacionInstructorLog::factory()->create(['resultado' => 'error']);

        $exitosos = AsignacionInstructorLog::exitoso()->get();

        $this->assertCount(1, $exitosos);
    }

    #[Test]
    public function scope_con_error_filtra_logs_con_error(): void
    {
        AsignacionInstructorLog::factory()->create(['resultado' => 'exitoso']);
        AsignacionInstructorLog::factory()->create(['resultado' => 'error']);

        $conError = AsignacionInstructorLog::conError()->get();

        $this->assertCount(1, $conError);
    }

    #[Test]
    public function obtiene_estadisticas(): void
    {
        AsignacionInstructorLog::factory()->count(3)->create(['resultado' => 'exitoso']);
        AsignacionInstructorLog::factory()->count(2)->create(['resultado' => 'error']);

        $estadisticas = AsignacionInstructorLog::obtenerEstadisticas();

        $this->assertIsArray($estadisticas);
        $this->assertArrayHasKey('total', $estadisticas);
        $this->assertEquals(5, $estadisticas['total']);
        $this->assertEquals(3, $estadisticas['exitosos']);
    }
}

