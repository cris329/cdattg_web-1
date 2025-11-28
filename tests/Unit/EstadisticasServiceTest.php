<?php

namespace Tests\Unit;

use App\Services\EstadisticasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EstadisticasServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EstadisticasService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
        ]);

        $this->service = app(EstadisticasService::class);
    }

    #[Test]
    public function puede_obtener_dashboard_general(): void
    {
        $resultado = $this->service->obtenerDashboardGeneral();

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('roles', $resultado);
        $this->assertArrayHasKey('asistencias_hoy', $resultado);
    }

    #[Test]
    public function dashboard_incluye_estadisticas_de_roles(): void
    {
        $resultado = $this->service->obtenerDashboardGeneral();

        $this->assertArrayHasKey('roles', $resultado);
        $this->assertArrayHasKey('instructores', $resultado['roles']);
        $this->assertArrayHasKey('aprendices', $resultado['roles']);
    }
}
