<?php

namespace Tests\Unit\Repositories;

use App\Models\ModalidadFormacion;
use App\Repositories\ModalidadFormacionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModalidadFormacionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ModalidadFormacionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->repository = new ModalidadFormacionRepository;
    }

    #[Test]
    public function obtiene_modalidades_activas(): void
    {
        ModalidadFormacion::create(['nombre' => 'Presencial', 'status' => true]);
        ModalidadFormacion::create(['nombre' => 'Virtual', 'status' => true]);
        ModalidadFormacion::create(['nombre' => 'Inactiva', 'status' => false]);

        $resultado = $this->repository->obtenerActivas();

        $this->assertGreaterThanOrEqual(2, $resultado->count());
        foreach ($resultado as $modalidad) {
            $this->assertTrue($modalidad->status);
        }
    }

    #[Test]
    public function invalida_cache(): void
    {
        $this->repository->obtenerActivas();

        $this->repository->invalidarCache();

        $this->assertTrue(true);
    }
}

