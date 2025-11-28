<?php

namespace Tests\Unit\Repositories;

use App\Models\Bloque;
use App\Repositories\BloqueRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BloqueRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected BloqueRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->repository = new BloqueRepository;
    }

    #[Test]
    public function puede_obtener_bloques_por_sede(): void
    {
        $sede = \App\Models\Sede::factory()->create();
        Bloque::factory()->count(2)->create(['sede_id' => $sede->id]);

        $resultado = $this->repository->obtenerPorSede($sede->id);

        $this->assertCount(2, $resultado);
    }
}
