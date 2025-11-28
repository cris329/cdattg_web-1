<?php

namespace Tests\Unit;

use App\Models\FichaCaracterizacion;
use App\Models\User;
use App\Repositories\InstructorFichaCaracterizacionRepository;
use App\Repositories\InstructorRepository;
use App\Repositories\ParametroRepository;
use App\Repositories\PersonaRepository;
use App\Services\AsistenceQrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AsistenceQrServiceTest extends TestCase
{
    use RefreshDatabase;

    private AsistenceQrService $service;

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

        $this->service = new AsistenceQrService(
            app(InstructorFichaCaracterizacionRepository::class),
            app(InstructorRepository::class),
            app(PersonaRepository::class),
            app(ParametroRepository::class)
        );
    }

    #[Test]
    public function obtiene_dias_formacion(): void
    {
        $dias = $this->service->getDiasFormacion();

        $this->assertIsIterable($dias);
    }

    #[Test]
    public function obtiene_datos_caracterizacion(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        $user = User::factory()->create();

        $datos = $this->service->obtenerDatosCaracterizacion($ficha->id, $user);

        $this->assertIsArray($datos);
        $this->assertArrayHasKey('fichaCaracterizacion', $datos);
        $this->assertArrayHasKey('aprendices', $datos);
    }

    #[Test]
    public function retorna_null_si_ficha_no_existe(): void
    {
        $user = User::factory()->create();

        $datos = $this->service->obtenerDatosCaracterizacion(99999, $user);

        $this->assertIsArray($datos);
        $this->assertNull($datos['fichaCaracterizacion']);
    }
}

