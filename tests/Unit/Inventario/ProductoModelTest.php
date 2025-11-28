<?php

namespace Tests\Unit\Inventario;

use App\Models\Inventario\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductoModelTest extends TestCase
{
    use RefreshDatabase;

    protected Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\TemaSeeder::class,
        ]);

        $this->producto = Producto::factory()->create();
    }

    #[Test]
    public function puede_crear_producto(): void
    {
        $this->assertInstanceOf(Producto::class, $this->producto);
        $this->assertDatabaseHas('productos', [
            'id' => $this->producto->id,
        ]);
    }

    #[Test]
    public function puede_actualizar_producto(): void
    {
        $this->producto->update(['producto' => 'Producto Actualizado']);

        $this->assertEquals('Producto Actualizado', $this->producto->producto);
        $this->assertDatabaseHas('productos', [
            'id' => $this->producto->id,
            'producto' => 'Producto Actualizado',
        ]);
    }
}
