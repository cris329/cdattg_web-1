<?php

namespace Tests\Complementarios\Unit\Repositories;

use App\Models\AspiranteComplementario;
use App\Models\SenasofiaplusValidationLog;
use App\Models\User;
use App\Repositories\SenasofiaplusValidationLogRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SenasofiaplusValidationLogRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SenasofiaplusValidationLogRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\TemaSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
        ]);

        $this->repository = new SenasofiaplusValidationLogRepository;
    }

    #[Test]
    public function registra_log_validacion(): void
    {
        $aspirante = AspiranteComplementario::factory()->create();
        $user = User::factory()->create();

        $datos = [
            'aspirante_id' => $aspirante->id,
            'accion' => 'validar',
            'resultado' => 'exitoso',
            'mensaje' => 'Validación exitosa',
            'user_id' => $user->id,
            'fecha_accion' => now(),
        ];

        $log = $this->repository->registrar($datos);

        $this->assertInstanceOf(SenasofiaplusValidationLog::class, $log);
    }

    #[Test]
    public function obtiene_estadisticas(): void
    {
        $fechaInicio = Carbon::today()->format('Y-m-d');
        $fechaFin = Carbon::today()->addDays(7)->format('Y-m-d');

        $estadisticas = $this->repository->obtenerEstadisticas($fechaInicio, $fechaFin);

        $this->assertIsArray($estadisticas);
    }

    #[Test]
    public function obtiene_auditoria(): void
    {
        $fechaInicio = Carbon::today()->format('Y-m-d');
        $fechaFin = Carbon::today()->addDays(7)->format('Y-m-d');

        $auditoria = $this->repository->obtenerAuditoria($fechaInicio, $fechaFin);

        $this->assertIsIterable($auditoria);
    }

    #[Test]
    public function obtiene_logs_con_errores(): void
    {
        $fechaInicio = Carbon::today()->format('Y-m-d');
        $fechaFin = Carbon::today()->addDays(7)->format('Y-m-d');

        $logs = $this->repository->obtenerLogsConErrores($fechaInicio, $fechaFin);

        $this->assertIsIterable($logs);
    }
}

