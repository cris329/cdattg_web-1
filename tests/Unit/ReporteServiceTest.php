<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ReporteService;
use App\Repositories\AsistenciaAprendizRepository;
use App\Repositories\AprendizRepository;
use App\Repositories\FichaRepository;
use App\Models\FichaCaracterizacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class ReporteServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ReporteService $service;
    protected $mockAsistenciaRepo;
    protected $mockAprendizRepo;
    protected $mockFichaRepo;

    protected function setUp(): void
    {
        parent::setUp();

        // Ejecutar seeders necesarios para las pruebas
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\PersonaSeeder::class,
            \Database\Seeders\UsersSeeder::class,
        ]);

        $this->mockAsistenciaRepo = Mockery::mock(AsistenciaAprendizRepository::class);
        $this->mockAprendizRepo = Mockery::mock(AprendizRepository::class);
        $this->mockFichaRepo = Mockery::mock(FichaRepository::class);

        $this->service = new ReporteService(
            $this->mockAsistenciaRepo,
            $this->mockAprendizRepo,
            $this->mockFichaRepo
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function puede_generar_reporte_de_asistencia()
    {
        $ficha = FichaCaracterizacion::factory()->create();
        $fechaInicio = '2024-01-01';
        $fechaFin = '2024-01-31';

        $this->mockAsistenciaRepo
            ->shouldReceive('obtenerPorFichaYFechas')
            ->once()
            ->andReturn(new Collection([]));

        $this->mockAsistenciaRepo
            ->shouldReceive('obtenerEstadisticas')
            ->once()
            ->andReturn(['total_registros' => 0]);

        $this->mockFichaRepo
            ->shouldReceive('encontrarConRelaciones')
            ->once()
            ->with($ficha->id)
            ->andReturn($ficha->load(['programaFormacion', 'jornadaFormacion']));

        $resultado = $this->service->generarReporteAsistencia($ficha->id, $fechaInicio, $fechaFin, 'array');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('ficha', $resultado);
        $this->assertArrayHasKey('periodo', $resultado);
        $this->assertArrayHasKey('estadisticas', $resultado);
    }

    #[Test]
    public function puede_generar_reporte_de_aprendices()
    {
        $ficha = FichaCaracterizacion::factory()->create();

        $this->mockAprendizRepo
            ->shouldReceive('obtenerPorFicha')
            ->once()
            ->andReturn(new Collection([]));

        $this->mockFichaRepo
            ->shouldReceive('encontrarConRelaciones')
            ->once()
            ->with($ficha->id)
            ->andReturn($ficha->load('programaFormacion'));

        $resultado = $this->service->generarReporteAprendices($ficha->id, 'array');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('total_aprendices', $resultado);
        $this->assertArrayHasKey('aprendices_activos', $resultado);
    }

    #[Test]
    public function puede_generar_reporte_consolidado_mes()
    {
        $mes = 1;
        $anio = 2024;

        $this->mockFichaRepo
            ->shouldReceive('obtenerVigentes')
            ->once()
            ->andReturn(new Collection([]));

        $resultado = $this->service->generarReporteConsolidadoMes($mes, $anio);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('periodo', $resultado);
        $this->assertArrayHasKey('total_fichas', $resultado);
        $this->assertArrayHasKey('fichas', $resultado);
    }
}
