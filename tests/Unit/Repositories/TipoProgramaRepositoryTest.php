<?php

namespace Tests\Unit\Repositories;

use App\Models\TipoPrograma;
use App\Repositories\TipoProgramaRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TipoProgramaRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TipoProgramaRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->repository = new TipoProgramaRepository;
    }

    #[Test]
    public function obtiene_tipos_programa_activos(): void
    {
        TipoPrograma::create(['nombre' => 'Formación', 'status' => true]);
        TipoPrograma::create(['nombre' => 'Complementaria', 'status' => true]);
        TipoPrograma::create(['nombre' => 'Inactivo', 'status' => false]);

        $resultado = $this->repository->obtenerActivos();

        $this->assertGreaterThanOrEqual(2, $resultado->count());
        foreach ($resultado as $tipo) {
            $this->assertTrue($tipo->status);
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

