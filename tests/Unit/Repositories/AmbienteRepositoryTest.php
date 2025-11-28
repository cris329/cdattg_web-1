<?php

namespace Tests\Unit\Repositories;

use App\Models\Ambiente;
use App\Repositories\AmbienteRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AmbienteRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected AmbienteRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->repository = new AmbienteRepository;
    }

    #[Test]
    public function puede_obtener_ambientes_activos(): void
    {
        Ambiente::factory()->count(3)->create(['status' => true]);
        Ambiente::factory()->count(2)->create(['status' => false]);

        $resultado = $this->repository->obtenerActivos();

        $this->assertCount(3, $resultado);
        $resultado->each(function ($ambiente) {
            $this->assertTrue($ambiente->status);
        });
    }

    #[Test]
    public function puede_obtener_ambientes_por_sede(): void
    {
        $sede = \App\Models\Sede::factory()->create();
        Ambiente::factory()->create([
            'sede_id' => $sede->id,
            'status' => true,
        ]);

        $resultado = $this->repository->obtenerPorSede($sede->id);

        $this->assertNotEmpty($resultado);
    }
}
