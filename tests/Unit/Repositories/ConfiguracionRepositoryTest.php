<?php

namespace Tests\Unit\Repositories;

use App\Repositories\ConfiguracionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConfiguracionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected ConfiguracionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);

        $this->repository = new ConfiguracionRepository;
    }

    #[Test]
    public function puede_obtener_fichas_activas(): void
    {
        \App\Models\FichaCaracterizacion::factory()->count(2)->create(['status' => 1]);

        $resultado = $this->repository->obtenerFichasActivas();

        $this->assertIsIterable($resultado);
    }

    #[Test]
    public function puede_obtener_regionales_activas(): void
    {
        $resultado = $this->repository->obtenerRegionalesActivas();

        $this->assertIsIterable($resultado);
    }
}
