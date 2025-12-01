<?php

namespace Tests\Feature\Inventario;

use Tests\TestCase;
use App\Models\User;
use App\Models\Inventario\Proveedor;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Pais;
use App\Models\ParametroTema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;

class ProveedorControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // Constantes para permisos
    private const PERMISSION_VER_PROVEEDOR = 'VER PROVEEDOR';
    private const PERMISSION_CREAR_PROVEEDOR = 'CREAR PROVEEDOR';
    private const PERMISSION_EDITAR_PROVEEDOR = 'EDITAR PROVEEDOR';
    private const PERMISSION_ELIMINAR_PROVEEDOR = 'ELIMINAR PROVEEDOR';

    // Constantes para rutas
    private const ROUTE_INDEX = 'inventario.proveedores.index';
    private const ROUTE_CREATE = 'inventario.proveedores.create';
    private const ROUTE_STORE = 'inventario.proveedores.store';
    private const ROUTE_SHOW = 'inventario.proveedores.show';
    private const ROUTE_EDIT = 'inventario.proveedores.edit';
    private const ROUTE_UPDATE = 'inventario.proveedores.update';
    private const ROUTE_DESTROY = 'inventario.proveedores.destroy';
    private const ROUTE_MUNICIPIOS = 'inventario.proveedores.municipios';

    // Constantes para vistas
    private const VIEW_INDEX = 'inventario.proveedores.index';
    private const VIEW_CREATE = 'inventario.proveedores.create';
    private const VIEW_SHOW = 'inventario.proveedores.show';
    private const VIEW_EDIT = 'inventario.proveedores.edit';

    // Constantes para datos
    private const PROVEEDOR_ACTUALIZADO = 'PROVEEDOR ACTUALIZADO';
    private const NIT_EJEMPLO = '900123456-7';

    protected User $user;
    protected Departamento $departamento;
    protected Municipio $municipio;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar solo los seeders necesarios para proveedores
        // RefreshDatabase ya ejecuta las migraciones automáticamente
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\TemaSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
        ]);

        // Crear país si no existe
        $pais = Pais::firstOrCreate(
            ['pais' => 'COLOMBIA'],
            ['status' => true]
        );

        // Crear departamento y municipio para los tests
        $this->departamento = Departamento::firstOrCreate(
            ['departamento' => 'ANTIOQUIA'],
            [
                'pais_id' => $pais->id,
                'status' => true,
            ]
        );

        $this->municipio = Municipio::firstOrCreate(
            [
                'municipio' => 'MEDELLIN',
                'departamento_id' => $this->departamento->id,
            ],
            ['status' => true]
        );

        // Obtener tema ESTADOS (TemaSeeder ya lo crea)
        $temaEstados = \App\Models\Tema::where('name', 'ESTADOS')->first();
        
        if (!$temaEstados) {
            $temaEstados = \App\Models\Tema::create([
                'name' => 'ESTADOS',
                'status' => true,
                'user_create_id' => null,
                'user_edit_id' => null,
            ]);
        }

        $estado = \App\Models\Parametro::firstOrCreate(
            ['name' => 'ACTIVO'],
            [
                'status' => true,
                'user_create_id' => null,
                'user_edit_id' => null,
            ]
        );

        ParametroTema::firstOrCreate(
            [
                'parametro_id' => $estado->id,
                'tema_id' => $temaEstados->id,
            ],
            [
                'status' => true,
                'user_create_id' => null,
                'user_edit_id' => null,
            ]
        );

        // Crear permisos necesarios
        Permission::firstOrCreate(['name' => self::PERMISSION_VER_PROVEEDOR]);
        Permission::firstOrCreate(['name' => self::PERMISSION_CREAR_PROVEEDOR]);
        Permission::firstOrCreate(['name' => self::PERMISSION_EDITAR_PROVEEDOR]);
        Permission::firstOrCreate(['name' => self::PERMISSION_ELIMINAR_PROVEEDOR]);

        // Crear usuario con permisos
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(self::PERMISSION_VER_PROVEEDOR);
    }

    #[Test]
    public function puede_ver_listado_de_proveedores()
    {
        $this->actingAs($this->user);

        // Crear algunos proveedores
        Proveedor::factory()->count(3)->create([
            'departamento_id' => $this->departamento->id,
            'municipio_id' => $this->municipio->id,
        ]);

        $response = $this->get(route(self::ROUTE_INDEX));

        $response->assertStatus(200);
        $response->assertViewIs(self::VIEW_INDEX);
        $response->assertViewHas('proveedores');
    }

    #[Test]
    public function puede_buscar_proveedores_por_nombre()
    {
        $this->actingAs($this->user);

        Proveedor::factory()->create([
            'proveedor' => 'TECNOLOGIA SISTEMAS LTDA',
            'departamento_id' => $this->departamento->id,
            'municipio_id' => $this->municipio->id,
        ]);

        $response = $this->get(route(self::ROUTE_INDEX, ['search' => 'TECNOLOGIA']));

        $response->assertStatus(200);
        $response->assertSee('TECNOLOGIA', false);
    }

    #[Test]
    public function puede_ver_formulario_de_creacion()
    {
        $this->user->givePermissionTo(self::PERMISSION_CREAR_PROVEEDOR);
        $this->actingAs($this->user);

        $response = $this->get(route(self::ROUTE_CREATE));

        $response->assertStatus(200);
        $response->assertViewIs(self::VIEW_CREATE);
        $response->assertViewHas('departamentos');
        $response->assertViewHas('municipios');
    }

    #[Test]
    public function puede_crear_proveedor()
    {
        $this->user->givePermissionTo(self::PERMISSION_CREAR_PROVEEDOR);
        $this->actingAs($this->user);

        $estadoId = ParametroTema::whereHas('tema', function($q) {
            $q->where('name', 'ESTADOS');
        })->first()->id ?? 1;

        $response = $this->post(route(self::ROUTE_STORE), [
            'proveedor' => 'NUEVO PROVEEDOR LTDA',
            'nit' => self::NIT_EJEMPLO,
            'email' => 'contacto@proveedor.com',
            'telefono' => '6012345678',
            'direccion' => 'Calle 123 #45-67',
            'departamento_id' => $this->departamento->id,
            'municipio_id' => $this->municipio->id,
            'contacto' => 'JUAN PEREZ',
            'estado_id' => $estadoId,
        ]);

        $response->assertRedirect(route(self::ROUTE_INDEX));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('proveedores', [
            'proveedor' => 'NUEVO PROVEEDOR LTDA',
            'nit' => self::NIT_EJEMPLO,
        ]);
    }

    #[Test]
    public function no_puede_crear_proveedor_sin_permiso()
    {
        $this->actingAs($this->user);

        $response = $this->post(route(self::ROUTE_STORE), [
            'proveedor' => 'PROVEEDOR SIN PERMISO',
            'nit' => self::NIT_EJEMPLO,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_ver_detalles_de_proveedor()
    {
        $this->actingAs($this->user);

        $proveedor = Proveedor::factory()->create([
            'departamento_id' => $this->departamento->id,
            'municipio_id' => $this->municipio->id,
        ]);

        $response = $this->get(route(self::ROUTE_SHOW, $proveedor->id));

        $response->assertStatus(200);
        $response->assertViewIs(self::VIEW_SHOW);
        $response->assertViewHas('proveedor');
    }

    #[Test]
    public function puede_ver_formulario_de_edicion()
    {
        $this->user->givePermissionTo(self::PERMISSION_EDITAR_PROVEEDOR);
        $this->actingAs($this->user);

        $proveedor = Proveedor::factory()->create([
            'departamento_id' => $this->departamento->id,
            'municipio_id' => $this->municipio->id,
        ]);

        $response = $this->get(route(self::ROUTE_EDIT, $proveedor->id));

        $response->assertStatus(200);
        $response->assertViewIs(self::VIEW_EDIT);
        $response->assertViewHas('proveedor');
        $response->assertViewHas('departamentos');
        $response->assertViewHas('municipios');
    }

    #[Test]
    public function puede_actualizar_proveedor()
    {
        $this->user->givePermissionTo(self::PERMISSION_EDITAR_PROVEEDOR);
        $this->actingAs($this->user);

        $proveedor = Proveedor::factory()->create([
            'proveedor' => 'PROVEEDOR ORIGINAL',
            'departamento_id' => $this->departamento->id,
            'municipio_id' => $this->municipio->id,
        ]);

        $estadoId = ParametroTema::whereHas('tema', function($q) {
            $q->where('name', 'ESTADOS');
        })->first()->id ?? 1;

        $response = $this->put(route(self::ROUTE_UPDATE, $proveedor->id), [
            'proveedor' => self::PROVEEDOR_ACTUALIZADO,
            'nit' => $proveedor->nit,
            'email' => 'nuevo@email.com',
            'telefono' => $proveedor->telefono,
            'direccion' => $proveedor->direccion,
            'departamento_id' => $this->departamento->id,
            'municipio_id' => $this->municipio->id,
            'contacto' => $proveedor->contacto,
            'estado_id' => $estadoId,
        ]);

        $response->assertRedirect(route(self::ROUTE_INDEX));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('proveedores', [
            'id' => $proveedor->id,
            'proveedor' => self::PROVEEDOR_ACTUALIZADO,
        ]);
    }

    #[Test]
    public function no_puede_actualizar_proveedor_sin_permiso()
    {
        $this->actingAs($this->user);

        $proveedor = Proveedor::factory()->create([
            'departamento_id' => $this->departamento->id,
            'municipio_id' => $this->municipio->id,
        ]);

        $response = $this->put(route(self::ROUTE_UPDATE, $proveedor->id), [
            'proveedor' => self::PROVEEDOR_ACTUALIZADO,
            'nit' => $proveedor->nit,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_eliminar_proveedor()
    {
        $this->user->givePermissionTo(self::PERMISSION_ELIMINAR_PROVEEDOR);
        $this->actingAs($this->user);

        $proveedor = Proveedor::factory()->create([
            'departamento_id' => $this->departamento->id,
            'municipio_id' => $this->municipio->id,
        ]);

        $response = $this->delete(route(self::ROUTE_DESTROY, $proveedor->id));

        $response->assertRedirect(route(self::ROUTE_INDEX));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function no_puede_eliminar_proveedor_sin_permiso()
    {
        $this->actingAs($this->user);

        $proveedor = Proveedor::factory()->create([
            'departamento_id' => $this->departamento->id,
            'municipio_id' => $this->municipio->id,
        ]);

        $response = $this->delete(route(self::ROUTE_DESTROY, $proveedor->id));

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_obtener_municipios_por_departamento()
    {
        $this->actingAs($this->user);

        // Crear otro municipio en el mismo departamento
        Municipio::firstOrCreate(
            [
                'municipio' => 'BOGOTA',
                'departamento_id' => $this->departamento->id,
            ],
            ['status' => true]
        );

        $response = $this->getJson(
            route(self::ROUTE_MUNICIPIOS, $this->departamento->id)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'municipio']
        ]);
        
        // Verificar que retorna los municipios del departamento
        $municipios = $response->json();
        $this->assertGreaterThanOrEqual(1, count($municipios));
    }

    #[Test]
    public function retorna_array_vacio_si_departamento_no_tiene_municipios()
    {
        $this->actingAs($this->user);

        // Crear un departamento sin municipios
        $departamentoVacio = Departamento::factory()->create();

        $response = $this->getJson(
            route(self::ROUTE_MUNICIPIOS, $departamentoVacio->id)
        );

        $response->assertStatus(200);
        $response->assertJson([]);
    }
}

