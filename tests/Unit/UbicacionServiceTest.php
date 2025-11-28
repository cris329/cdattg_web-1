<?php

namespace Tests\Unit;

use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Pais;
use App\Services\UbicacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UbicacionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UbicacionService $service;

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

        $this->service = app(UbicacionService::class);
    }

    #[Test]
    public function puede_obtener_paises_activos(): void
    {
        $paises = $this->service->obtenerPaisesActivos();

        $this->assertNotEmpty($paises);
    }

    #[Test]
    public function puede_obtener_departamentos_por_pais(): void
    {
        $pais = Pais::first();
        if ($pais) {
            Departamento::factory()->create(['pais_id' => $pais->id]);

            $resultado = $this->service->obtenerDepartamentosPorPais($pais->id);

            $this->assertNotEmpty($resultado);
        }
    }

    #[Test]
    public function puede_obtener_municipios_por_departamento(): void
    {
        $departamento = Departamento::first();
        if ($departamento) {
            Municipio::factory()->create(['departamento_id' => $departamento->id]);

            $resultado = $this->service->obtenerMunicipiosPorDepartamento($departamento->id);

            $this->assertNotEmpty($resultado);
        }
    }
}
