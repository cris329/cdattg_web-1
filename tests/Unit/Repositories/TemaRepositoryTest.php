<?php

namespace Tests\Unit\Repositories;

use App\Models\Tema;
use App\Repositories\TemaRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TemaRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected TemaRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->repository = new TemaRepository;
    }

    #[Test]
    public function puede_obtener_temas_con_parametros(): void
    {
        $temas = $this->repository->obtenerConParametros();

        $this->assertIsIterable($temas);
    }

    #[Test]
    public function puede_encontrar_tema_con_parametros(): void
    {
        $tema = Tema::first();

        if ($tema) {
            $resultado = $this->repository->encontrarConParametros($tema->id);

            $this->assertNotNull($resultado);
        }
    }
}
