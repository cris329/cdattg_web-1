<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\BloqueService;
use App\Models\Bloque;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class BloqueServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BloqueService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->service = app(BloqueService::class);
    }

    #[Test]
    public function puede_listar_bloques(): void
    {
        Bloque::factory()->count(5)->create();

        $resultado = $this->service->listar(10);

        $this->assertGreaterThanOrEqual(5, $resultado->total());
    }

    #[Test]
    public function puede_obtener_bloques_por_sede(): void
    {
        $sede = \App\Models\Sede::factory()->create();
        Bloque::factory()->count(2)->create(['sede_id' => $sede->id]);

        $resultado = $this->service->obtenerPorSede($sede->id);

        $this->assertCount(2, $resultado);
    }
}

