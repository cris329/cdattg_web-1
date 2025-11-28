<?php

namespace Tests\Unit;

use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->service = app(DashboardService::class);
    }

    #[Test]
    public function puede_obtener_dashboard_administrativo(): void
    {
        $resultado = $this->service->obtenerDashboardAdministrativo();

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('resumen', $resultado);
        $this->assertArrayHasKey('tendencias', $resultado);
    }
}
