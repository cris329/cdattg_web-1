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
    private const PRODUCTO_ACTUALIZADO = 'PRODUCTO ACTUALIZADO';

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
        $this->producto->update(['name' => 'Producto Actualizado']);

        // El modelo convierte automáticamente a mayúsculas
        $this->assertEquals(self::PRODUCTO_ACTUALIZADO, $this->producto->name);
        $this->assertDatabaseHas('productos', [
            'id' => $this->producto->id,
            'name' => self::PRODUCTO_ACTUALIZADO,
        ]);
    }
}
