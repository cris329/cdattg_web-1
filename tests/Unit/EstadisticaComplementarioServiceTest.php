<?php

namespace Tests\Unit;

use App\Models\AspiranteComplementario;
use App\Services\EstadisticaComplementarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EstadisticaComplementarioServiceTest extends TestCase
{
    use RefreshDatabase;

    private EstadisticaComplementarioService $service;

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

        $this->service = new EstadisticaComplementarioService;
    }

    #[Test]
    public function obtiene_estadisticas_reales(): void
    {
        $estadisticas = $this->service->obtenerEstadisticasReales();

        $this->assertIsArray($estadisticas);
        $this->assertArrayHasKey('total_aspirantes', $estadisticas);
        $this->assertArrayHasKey('aspirantes_aceptados', $estadisticas);
        $this->assertArrayHasKey('programas_activos', $estadisticas);
    }

    #[Test]
    public function obtiene_estadisticas_filtradas(): void
    {
        $filtros = [
            'fecha_inicio' => now()->subMonth()->format('Y-m-d'),
            'fecha_fin' => now()->format('Y-m-d'),
        ];

        $estadisticas = $this->service->obtenerEstadisticasFiltradas($filtros);

        $this->assertIsArray($estadisticas);
        $this->assertArrayHasKey('total_filtrado', $estadisticas);
    }

    #[Test]
    public function genera_reporte_tendencias(): void
    {
        $reporte = $this->service->generarReporteTendencias(6);

        $this->assertIsIterable($reporte);
    }
}

