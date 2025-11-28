<?php

namespace Tests\Unit\Inventario;

use App\Models\Inventario\Orden;
use App\Models\Inventario\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrdenModelTest extends TestCase
{
    use RefreshDatabase;

    protected Orden $orden;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\TemaSeeder::class,
        ]);

        $this->orden = Orden::factory()->create();
    }

    #[Test]
    public function puede_crear_orden(): void
    {
        $this->assertInstanceOf(Orden::class, $this->orden);
        $this->assertDatabaseHas('ordenes', [
            'id' => $this->orden->id,
        ]);
    }

    #[Test]
    public function tiene_relacion_con_detalles(): void
    {
        $producto = Producto::factory()->create();
        \App\Models\Inventario\DetalleOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'producto_id' => $producto->id,
        ]);

        $this->assertTrue($this->orden->detalles()->exists());
    }
}
