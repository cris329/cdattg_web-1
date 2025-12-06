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

    // Constantes para permisos
    private const PERMISSION_VER_CARRITO = 'VER CARRITO';
    private const PERMISSION_AGREGAR_CARRITO = 'AGREGAR CARRITO';
    private const PERMISSION_ACTUALIZAR_CARRITO = 'ACTUALIZAR CARRITO';
    private const PERMISSION_ELIMINAR_CARRITO = 'ELIMINAR CARRITO';
    private const PERMISSION_VACIAR_CARRITO = 'VACIAR CARRITO';

    // Constantes para rutas
    private const ROUTE_ECOMMERCE = 'inventario.carrito.ecommerce';
    private const ROUTE_AGREGAR = 'inventario.carrito.agregar';
    private const ROUTE_ACTUALIZAR = 'inventario.carrito.actualizar';
    private const ROUTE_ELIMINAR = 'inventario.carrito.eliminar';
    private const ROUTE_VACIAR = 'inventario.carrito.vaciar';
    private const ROUTE_CONTENIDO = 'inventario.carrito.contenido';

    // Constantes para vistas
    private const VIEW_CARRITO = 'inventario.carrito.carrito';


    protected function setUp(): void
    {
        parent::setUp();
        $this->desactivarCSRF();
        $this->ejecutarSeedersNecesarios();
        $this->crearPermisos();
        $this->crearUsuarioConPermisos();
    }

    private function desactivarCSRF(): void
    {
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);
    }

    private function ejecutarSeedersNecesarios(): void
    {
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

    private function crearPermisos(): void
    {
        Permission::firstOrCreate(['name' => self::PERMISSION_VER_CARRITO]);
        Permission::firstOrCreate(['name' => self::PERMISSION_AGREGAR_CARRITO]);
        Permission::firstOrCreate(['name' => self::PERMISSION_ACTUALIZAR_CARRITO]);
        Permission::firstOrCreate(['name' => self::PERMISSION_ELIMINAR_CARRITO]);
        Permission::firstOrCreate(['name' => self::PERMISSION_VACIAR_CARRITO]);
    }

    private function crearUsuarioConPermisos(): void
    {
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(self::PERMISSION_VER_CARRITO);
    }

    #[Test]
    public function puede_ver_vista_del_carrito()
    {
        $this->actingAs($this->user);

        $response = $this->get(route(self::ROUTE_ECOMMERCE));

        $response->assertStatus(200);
        $response->assertViewIs(self::VIEW_CARRITO);
    }

    #[Test]
    public function no_puede_ver_carrito_sin_permiso()
    {
        /** @var User $userSinPermiso */
        $userSinPermiso = User::factory()->create();
        $this->actingAs($userSinPermiso);

        $response = $this->get(route(self::ROUTE_ECOMMERCE));

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_agregar_productos_al_carrito()
    {
        $this->user->givePermissionTo(self::PERMISSION_AGREGAR_CARRITO);
        $this->actingAs($this->user);

        $producto1 = Producto::factory()->create(['cantidad' => 10]);
        $producto2 = Producto::factory()->create(['cantidad' => 5]);

        $response = $this->postJson(route(self::ROUTE_AGREGAR), [
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
        $this->user->givePermissionTo(self::PERMISSION_AGREGAR_CARRITO);
        $this->actingAs($this->user);

        $producto = Producto::factory()->create(['cantidad' => 2]);

        $response = $this->postJson(route(self::ROUTE_AGREGAR), [
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
        $response->assertJsonStructure([
            'success',
            'message',
            'errores',
        ]);
    }

    #[Test]
    public function no_puede_agregar_productos_sin_permiso()
    {
        $this->actingAs($this->user);

        $producto = Producto::factory()->create(['cantidad' => 10]);

        $response = $this->postJson(route(self::ROUTE_AGREGAR), [
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
        $this->user->givePermissionTo(self::PERMISSION_ACTUALIZAR_CARRITO);
        $this->actingAs($this->user);

        $producto = Producto::factory()->create(['cantidad' => 10]);

        $response = $this->putJson(route(self::ROUTE_ACTUALIZAR, $producto->id), [
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

        $response = $this->putJson(route(self::ROUTE_ACTUALIZAR, $producto->id), [
            'cantidad' => 3,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_eliminar_producto_del_carrito()
    {
        $this->user->givePermissionTo(self::PERMISSION_ELIMINAR_CARRITO);
        $this->actingAs($this->user);

        $producto = Producto::factory()->create();

        $response = $this->deleteJson(route(self::ROUTE_ELIMINAR, $producto->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Producto eliminado del carrito',
        ]);
    }

    #[Test]
    public function retorna_error_al_eliminar_producto_inexistente()
    {
        $this->user->givePermissionTo(self::PERMISSION_ELIMINAR_CARRITO);
        $this->actingAs($this->user);

        $response = $this->deleteJson(route(self::ROUTE_ELIMINAR, 99999));

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

        $response = $this->deleteJson(route(self::ROUTE_ELIMINAR, $producto->id));

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_vaciar_carrito()
    {
        $this->user->givePermissionTo(self::PERMISSION_VACIAR_CARRITO);
        $this->actingAs($this->user);

        $response = $this->postJson(route(self::ROUTE_VACIAR));

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

        $response = $this->postJson(route(self::ROUTE_VACIAR));

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_obtener_contenido_del_carrito()
    {
        $this->actingAs($this->user);

        $producto1 = Producto::factory()->create();
        $producto2 = Producto::factory()->create();

        $response = $this->getJson(route(self::ROUTE_CONTENIDO), [
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
                '*' => ['id', 'name', 'cantidad', 'codigo_barras'],
            ],
        ]);
    }

    #[Test]
    public function puede_obtener_contenido_con_carrito_vacio()
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route(self::ROUTE_CONTENIDO), [
            'items' => [],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'productos' => [],
        ]);
    }
}

