<?php

namespace Tests\Inventario\Unit\Repositories;

use Tests\TestCase;
use App\Inventario\Repositories\Orden\DetalleOrdenRepository;
use App\Models\Inventario\Orden;
use App\Models\Inventario\DetalleOrden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class DetalleOrdenRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected DetalleOrdenRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DetalleOrdenRepository();
        
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
    public function puede_crear_detalle_orden()
    {
        $orden = Orden::factory()->create();
        $producto = \App\Models\Inventario\Producto::factory()->create();

        $datos = [
            'orden_id' => $orden->id,
            'producto_id' => $producto->id,
            'cantidad' => 5,
            'estado_orden_id' => 46,
            'user_create_id' => 1,
            'user_update_id' => 1,
        ];

        $resultado = $this->repository->crear($datos);

        $this->assertInstanceOf(DetalleOrden::class, $resultado);
        $this->assertEquals($orden->id, $resultado->orden_id);
        $this->assertEquals(5, $resultado->cantidad);
    }

    #[Test]
    public function puede_actualizar_detalle_orden()
    {
        $detalleOrden = DetalleOrden::factory()->create(['cantidad' => 5]);

        $resultado = $this->repository->actualizar($detalleOrden, ['cantidad' => 10]);

        $this->assertTrue($resultado);
        $this->assertEquals(10, $detalleOrden->fresh()->cantidad);
    }

    #[Test]
    public function puede_eliminar_detalle_orden()
    {
        $detalleOrden = DetalleOrden::factory()->create();

        $resultado = $this->repository->eliminar($detalleOrden);

        $this->assertTrue($resultado);
        $this->assertNull(DetalleOrden::find($detalleOrden->id));
    }

    #[Test]
    public function puede_eliminar_detalles_por_orden()
    {
        $orden = Orden::factory()->create();
        DetalleOrden::factory()->count(3)->create(['orden_id' => $orden->id]);

        $resultado = $this->repository->eliminarPorOrden($orden->id);

        $this->assertTrue($resultado);
        $this->assertCount(0, DetalleOrden::where('orden_id', $orden->id)->get());
    }

    #[Test]
    public function puede_encontrar_detalle_orden_por_id()
    {
        $detalleOrden = DetalleOrden::factory()->create();

        $resultado = $this->repository->encontrar($detalleOrden->id);

        $this->assertNotNull($resultado);
        $this->assertEquals($detalleOrden->id, $resultado->id);
    }

    #[Test]
    public function puede_encontrar_detalle_orden_con_relaciones()
    {
        $detalleOrden = DetalleOrden::factory()->create();

        $resultado = $this->repository->encontrarConRelaciones($detalleOrden->id);

        $this->assertNotNull($resultado);
        $this->assertTrue($resultado->relationLoaded('orden'));
        $this->assertTrue($resultado->relationLoaded('producto'));
        $this->assertTrue($resultado->relationLoaded('estadoOrden'));
    }
}

