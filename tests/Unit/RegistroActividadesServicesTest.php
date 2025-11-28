<?php

namespace Tests\Unit;

use App\Models\InstructorFichaCaracterizacion;
use App\Repositories\CompetenciaRepository;
use App\Repositories\EvidenciasRepository;
use App\Repositories\FichaCaracterizacionRepository;
use App\Repositories\ResultadosAprendizajeRepository;
use App\Services\RegistroActividadesServices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistroActividadesServicesTest extends TestCase
{
    use RefreshDatabase;

    protected RegistroActividadesServices $service;

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

        $fichaRepo = app(FichaCaracterizacionRepository::class);
        $rapRepo = app(ResultadosAprendizajeRepository::class);
        $competenciaRepo = app(CompetenciaRepository::class);
        $evidenciaRepo = app(EvidenciasRepository::class);

        $this->service = new RegistroActividadesServices(
            $fichaRepo,
            $rapRepo,
            $competenciaRepo,
            $evidenciaRepo
        );
    }

    #[Test]
    public function puede_instanciar_servicio(): void
    {
        $this->assertInstanceOf(RegistroActividadesServices::class, $this->service);
    }

    #[Test]
    public function obtiene_actividades(): void
    {
        $instructorFicha = InstructorFichaCaracterizacion::factory()->create();

        $actividades = $this->service->getActividades($instructorFicha);

        $this->assertIsIterable($actividades);
    }
}


