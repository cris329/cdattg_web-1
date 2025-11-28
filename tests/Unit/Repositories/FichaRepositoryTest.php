<?php

namespace Tests\Unit\Repositories;

use App\Models\FichaCaracterizacion;
use App\Repositories\FichaRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FichaRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected FichaRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);

        $this->repository = new FichaRepository;
    }

    #[Test]
    public function puede_obtener_fichas_activas(): void
    {
        FichaCaracterizacion::factory()->count(3)->create(['status' => 1]);
        FichaCaracterizacion::factory()->count(2)->create(['status' => 0]);

        $resultado = $this->repository->obtenerActivas();

        $this->assertCount(3, $resultado);
    }

    #[Test]
    public function puede_obtener_fichas_con_filtros(): void
    {
        FichaCaracterizacion::factory()->count(5)->create(['status' => 1]);

        $resultado = $this->repository->obtenerConFiltros(['per_page' => 10]);

        $this->assertIsIterable($resultado);
    }
}
