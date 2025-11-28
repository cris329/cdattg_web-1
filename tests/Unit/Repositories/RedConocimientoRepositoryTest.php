<?php

namespace Tests\Unit\Repositories;

use App\Models\RedConocimiento;
use App\Repositories\RedConocimientoRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RedConocimientoRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected RedConocimientoRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
        ]);

        $this->repository = new RedConocimientoRepository;
    }

    #[Test]
    public function puede_obtener_redes_activas(): void
    {
        RedConocimiento::factory()->count(3)->create(['status' => true]);
        RedConocimiento::factory()->count(2)->create(['status' => false]);

        $resultado = $this->repository->obtenerActivas();

        $this->assertCount(3, $resultado);
        $resultado->each(function ($red) {
            $this->assertTrue($red->status);
        });
    }

    #[Test]
    public function puede_obtener_redes_por_regional(): void
    {
        $regional = \App\Models\Regional::first();
        if ($regional) {
            RedConocimiento::factory()->create([
                'regionals_id' => $regional->id,
                'status' => true,
            ]);

            $resultado = $this->repository->obtenerPorRegional($regional->id);

            $this->assertNotEmpty($resultado);
        }
    }
}
