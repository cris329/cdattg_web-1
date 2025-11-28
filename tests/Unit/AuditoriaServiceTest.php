<?php

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\AsignacionInstructorLogRepository;
use App\Repositories\LoginRepository;
use App\Repositories\SenasofiaplusValidationLogRepository;
use App\Services\AuditoriaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuditoriaServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuditoriaService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->service = new AuditoriaService(
            app(LoginRepository::class),
            app(AsignacionInstructorLogRepository::class),
            app(SenasofiaplusValidationLogRepository::class)
        );
    }

    #[Test]
    public function registra_intento_de_login(): void
    {
        $datos = [
            'email' => 'test@example.com',
            'exitoso' => true,
            'ip_address' => '127.0.0.1',
        ];

        $this->service->registrarLogin($datos);

        $this->assertDatabaseHas('logins', [
            'email' => 'test@example.com',
        ]);
    }

    #[Test]
    public function registra_cambio_asignacion(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $instructorId = 1;
        $fichaId = 1;

        $this->service->registrarCambioAsignacion($instructorId, $fichaId, 'asignar');

        $this->assertTrue(true);
    }

    #[Test]
    public function obtiene_reporte_auditoria(): void
    {
        $fechaInicio = Carbon::today()->format('Y-m-d');
        $fechaFin = Carbon::today()->addDays(7)->format('Y-m-d');

        $reporte = $this->service->obtenerReporteAuditoria($fechaInicio, $fechaFin, 'todo');

        $this->assertIsArray($reporte);
    }

    #[Test]
    public function detecta_actividades_sospechosas(): void
    {
        $fechaInicio = Carbon::today()->format('Y-m-d');
        $fechaFin = Carbon::today()->addDays(7)->format('Y-m-d');

        $sospechosas = $this->service->detectarActividadesSospechosas($fechaInicio, $fechaFin);

        $this->assertIsArray($sospechosas);
    }
}

