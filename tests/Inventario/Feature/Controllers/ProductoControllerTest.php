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

    // Constantes para permisos
    private const PERMISSION_VER_PRODUCTO = 'VER PRODUCTO';
    private const PERMISSION_CREAR_PRODUCTO = 'CREAR PRODUCTO';
    private const PERMISSION_EDITAR_PRODUCTO = 'EDITAR PRODUCTO';
    private const PERMISSION_ELIMINAR_PRODUCTO = 'ELIMINAR PRODUCTO';
    private const PERMISSION_BUSCAR_PRODUCTO = 'BUSCAR PRODUCTO';
    private const PERMISSION_VER_CATALOGO_PRODUCTO = 'VER CATALOGO PRODUCTO';

    // Constantes para rutas
    private const ROUTE_INDEX = 'inventario.productos.index';
    private const ROUTE_BUSCAR = 'inventario.productos.buscar';
    private const ROUTE_CREATE = 'inventario.productos.create';
    private const ROUTE_STORE = 'inventario.productos.store';
    private const ROUTE_SHOW = 'inventario.productos.show';
    private const ROUTE_EDIT = 'inventario.productos.edit';
    private const ROUTE_UPDATE = 'inventario.productos.update';
    private const ROUTE_DESTROY = 'inventario.productos.destroy';

    // Constantes para vistas
    private const VIEW_INDEX = 'inventario.productos.index';
    private const VIEW_CREATE = 'inventario.productos.create';

    // Constantes para datos
    private const PRODUCTO_ACTUALIZADO = 'Producto Actualizado';
    private const ROUTE_LOGIN = 'verificarLogin';

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
        Permission::firstOrCreate(['name' => self::PERMISSION_VER_PRODUCTO]);
        Permission::firstOrCreate(['name' => self::PERMISSION_CREAR_PRODUCTO]);
        Permission::firstOrCreate(['name' => self::PERMISSION_EDITAR_PRODUCTO]);
        Permission::firstOrCreate(['name' => self::PERMISSION_ELIMINAR_PRODUCTO]);
        Permission::firstOrCreate(['name' => self::PERMISSION_BUSCAR_PRODUCTO]);
        Permission::firstOrCreate(['name' => self::PERMISSION_VER_CATALOGO_PRODUCTO]);

        // Crear usuario con permisos usando factory
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(self::PERMISSION_VER_PRODUCTO);
    }

    #[Test]
    public function puede_ver_listado_de_productos(): void
    {
        $this->actingAs($this->user);

        Producto::factory()->count(5)->create();

        $response = $this->get(route(self::ROUTE_INDEX));

        $response->assertStatus(200);
        $response->assertViewIs(self::VIEW_INDEX);
        $response->assertViewHas('productos');
    }

    #[Test]
    public function puede_buscar_productos(): void
    {
        $this->user->givePermissionTo(self::PERMISSION_BUSCAR_PRODUCTO);
        $this->actingAs($this->user);

        Producto::factory()->create(['producto' => 'Producto Test']);

        $response = $this->get(route(self::ROUTE_BUSCAR, ['search' => 'Test']));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_ver_formulario_de_creacion(): void
    {
        $this->user->givePermissionTo(self::PERMISSION_CREAR_PRODUCTO);
        $this->actingAs($this->user);

        $response = $this->get(route(self::ROUTE_CREATE));

        $response->assertStatus(200);
        $response->assertViewIs(self::VIEW_CREATE);
    }

    #[Test]
    public function puede_crear_producto(): void
    {
        $this->user->givePermissionTo(self::PERMISSION_CREAR_PRODUCTO);
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
            $this->markTestSkipped('Faltan parámetros necesarios');
        }

        $response = $this->post(route(self::ROUTE_STORE), [
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

        $response = $this->post(route(self::ROUTE_STORE), []);

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_ver_detalles_de_producto(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::factory()->create();

        $response = $this->get(route(self::ROUTE_SHOW, $producto->id));

        $response->assertStatus(200);
        $response->assertViewHas('producto');
    }

    #[Test]
    public function puede_ver_formulario_de_edicion(): void
    {
        $this->user->givePermissionTo(self::PERMISSION_EDITAR_PRODUCTO);
        $this->actingAs($this->user);

        $producto = Producto::factory()->create();

        $response = $this->get(route(self::ROUTE_EDIT, $producto->id));

        $response->assertStatus(200);
        $response->assertViewHas('producto');
    }

    #[Test]
    public function puede_actualizar_producto(): void
    {
        $this->user->givePermissionTo(self::PERMISSION_EDITAR_PRODUCTO);
        $this->actingAs($this->user);

        $producto = Producto::factory()->create();

        $response = $this->put(route(self::ROUTE_UPDATE, $producto->id), [
            'producto' => self::PRODUCTO_ACTUALIZADO,
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
            'producto' => self::PRODUCTO_ACTUALIZADO,
        ]);
    }

    #[Test]
    public function puede_eliminar_producto(): void
    {
        $this->user->givePermissionTo(self::PERMISSION_ELIMINAR_PRODUCTO);
        $this->actingAs($this->user);

        $producto = Producto::factory()->create();

        $response = $this->delete(route(self::ROUTE_DESTROY, $producto->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('productos', [
            'id' => $producto->id,
        ]);
    }

    #[Test]
    public function requiere_autenticacion_para_ver_productos(): void
    {
        $response = $this->get(route(self::ROUTE_INDEX));

        $response->assertRedirect(route(self::ROUTE_LOGIN));
    }
}
