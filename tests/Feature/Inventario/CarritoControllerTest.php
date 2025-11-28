<?php

namespace Tests\Feature\Inventario;

use Tests\TestCase;
use App\Models\User;
use App\Models\Inventario\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;

class CarritoControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar migraciones y seeders de todos los módulos
        $this->migrateDatabases();
        
        // Asegurar que los seeders se ejecuten después de RefreshDatabase
        if (!\App\Models\Tema::where('name', 'CATEGORIAS')->exists()) {
            $this->artisan('db:seed', ['--force' => true]);
        }

        // Crear permisos necesarios
        Permission::firstOrCreate(['name' => 'VER CARRITO']);
        Permission::firstOrCreate(['name' => 'AGREGAR CARRITO']);
        Permission::firstOrCreate(['name' => 'ACTUALIZAR CARRITO']);
        Permission::firstOrCreate(['name' => 'ELIMINAR CARRITO']);
        Permission::firstOrCreate(['name' => 'VACIAR CARRITO']);

        // Crear usuario con permisos
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER CARRITO');
    }

    #[Test]
    public function puede_ver_vista_del_carrito()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('inventario.carrito.ecommerce'));

        $response->assertStatus(200);
        $response->assertViewIs('inventario.carrito.carrito');
    }

    #[Test]
    public function no_puede_ver_carrito_sin_permiso()
    {
        $userSinPermiso = User::factory()->create();
        $this->actingAs($userSinPermiso);

        $response = $this->get(route('inventario.carrito.ecommerce'));

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_agregar_productos_al_carrito()
    {
        $this->user->givePermissionTo('AGREGAR CARRITO');
        $this->actingAs($this->user);

        $producto1 = Producto::factory()->create(['cantidad' => 10]);
        $producto2 = Producto::factory()->create(['cantidad' => 5]);

        $response = $this->postJson(route('inventario.carrito.agregar'), [
            'items' => [
                [
                    'producto_id' => $producto1->id,
                    'cantidad' => 2,
                ],
                [
                    'producto_id' => $producto2->id,
                    'cantidad' => 1,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Solicitud procesada correctamente',
        ]);
    }

    #[Test]
    public function no_puede_agregar_productos_sin_stock_suficiente()
    {
        $this->user->givePermissionTo('AGREGAR CARRITO');
        $this->actingAs($this->user);

        $producto = Producto::factory()->create(['cantidad' => 2]);

        $response = $this->postJson(route('inventario.carrito.agregar'), [
            'items' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 5, // Más de lo disponible
                ],
            ],
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Stock insuficiente para algunos productos',
        ]);
        $response->assertJsonHas('errores');
    }

    #[Test]
    public function no_puede_agregar_productos_sin_permiso()
    {
        $this->actingAs($this->user);

        $producto = Producto::factory()->create(['cantidad' => 10]);

        $response = $this->postJson(route('inventario.carrito.agregar'), [
            'items' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 2,
                ],
            ],
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_actualizar_cantidad_en_carrito()
    {
        $this->user->givePermissionTo('ACTUALIZAR CARRITO');
        $this->actingAs($this->user);

        $producto = Producto::factory()->create(['cantidad' => 10]);

        $response = $this->putJson(route('inventario.carrito.actualizar', $producto->id), [
            'cantidad' => 3,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
        ]);
    }

    #[Test]
    public function no_puede_actualizar_cantidad_sin_permiso()
    {
        $this->actingAs($this->user);

        $producto = Producto::factory()->create(['cantidad' => 10]);

        $response = $this->putJson(route('inventario.carrito.actualizar', $producto->id), [
            'cantidad' => 3,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_eliminar_producto_del_carrito()
    {
        $this->user->givePermissionTo('ELIMINAR CARRITO');
        $this->actingAs($this->user);

        $producto = Producto::factory()->create();

        $response = $this->deleteJson(route('inventario.carrito.eliminar', $producto->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Producto eliminado del carrito',
        ]);
    }

    #[Test]
    public function retorna_error_al_eliminar_producto_inexistente()
    {
        $this->user->givePermissionTo('ELIMINAR CARRITO');
        $this->actingAs($this->user);

        $response = $this->deleteJson(route('inventario.carrito.eliminar', 99999));

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Producto no encontrado',
        ]);
    }

    #[Test]
    public function no_puede_eliminar_producto_sin_permiso()
    {
        $this->actingAs($this->user);

        $producto = Producto::factory()->create();

        $response = $this->deleteJson(route('inventario.carrito.eliminar', $producto->id));

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_vaciar_carrito()
    {
        $this->user->givePermissionTo('VACIAR CARRITO');
        $this->actingAs($this->user);

        $response = $this->postJson(route('inventario.carrito.vaciar'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Carrito vaciado correctamente',
        ]);
    }

    #[Test]
    public function no_puede_vaciar_carrito_sin_permiso()
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('inventario.carrito.vaciar'));

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_obtener_contenido_del_carrito()
    {
        $this->actingAs($this->user);

        $producto1 = Producto::factory()->create();
        $producto2 = Producto::factory()->create();

        $response = $this->getJson(route('inventario.carrito.contenido'), [
            'items' => [
                ['producto_id' => $producto1->id, 'cantidad' => 2],
                ['producto_id' => $producto2->id, 'cantidad' => 1],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'productos' => [
                '*' => ['id', 'producto', 'cantidad', 'codigo_barras'],
            ],
        ]);
    }

    #[Test]
    public function puede_obtener_contenido_con_carrito_vacio()
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('inventario.carrito.contenido'), [
            'items' => [],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'productos' => [],
        ]);
    }
}

