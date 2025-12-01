<?php

namespace Tests\Complementarios\Unit\Repositories;

use App\Models\Complementarios\AspiranteComplementario;
use App\Models\Complementarios\SenasofiaplusValidationLog;
use App\Models\User;
use App\Repositories\Complementarios\SenasofiaplusValidationLogRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Complementarios\Concerns\SeedsComplementariosDatabase;

class SenasofiaplusValidationLogRepositoryTest extends TestCase
{
    use RefreshDatabase;
    use SeedsComplementariosDatabase;

    private const RESULTADO_EXITOSO = 'exitoso';
    private const RESULTADO_ERROR = 'error';
    private const RESULTADO_ADVERTENCIA = 'advertencia';
    private const ACCION_VALIDAR = 'validar';

    private SenasofiaplusValidationLogRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedComplementariosDatabaseIfNeeded();

        $this->repository = new SenasofiaplusValidationLogRepository();
    }

    #[Test]
    public function puede_registrar_log_validacion()
    {
        $aspirante = AspiranteComplementario::factory()->create();
        $user = User::factory()->create();

        $datos = [
            'aspirante_id' => $aspirante->id,
            'accion' => self::ACCION_VALIDAR,
            'resultado' => self::RESULTADO_EXITOSO,
            'mensaje' => 'Validación exitosa',
            'user_id' => $user->id,
            'fecha_accion' => now(),
        ];

        $log = $this->repository->registrar($datos);

        $this->assertInstanceOf(SenasofiaplusValidationLog::class, $log);
        $this->assertEquals($aspirante->id, $log->aspirante_id);
        $this->assertEquals(self::ACCION_VALIDAR, $log->accion);
        $this->assertEquals(self::RESULTADO_EXITOSO, $log->resultado);
        $this->assertEquals('Validación exitosa', $log->mensaje);
        $this->assertDatabaseHas('senasofiaplus_validation_logs', [
            'aspirante_id' => $aspirante->id,
            'resultado' => self::RESULTADO_EXITOSO,
        ]);
    }

    #[Test]
    public function puede_registrar_log_con_detalles_y_datos()
    {
        $aspirante = AspiranteComplementario::factory()->create();
        $user = User::factory()->create();

        $datos = [
            'aspirante_id' => $aspirante->id,
            'accion' => self::ACCION_VALIDAR,
            'resultado' => self::RESULTADO_EXITOSO,
            'mensaje' => 'Validación con detalles',
            'user_id' => $user->id,
            'fecha_accion' => now(),
            'detalles' => ['campo' => 'valor'],
            'datos_anteriores' => ['estado' => 1],
            'datos_nuevos' => ['estado' => 3],
        ];

        $log = $this->repository->registrar($datos);

        $this->assertInstanceOf(SenasofiaplusValidationLog::class, $log);
        $this->assertEquals(['campo' => 'valor'], $log->detalles);
        $this->assertEquals(['estado' => 1], $log->datos_anteriores);
        $this->assertEquals(['estado' => 3], $log->datos_nuevos);
    }

    #[Test]
    public function puede_registrar_log_con_valores_por_defecto()
    {
        $aspirante = AspiranteComplementario::factory()->create();

        $datos = [
            'aspirante_id' => $aspirante->id,
            'resultado' => self::RESULTADO_EXITOSO,
            'mensaje' => 'Validación con valores por defecto',
        ];

        $log = $this->repository->registrar($datos);

        $this->assertInstanceOf(SenasofiaplusValidationLog::class, $log);
        $this->assertEquals(self::ACCION_VALIDAR, $log->accion);
        $this->assertEquals(1, $log->user_id); // Bot user por defecto
        $this->assertNotNull($log->fecha_accion);
    }

    #[Test]
    public function puede_obtener_logs_por_aspirante()
    {
        $aspirante1 = AspiranteComplementario::factory()->create();
        $aspirante2 = AspiranteComplementario::factory()->create();
        $user = User::factory()->create();

        // Crear logs para aspirante1
        SenasofiaplusValidationLog::factory()->count(5)->create([
            'aspirante_id' => $aspirante1->id,
            'user_id' => $user->id,
        ]);

        // Crear logs para aspirante2
        SenasofiaplusValidationLog::factory()->count(3)->create([
            'aspirante_id' => $aspirante2->id,
            'user_id' => $user->id,
        ]);

        $logs = $this->repository->obtenerLogsPorAspirante($aspirante1->id);

        $this->assertCount(5, $logs);
        $logs->each(function ($log) use ($aspirante1) {
            $this->assertEquals($aspirante1->id, $log->aspirante_id);
            $this->assertTrue($log->relationLoaded('user'));
        });
    }

    #[Test]
    public function puede_obtener_logs_por_aspirante_con_limite()
    {
        $aspirante = AspiranteComplementario::factory()->create();
        $user = User::factory()->create();

        SenasofiaplusValidationLog::factory()->count(10)->create([
            'aspirante_id' => $aspirante->id,
            'user_id' => $user->id,
        ]);

        $logs = $this->repository->obtenerLogsPorAspirante($aspirante->id, 5);

        $this->assertCount(5, $logs);
    }

    #[Test]
    public function puede_obtener_logs_por_aspirante_ordenados_por_fecha_desc()
    {
        $aspirante = AspiranteComplementario::factory()->create();
        $user = User::factory()->create();

        $log1 = SenasofiaplusValidationLog::factory()->create([
            'aspirante_id' => $aspirante->id,
            'user_id' => $user->id,
            'fecha_accion' => now()->subDays(2),
        ]);

        $log2 = SenasofiaplusValidationLog::factory()->create([
            'aspirante_id' => $aspirante->id,
            'user_id' => $user->id,
            'fecha_accion' => now(),
        ]);

        SenasofiaplusValidationLog::factory()->create([
            'aspirante_id' => $aspirante->id,
            'user_id' => $user->id,
            'fecha_accion' => now()->subDay(),
        ]);

        $logs = $this->repository->obtenerLogsPorAspirante($aspirante->id);

        $this->assertEquals($log2->id, $logs->first()->id);
        $this->assertEquals($log1->id, $logs->last()->id);
    }

    #[Test]
    public function puede_obtener_estadisticas_validaciones()
    {
        $aspirante = AspiranteComplementario::factory()->create();
        $user = User::factory()->create();
        $fechaInicio = Carbon::today()->format('Y-m-d');
        $fechaFin = Carbon::today()->addDays(7)->format('Y-m-d');

        // Crear logs con diferentes resultados
        SenasofiaplusValidationLog::factory()->count(5)->create([
            'aspirante_id' => $aspirante->id,
            'user_id' => $user->id,
            'resultado' => self::RESULTADO_EXITOSO,
            'fecha_accion' => Carbon::today()->addDays(1),
        ]);

        SenasofiaplusValidationLog::factory()->count(3)->create([
            'aspirante_id' => $aspirante->id,
            'user_id' => $user->id,
            'resultado' => self::RESULTADO_ERROR,
            'fecha_accion' => Carbon::today()->addDays(2),
        ]);

        SenasofiaplusValidationLog::factory()->count(2)->create([
            'aspirante_id' => $aspirante->id,
            'user_id' => $user->id,
            'resultado' => self::RESULTADO_ADVERTENCIA,
            'fecha_accion' => Carbon::today()->addDays(3),
        ]);

        $estadisticas = $this->repository->obtenerEstadisticas($fechaInicio, $fechaFin);

        $this->assertIsArray($estadisticas);
        $this->assertEquals(10, $estadisticas['total_validaciones']);
        $this->assertEquals(5, $estadisticas['exitosas']);
        $this->assertEquals(3, $estadisticas['errores']);
        $this->assertEquals(2, $estadisticas['advertencias']);
        $this->assertEquals(50.0, $estadisticas['tasa_exito']);
    }

    #[Test]
    public function puede_obtener_estadisticas_sin_logs()
    {
        $fechaInicio = Carbon::today()->format('Y-m-d');
        $fechaFin = Carbon::today()->addDays(7)->format('Y-m-d');

        $estadisticas = $this->repository->obtenerEstadisticas($fechaInicio, $fechaFin);

        $this->assertIsArray($estadisticas);
        $this->assertEquals(0, $estadisticas['total_validaciones']);
        $this->assertEquals(0, $estadisticas['exitosas']);
        $this->assertEquals(0, $estadisticas['errores']);
        $this->assertEquals(0, $estadisticas['advertencias']);
        $this->assertEquals(0, $estadisticas['tasa_exito']);
    }

    #[Test]
    public function puede_obtener_auditoria_completa()
    {
        $aspirante = AspiranteComplementario::factory()->create();
        $user = User::factory()->create();
        $fechaInicio = Carbon::today()->format('Y-m-d');
        $fechaFin = Carbon::today()->addDays(7)->format('Y-m-d');

        SenasofiaplusValidationLog::factory()->count(5)->create([
            'aspirante_id' => $aspirante->id,
            'user_id' => $user->id,
            'fecha_accion' => Carbon::today()->addDays(1),
        ]);

        $auditoria = $this->repository->obtenerAuditoria($fechaInicio, $fechaFin);

        $this->assertCount(5, $auditoria);
        $auditoria->each(function ($log) {
            $this->assertTrue($log->relationLoaded('aspirante'));
            $this->assertTrue($log->relationLoaded('user'));
            if ($log->aspirante) {
                $this->assertTrue($log->aspirante->relationLoaded('persona'));
            }
        });
    }

    #[Test]
    public function puede_obtener_auditoria_ordenada_por_fecha_desc()
    {
        $aspirante = AspiranteComplementario::factory()->create();
        $user = User::factory()->create();
        $fechaInicio = Carbon::today()->format('Y-m-d');
        $fechaFin = Carbon::today()->addDays(7)->format('Y-m-d');

        $log1 = SenasofiaplusValidationLog::factory()->create([
            'aspirante_id' => $aspirante->id,
            'user_id' => $user->id,
            'fecha_accion' => Carbon::today()->addDays(1),
        ]);

        $log2 = SenasofiaplusValidationLog::factory()->create([
            'aspirante_id' => $aspirante->id,
            'user_id' => $user->id,
            'fecha_accion' => Carbon::today()->addDays(3),
        ]);

        SenasofiaplusValidationLog::factory()->create([
            'aspirante_id' => $aspirante->id,
            'user_id' => $user->id,
            'fecha_accion' => Carbon::today()->addDays(2),
        ]);

        $auditoria = $this->repository->obtenerAuditoria($fechaInicio, $fechaFin);

        $this->assertEquals($log2->id, $auditoria->first()->id);
        $this->assertEquals($log1->id, $auditoria->last()->id);
    }

    #[Test]
    public function puede_obtener_logs_con_errores()
    {
        $aspirante = AspiranteComplementario::factory()->create();
        $user = User::factory()->create();
        $fechaInicio = Carbon::today()->format('Y-m-d');
        $fechaFin = Carbon::today()->addDays(7)->format('Y-m-d');

        // Crear logs con diferentes resultados
        SenasofiaplusValidationLog::factory()->count(3)->create([
            'aspirante_id' => $aspirante->id,
            'user_id' => $user->id,
            'resultado' => self::RESULTADO_ERROR,
            'fecha_accion' => Carbon::today()->addDays(1),
        ]);

        SenasofiaplusValidationLog::factory()->count(2)->create([
            'aspirante_id' => $aspirante->id,
            'user_id' => $user->id,
            'resultado' => self::RESULTADO_EXITOSO,
            'fecha_accion' => Carbon::today()->addDays(2),
        ]);

        $logs = $this->repository->obtenerLogsConErrores($fechaInicio, $fechaFin);

        $this->assertCount(3, $logs);
        $logs->each(function ($log) {
            $this->assertEquals(self::RESULTADO_ERROR, $log->resultado);
            $this->assertTrue($log->relationLoaded('aspirante'));
            $this->assertTrue($log->relationLoaded('user'));
            if ($log->aspirante) {
                $this->assertTrue($log->aspirante->relationLoaded('persona'));
            }
        });
    }

    #[Test]
    public function puede_obtener_logs_con_errores_ordenados_por_fecha_desc()
    {
        $aspirante = AspiranteComplementario::factory()->create();
        $user = User::factory()->create();
        $fechaInicio = Carbon::today()->format('Y-m-d');
        $fechaFin = Carbon::today()->addDays(7)->format('Y-m-d');

        $log1 = SenasofiaplusValidationLog::factory()->create([
            'aspirante_id' => $aspirante->id,
            'user_id' => $user->id,
            'resultado' => self::RESULTADO_ERROR,
            'fecha_accion' => Carbon::today()->addDays(1),
        ]);

        $log2 = SenasofiaplusValidationLog::factory()->create([
            'aspirante_id' => $aspirante->id,
            'user_id' => $user->id,
            'resultado' => self::RESULTADO_ERROR,
            'fecha_accion' => Carbon::today()->addDays(3),
        ]);

        $logs = $this->repository->obtenerLogsConErrores($fechaInicio, $fechaFin);

        $this->assertGreaterThanOrEqual(2, $logs->count());
        $logsIds = $logs->pluck('id')->toArray();
        $this->assertContains($log1->id, $logsIds);
        $this->assertContains($log2->id, $logsIds);
        $this->assertEquals($log2->id, $logs->first()->id);
    }

    #[Test]
    public function puede_obtener_logs_con_errores_filtra_solo_errores()
    {
        $aspirante = AspiranteComplementario::factory()->create();
        $user = User::factory()->create();
        $fechaInicio = Carbon::today()->format('Y-m-d');
        $fechaFin = Carbon::today()->addDays(7)->format('Y-m-d');

        SenasofiaplusValidationLog::factory()->count(2)->create([
            'aspirante_id' => $aspirante->id,
            'user_id' => $user->id,
            'resultado' => self::RESULTADO_ERROR,
            'fecha_accion' => Carbon::today()->addDays(1),
        ]);

        SenasofiaplusValidationLog::factory()->count(3)->create([
            'aspirante_id' => $aspirante->id,
            'user_id' => $user->id,
            'resultado' => self::RESULTADO_EXITOSO,
            'fecha_accion' => Carbon::today()->addDays(2),
        ]);

        SenasofiaplusValidationLog::factory()->count(1)->create([
            'aspirante_id' => $aspirante->id,
            'user_id' => $user->id,
            'resultado' => self::RESULTADO_ADVERTENCIA,
            'fecha_accion' => Carbon::today()->addDays(3),
        ]);

        $logs = $this->repository->obtenerLogsConErrores($fechaInicio, $fechaFin);

        $this->assertCount(2, $logs);
        $logs->each(function ($log) {
            $this->assertEquals(self::RESULTADO_ERROR, $log->resultado);
        });
    }

    #[Test]
    public function puede_obtener_logs_por_aspirante_sin_logs()
    {
        $aspirante = AspiranteComplementario::factory()->create();

        $logs = $this->repository->obtenerLogsPorAspirante($aspirante->id);

        $this->assertCount(0, $logs);
    }

    #[Test]
    public function puede_obtener_auditoria_sin_logs()
    {
        $fechaInicio = Carbon::today()->format('Y-m-d');
        $fechaFin = Carbon::today()->addDays(7)->format('Y-m-d');

        $auditoria = $this->repository->obtenerAuditoria($fechaInicio, $fechaFin);

        $this->assertCount(0, $auditoria);
    }

    #[Test]
    public function puede_obtener_logs_con_errores_sin_logs()
    {
        $fechaInicio = Carbon::today()->format('Y-m-d');
        $fechaFin = Carbon::today()->addDays(7)->format('Y-m-d');

        $logs = $this->repository->obtenerLogsConErrores($fechaInicio, $fechaFin);

        $this->assertCount(0, $logs);
    }
}

