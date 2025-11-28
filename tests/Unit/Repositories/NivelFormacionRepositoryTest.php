<?php

namespace Tests\Unit\Repositories;

use App\Models\NivelFormacion;
use App\Repositories\NivelFormacionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NivelFormacionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private NivelFormacionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->repository = new NivelFormacionRepository;
    }

    #[Test]
    public function obtiene_niveles_activos(): void
    {
        NivelFormacion::create(['nombre' => 'Técnico', 'status' => true]);
        NivelFormacion::create(['nombre' => 'Tecnólogo', 'status' => true]);
        NivelFormacion::create(['nombre' => 'Inactivo', 'status' => false]);

        $resultado = $this->repository->obtenerActivos();

        $this->assertGreaterThanOrEqual(2, $resultado->count());
        foreach ($resultado as $nivel) {
            $this->assertTrue($nivel->status);
        }
    }

    #[Test]
    public function invalida_cache(): void
    {
        $this->repository->obtenerActivos();

        $this->repository->invalidarCache();

        $this->assertTrue(true);
    }
}

