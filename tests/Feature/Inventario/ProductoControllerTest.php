<?php

namespace Tests\Feature\Inventario;

use App\Models\Inventario\Producto;
use App\Models\ParametroTema;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductoControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Seeders base para datos realistas
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\TemaSeeder::class,
        ]);

        // Crear permisos necesarios
        Permission::firstOrCreate(['name' => 'VER PRODUCTO']);
        Permission::firstOrCreate(['name' => 'CREAR PRODUCTO']);
        Permission::firstOrCreate(['name' => 'EDITAR PRODUCTO']);
        Permission::firstOrCreate(['name' => 'ELIMINAR PRODUCTO']);
        Permission::firstOrCreate(['name' => 'BUSCAR PRODUCTO']);
        Permission::firstOrCreate(['name' => 'VER CATALOGO PRODUCTO']);

        // Crear usuario con permisos usando factory
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER PRODUCTO');
    }

    #[Test]
    public function puede_ver_listado_de_productos(): void
    {
        $this->actingAs($this->user);

        Producto::factory()->count(5)->create();

        $response = $this->get(route('inventario.productos.index'));

        $response->assertStatus(200);
        $response->assertViewIs('inventario.productos.index');
        $response->assertViewHas('productos');
    }

    #[Test]
    public function puede_buscar_productos(): void
    {
        $this->user->givePermissionTo('BUSCAR PRODUCTO');
        $this->actingAs($this->user);

        $producto = Producto::factory()->create(['producto' => 'Producto Test']);

        $response = $this->get(route('inventario.productos.buscar', ['search' => 'Test']));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_ver_formulario_de_creacion(): void
    {
        $this->user->givePermissionTo('CREAR PRODUCTO');
        $this->actingAs($this->user);

        $response = $this->get(route('inventario.productos.create'));

        $response->assertStatus(200);
        $response->assertViewIs('inventario.productos.create');
    }

    #[Test]
    public function puede_crear_producto(): void
    {
        $this->user->givePermissionTo('CREAR PRODUCTO');
        $this->actingAs($this->user);

        // Obtener parámetros necesarios
        $tipoProducto = ParametroTema::whereHas('tema', function ($q) {
            $q->where('name', 'TIPOS DE PRODUCTO');
        })->first();

        $unidadMedida = ParametroTema::whereHas('tema', function ($q) {
            $q->where('name', 'UNIDADES DE MEDIDA');
        })->first();

        $estado = ParametroTema::whereHas('tema', function ($q) {
            $q->where('name', 'ESTADOS DE PRODUCTO');
        })->first();

        if (! $tipoProducto || ! $unidadMedida || ! $estado) {
            $this->markTestSkipped('Faltan parámetros necesarios (requiere seeders completos)');
        }

        $response = $this->post(route('inventario.productos.store'), [
            'producto' => 'Producto de Prueba '.$this->faker->word(),
            'tipo_producto_id' => $tipoProducto->id,
            'descripcion' => $this->faker->sentence(),
            'peso' => $this->faker->randomFloat(2, 0.1, 100),
            'unidad_medida_id' => $unidadMedida->id,
            'cantidad' => $this->faker->numberBetween(1, 100),
            'estado_producto_id' => $estado->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    #[Test]
    public function no_puede_crear_producto_sin_permiso(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('inventario.productos.store'), []);

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_ver_detalles_de_producto(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::factory()->create();

        $response = $this->get(route('inventario.productos.show', $producto->id));

        $response->assertStatus(200);
        $response->assertViewHas('producto');
    }

    #[Test]
    public function puede_ver_formulario_de_edicion(): void
    {
        $this->user->givePermissionTo('EDITAR PRODUCTO');
        $this->actingAs($this->user);

        $producto = Producto::factory()->create();

        $response = $this->get(route('inventario.productos.edit', $producto->id));

        $response->assertStatus(200);
        $response->assertViewHas('producto');
    }

    #[Test]
    public function puede_actualizar_producto(): void
    {
        $this->user->givePermissionTo('EDITAR PRODUCTO');
        $this->actingAs($this->user);

        $producto = Producto::factory()->create();

        $response = $this->put(route('inventario.productos.update', $producto->id), [
            'producto' => 'Producto Actualizado',
            'tipo_producto_id' => $producto->tipo_producto_id,
            'descripcion' => $producto->descripcion,
            'peso' => $producto->peso,
            'unidad_medida_id' => $producto->unidad_medida_id,
            'cantidad' => $producto->cantidad,
            'estado_producto_id' => $producto->estado_producto_id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'producto' => 'Producto Actualizado',
        ]);
    }

    #[Test]
    public function puede_eliminar_producto(): void
    {
        $this->user->givePermissionTo('ELIMINAR PRODUCTO');
        $this->actingAs($this->user);

        $producto = Producto::factory()->create();

        $response = $this->delete(route('inventario.productos.destroy', $producto->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('productos', [
            'id' => $producto->id,
        ]);
    }

    #[Test]
    public function requiere_autenticacion_para_ver_productos(): void
    {
        $response = $this->get(route('inventario.productos.index'));

        $response->assertRedirect(route('login'));
    }
}
