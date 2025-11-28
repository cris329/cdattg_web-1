<?php

namespace Tests\Unit\Repositories;

use App\Models\CentroFormacion;
use App\Repositories\CentroFormacionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CentroFormacionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected CentroFormacionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
        ]);

        $this->repository = new CentroFormacionRepository;
    }

    #[Test]
    public function puede_obtener_centros_activos(): void
    {
        CentroFormacion::factory()->count(3)->create(['status' => true]);
        CentroFormacion::factory()->count(2)->create(['status' => false]);

        $resultado = $this->repository->obtenerActivos();

        $this->assertCount(3, $resultado);
        $resultado->each(function ($centro) {
            $this->assertTrue($centro->status);
        });
    }

    #[Test]
    public function puede_obtener_centros_por_regional(): void
    {
        $regional = \App\Models\Regional::first();
        if ($regional) {
            CentroFormacion::factory()->create([
                'regional_id' => $regional->id,
                'status' => true,
            ]);

            $resultado = $this->repository->obtenerPorRegional($regional->id);

            $this->assertNotEmpty($resultado);
        }
    }
}
