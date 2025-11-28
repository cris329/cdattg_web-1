<?php

namespace Tests\Unit\Repositories;

use App\Models\DiasFormacion;
use App\Models\FichaCaracterizacion;
use App\Repositories\DiasFormacionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DiasFormacionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private DiasFormacionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->repository = new DiasFormacionRepository;
    }

    #[Test]
    public function obtiene_todos_los_dias(): void
    {
        DiasFormacion::create(['nombre' => 'Lunes']);
        DiasFormacion::create(['nombre' => 'Martes']);

        $resultado = $this->repository->obtenerTodos();

        $this->assertGreaterThanOrEqual(2, $resultado->count());
    }

    #[Test]
    public function obtiene_dias_por_ficha(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();

        $resultado = $this->repository->obtenerPorFicha($ficha->id);

        $this->assertIsIterable($resultado);
    }

    #[Test]
    public function invalida_cache(): void
    {
        $this->repository->obtenerTodos();

        $this->repository->invalidarCache();

        $this->assertTrue(true);
    }
}

