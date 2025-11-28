<?php

namespace Tests\Unit\Repositories;

use App\Models\Sede;
use App\Repositories\SedeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SedeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected SedeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
        ]);

        $this->repository = new SedeRepository;
    }

    #[Test]
    public function puede_obtener_sedes_activas(): void
    {
        Sede::factory()->count(3)->create(['status' => true]);
        Sede::factory()->count(2)->create(['status' => false]);

        $resultado = $this->repository->obtenerActivas();

        $this->assertCount(3, $resultado);
        $resultado->each(function ($sede) {
            $this->assertTrue($sede->status);
        });
    }

    #[Test]
    public function puede_encontrar_sede_con_relaciones(): void
    {
        $sede = Sede::factory()->create();

        $resultado = $this->repository->encontrarConRelaciones($sede->id);

        $this->assertNotNull($resultado);
        $this->assertEquals($sede->id, $resultado->id);
    }
}
