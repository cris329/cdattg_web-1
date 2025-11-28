<?php

namespace Tests\Unit\Repositories;

use App\Repositories\ParametroRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ParametroRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected ParametroRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->repository = new ParametroRepository;
    }

    #[Test]
    public function puede_obtener_dias_formacion(): void
    {
        $resultado = $this->repository->getDiasFormacion();

        $this->assertIsArray($resultado);
    }
}
