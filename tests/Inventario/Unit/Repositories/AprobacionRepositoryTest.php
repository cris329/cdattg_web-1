<?php

namespace Tests\Inventario\Unit\Repositories;

use Tests\TestCase;
use App\Inventario\Repositories\Aprobacion\AprobacionRepository;
use App\Models\Inventario\Aprobacion;
use App\Models\Inventario\DetalleOrden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class AprobacionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected AprobacionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AprobacionRepository();
        
        // Ejecutar seeders necesarios
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\TemaSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\PersonaSeeder::class,
            \Database\Seeders\UsersSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
            \Database\Seeders\SedeSeeder::class,
            \Database\Seeders\BloqueSeeder::class,
            \Database\Seeders\PisoSeeder::class,
            \Database\Seeders\AmbienteSeeder::class,
        ]);
    }

    #[Test]
    public function puede_crear_aprobacion()
    {
        $detalleOrden = DetalleOrden::factory()->create();
        $datos = [
            'detalle_orden_id' => $detalleOrden->id,
            'estado_aprobacion_id' => 49,
            'user_create_id' => 1,
            'user_update_id' => 1,
        ];

        $resultado = $this->repository->crear($datos);

        $this->assertInstanceOf(Aprobacion::class, $resultado);
        $this->assertEquals($detalleOrden->id, $resultado->detalle_orden_id);
        $this->assertEquals(49, $resultado->estado_aprobacion_id);
    }
}

