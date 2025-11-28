<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ParametroService;
use App\Models\Parametro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ParametroServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ParametroService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->service = app(ParametroService::class);
    }

    #[Test]
    public function puede_listar_parametros(): void
    {
        Parametro::factory()->count(5)->create();

        $resultado = $this->service->listar(10);

        $this->assertGreaterThanOrEqual(5, $resultado->total());
    }

    #[Test]
    public function puede_obtener_parametros_activos(): void
    {
        Parametro::factory()->count(3)->create(['status' => 1]);
        Parametro::factory()->count(2)->create(['status' => 0]);

        $resultado = $this->service->obtenerActivos();

        $this->assertCount(3, $resultado);
    }
}

