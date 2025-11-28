<?php

namespace Tests\Unit\Repositories;

use App\Models\Piso;
use App\Repositories\PisoRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PisoRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected PisoRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->repository = new PisoRepository;
    }

    #[Test]
    public function puede_obtener_pisos_por_sede(): void
    {
        $sede = \App\Models\Sede::factory()->create();
        Piso::factory()->count(2)->create(['sede_id' => $sede->id]);

        $resultado = $this->repository->obtenerPorSede($sede->id);

        $this->assertCount(2, $resultado);
    }
}
