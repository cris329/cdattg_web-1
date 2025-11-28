<?php

namespace Tests\Unit\Repositories;

use App\Repositories\MunicipioRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MunicipioRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected MunicipioRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
        ]);

        $this->repository = new MunicipioRepository;
    }

    #[Test]
    public function puede_obtener_municipios_por_departamento(): void
    {
        $departamento = \App\Models\Departamento::first();
        if ($departamento) {
            $resultado = $this->repository->obtenerPorDepartamento($departamento->id);

            $this->assertIsIterable($resultado);
        }
    }

    #[Test]
    public function puede_buscar_municipios(): void
    {
        $resultado = $this->repository->buscar('Test');

        $this->assertIsIterable($resultado);
    }
}
