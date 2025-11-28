<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AmbienteService;
use App\Models\Ambiente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class AmbienteServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AmbienteService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->service = app(AmbienteService::class);
    }

    #[Test]
    public function puede_listar_ambientes(): void
    {
        Ambiente::factory()->count(5)->create();

        $resultado = $this->service->listar(10);

        $this->assertGreaterThanOrEqual(5, $resultado->total());
    }

    #[Test]
    public function puede_obtener_ambiente(): void
    {
        $ambiente = Ambiente::factory()->create();

        $resultado = $this->service->obtener($ambiente->id);

        $this->assertNotNull($resultado);
        $this->assertEquals($ambiente->id, $resultado->id);
    }
}

