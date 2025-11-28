<?php

namespace Tests\Unit;

use App\Models\FichaCaracterizacion;
use App\Repositories\FichaCaracterizacionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FichaCaracterizacionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected FichaCaracterizacionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);

        $this->repository = app(FichaCaracterizacionRepository::class);
    }

    #[Test]
    public function puede_obtener_ficha_por_id(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();

        $resultado = $this->repository->getFichaCaracterizacion($ficha->id);

        $this->assertNotNull($resultado);
        $this->assertEquals($ficha->id, $resultado->id);
    }

    #[Test]
    public function retorna_null_si_ficha_no_existe(): void
    {
        $resultado = $this->repository->getFichaCaracterizacion(99999);

        $this->assertNull($resultado);
    }
}
