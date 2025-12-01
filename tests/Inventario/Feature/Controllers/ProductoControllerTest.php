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
        
        // Ejecutar solo los seeders necesarios para productos
        // RefreshDatabase ya ejecuta las migraciones automáticamente
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

        // Obtener o crear categoría
        $categoria = \App\Models\Parametro::whereHas('temas', function ($q) {
            $q->where('name', 'CATEGORIAS');
        })->first();
        
        if (!$categoria) {
            // TemaSeeder ya crea CATEGORÍAS, solo buscar el tema existente
            $temaCategorias = \App\Models\Tema::where('name', 'CATEGORÍAS')->first();
            if (!$temaCategorias) {
                $temaCategorias = \App\Models\Tema::create([
                    'name' => 'CATEGORÍAS',
                    'status' => true,
                    'user_create_id' => null,
                    'user_edit_id' => null,
                ]);
            }
            $categoria = \App\Models\Parametro::factory()->create([
                'name' => 'CATEGORIA TEST',
                'status' => true,
                'user_create_id' => null,
                'user_edit_id' => null,
            ]);
            \App\Models\ParametroTema::create([
                'parametro_id' => $categoria->id,
                'tema_id' => $temaCategorias->id,
                'status' => true,
                'user_create_id' => null,
                'user_edit_id' => null,
            ]);
        }

        // Obtener o crear marca
        $marca = \App\Models\Parametro::whereHas('temas', function ($q) {
            $q->where('name', 'MARCAS');
        })->first();
        
        if (!$marca) {
            // TemaSeeder ya crea MARCAS, solo buscar el tema existente
            $temaMarcas = \App\Models\Tema::where('name', 'MARCAS')->first();
            if (!$temaMarcas) {
                $temaMarcas = \App\Models\Tema::create([
                    'name' => 'MARCAS',
                    'status' => true,
                    'user_create_id' => null,
                    'user_edit_id' => null,
                ]);
            }
            $marca = \App\Models\Parametro::factory()->create([
                'name' => 'MARCA TEST',
                'status' => true,
                'user_create_id' => null,
                'user_edit_id' => null,
            ]);
            \App\Models\ParametroTema::create([
                'parametro_id' => $marca->id,
                'tema_id' => $temaMarcas->id,
                'status' => true,
                'user_create_id' => null,
                'user_edit_id' => null,
            ]);
        }

        // Crear contrato convenio
        $contratoConvenio = \App\Models\Inventario\ContratoConvenio::factory()->create();

        // Obtener o crear ambiente (usar existente si hay)
        $ambiente = \App\Models\Ambiente::inRandomOrder()->first();
        if (!$ambiente) {
            // Crear ambiente con dependencias (Sede -> Bloque -> Piso -> Ambiente)
            $sede = \App\Models\Sede::inRandomOrder()->first();
            if (!$sede) {
                $sede = \App\Models\Sede::factory()->create();
            }
            $bloque = \App\Models\Bloque::factory()->create(['sede_id' => $sede->id]);
            $piso = \App\Models\Piso::factory()->create(['bloque_id' => $bloque->id]);
            $ambiente = \App\Models\Ambiente::factory()->create(['piso_id' => $piso->id]);
        }

        // Crear proveedor
        $proveedor = \App\Models\Inventario\Proveedor::factory()->create();

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
            'categoria_id' => $categoria->id,
            'marca_id' => $marca->id,
            'contrato_convenio_id' => $contratoConvenio->id,
            'ambiente_id' => $ambiente->id,
            'proveedor_id' => $proveedor->id,
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
            'categoria_id' => $producto->categoria_id,
            'marca_id' => $producto->marca_id,
            'contrato_convenio_id' => $producto->contrato_convenio_id,
            'ambiente_id' => $producto->ambiente_id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'producto' => strtoupper(self::PRODUCTO_ACTUALIZADO),
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
