<?php

namespace Tests\Unit\Repositories;

use App\Repositories\DepartamentoRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DepartamentoRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected DepartamentoRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
        ]);

        $this->repository = new DepartamentoRepository;
    }

    #[Test]
    public function puede_obtener_todos_los_departamentos(): void
    {
        $resultado = $this->repository->obtenerTodos();

        $this->assertIsIterable($resultado);
    }

    #[Test]
    public function puede_obtener_departamentos_por_pais(): void
    {
        $pais = Pais::first();
        if ($pais) {
            $resultado = $this->repository->obtenerPorPais($pais->id);

            $this->assertIsIterable($resultado);
        }
    }

    #[Test]
    public function puede_buscar_departamentos(): void
    {
        $resultado = $this->repository->buscar('Test');

        $this->assertIsIterable($resultado);
    }
}
