<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PisoService;
use App\Models\Piso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class PisoServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PisoService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->service = app(PisoService::class);
    }

    #[Test]
    public function puede_listar_pisos(): void
    {
        Piso::factory()->count(5)->create();

        $resultado = $this->service->listar(10);

        $this->assertGreaterThanOrEqual(5, $resultado->total());
    }

    #[Test]
    public function puede_obtener_pisos_por_bloque(): void
    {
        $bloque = \App\Models\Bloque::factory()->create();
        Piso::factory()->count(2)->create(['bloque_id' => $bloque->id]);

        $resultado = $this->service->obtenerPorBloque($bloque->id);

        $this->assertCount(2, $resultado);
    }
}

