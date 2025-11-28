<?php

namespace Tests\Unit\Inventario;

use App\Models\Inventario\Aprobacion;
use App\Models\Inventario\Devolucion;
use App\Models\Inventario\DetalleOrden;
use App\Models\Inventario\Orden;
use App\Models\Inventario\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DetalleOrdenModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);
    }

    #[Test]
    public function tiene_relacion_con_orden(): void
    {
        $orden = Orden::factory()->create();
        $detalle = DetalleOrden::factory()->create(['orden_id' => $orden->id]);

        $this->assertInstanceOf(Orden::class, $detalle->orden);
        $this->assertEquals($orden->id, $detalle->orden->id);
    }

    #[Test]
    public function tiene_relacion_con_producto(): void
    {
        $producto = Producto::factory()->create();
        $detalle = DetalleOrden::factory()->create(['producto_id' => $producto->id]);

        $this->assertInstanceOf(Producto::class, $detalle->producto);
        $this->assertEquals($producto->id, $detalle->producto->id);
    }

    #[Test]
    public function tiene_relacion_con_devoluciones(): void
    {
        $detalle = DetalleOrden::factory()->create();
        Devolucion::factory()->count(2)->create(['detalle_orden_id' => $detalle->id]);

        $this->assertCount(2, $detalle->devoluciones);
    }

    #[Test]
    public function calcula_cantidad_devuelta(): void
    {
        $detalle = DetalleOrden::factory()->create(['cantidad' => 10]);
        Devolucion::factory()->create([
            'detalle_orden_id' => $detalle->id,
            'cantidad_devuelta' => 3,
        ]);
        Devolucion::factory()->create([
            'detalle_orden_id' => $detalle->id,
            'cantidad_devuelta' => 2,
        ]);

        $this->assertEquals(5, $detalle->getCantidadDevuelta());
    }

    #[Test]
    public function calcula_cantidad_pendiente(): void
    {
        $detalle = DetalleOrden::factory()->create(['cantidad' => 10]);
        Devolucion::factory()->create([
            'detalle_orden_id' => $detalle->id,
            'cantidad_devuelta' => 3,
        ]);

        $this->assertEquals(7, $detalle->getCantidadPendiente());
    }

    #[Test]
    public function verifica_si_esta_completamente_devuelto(): void
    {
        $detalle = DetalleOrden::factory()->create(['cantidad' => 10]);
        Devolucion::factory()->create([
            'detalle_orden_id' => $detalle->id,
            'cantidad_devuelta' => 10,
        ]);

        $this->assertTrue($detalle->estaCompletamenteDevuelto());
    }

    #[Test]
    public function verifica_si_tiene_cierre_sin_stock(): void
    {
        $detalle = DetalleOrden::factory()->create();
        Devolucion::factory()->create([
            'detalle_orden_id' => $detalle->id,
            'cierra_sin_stock' => true,
        ]);

        $this->assertTrue($detalle->tieneCierreSinStock());
    }
}

