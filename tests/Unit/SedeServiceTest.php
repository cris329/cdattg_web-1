<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SedeService;
use App\Models\Sede;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class SedeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SedeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
        ]);

        $this->service = app(SedeService::class);
    }

    #[Test]
    public function puede_listar_sedes(): void
    {
        Sede::factory()->count(5)->create();

        $resultado = $this->service->listar(10);

        $this->assertGreaterThanOrEqual(5, $resultado->total());
    }

    #[Test]
    public function puede_obtener_sedes_por_regional(): void
    {
        $regional = \App\Models\Regional::first();
        if ($regional) {
            Sede::factory()->count(2)->create([
                'regional_id' => $regional->id,
                'status' => 1,
            ]);

            $resultado = $this->service->obtenerPorRegional($regional->id);

            $this->assertNotEmpty($resultado);
        }
    }
}

