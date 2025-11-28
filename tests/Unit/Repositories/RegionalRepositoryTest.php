<?php

namespace Tests\Unit\Repositories;

use App\Models\Regional;
use App\Repositories\RegionalRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegionalRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected RegionalRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->repository = new RegionalRepository;
    }

    #[Test]
    public function puede_obtener_regionales_activas(): void
    {
        Regional::factory()->count(3)->create(['status' => true]);
        Regional::factory()->count(2)->create(['status' => false]);

        $resultado = $this->repository->obtenerActivas();

        $this->assertCount(3, $resultado);
        $resultado->each(function ($regional) {
            $this->assertTrue($regional->status);
        });
    }

    #[Test]
    public function puede_encontrar_regional(): void
    {
        $regional = Regional::factory()->create();

        $resultado = $this->repository->encontrar($regional->id);

        $this->assertNotNull($resultado);
        $this->assertEquals($regional->id, $resultado->id);
    }

    #[Test]
    public function puede_buscar_regionales(): void
    {
        Regional::factory()->create(['nombre' => 'Regional Test', 'status' => true]);

        $resultado = $this->repository->buscar('Test');

        $this->assertNotEmpty($resultado);
    }
}
