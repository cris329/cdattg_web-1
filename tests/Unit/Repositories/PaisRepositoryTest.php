<?php

namespace Tests\Unit\Repositories;

use App\Repositories\PaisRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaisRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected PaisRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\PaisSeeder::class,
        ]);

        $this->repository = new PaisRepository;
    }

    #[Test]
    public function puede_obtener_todos_los_paises(): void
    {
        $resultado = $this->repository->obtenerTodos();

        $this->assertNotEmpty($resultado);
    }
}
