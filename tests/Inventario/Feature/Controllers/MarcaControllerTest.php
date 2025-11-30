<?php

namespace Tests\Feature\Inventario;

use Tests\TestCase;
use App\Models\User;
use App\Models\Parametro;
use App\Models\Tema;
use App\Models\ParametroTema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;

class MarcaControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // Constantes para permisos
    private const PERMISSION_VER_MARCA = 'VER MARCA';
    private const PERMISSION_CREAR_MARCA = 'CREAR MARCA';
    private const PERMISSION_EDITAR_MARCA = 'EDITAR MARCA';
    private const PERMISSION_ELIMINAR_MARCA = 'ELIMINAR MARCA';

    // Constantes para rutas
    private const ROUTE_INDEX = 'inventario.marcas.index';
    private const ROUTE_CREATE = 'inventario.marcas.create';
    private const ROUTE_STORE = 'inventario.marcas.store';
    private const ROUTE_SHOW = 'inventario.marcas.show';
    private const ROUTE_EDIT = 'inventario.marcas.edit';
    private const ROUTE_UPDATE = 'inventario.marcas.update';
    private const ROUTE_DESTROY = 'inventario.marcas.destroy';

    // Constantes para vistas
    private const VIEW_INDEX = 'inventario.marcas.index';
    private const VIEW_CREATE = 'inventario.marcas.create';
    private const VIEW_SHOW = 'inventario.marcas.show';
    private const VIEW_EDIT = 'inventario.marcas.edit';

    // Constantes para datos
    private const TEMA_MARCAS = 'MARCAS';
    private const MARCA_ACTUALIZADA = 'MARCA ACTUALIZADA';
    private const MARCA_TEST = 'MARCA TEST';

    protected User $user;
    protected Tema $temaMarcas;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Desactivar CSRF para tests
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);
        
        // Ejecutar migraciones y seeders de todos los módulos
        $this->migrateDatabases();
        
        // Asegurar que los seeders se ejecuten después de RefreshDatabase
        if (!\App\Models\Tema::where('name', self::TEMA_MARCAS)->exists()) {
            $this->artisan('db:seed', ['--force' => true]);
        }

        // Crear tema MARCAS
        $this->temaMarcas = Tema::firstOrCreate(
            ['name' => self::TEMA_MARCAS],
            [
                'status' => true,
                'user_create_id' => 1,
                'user_edit_id' => 1,
            ]
        );

        // Crear permisos necesarios
        Permission::firstOrCreate(['name' => self::PERMISSION_VER_MARCA]);
        Permission::firstOrCreate(['name' => self::PERMISSION_CREAR_MARCA]);
        Permission::firstOrCreate(['name' => self::PERMISSION_EDITAR_MARCA]);
        Permission::firstOrCreate(['name' => self::PERMISSION_ELIMINAR_MARCA]);

        // Crear usuario con permisos
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(self::PERMISSION_VER_MARCA);
    }

    #[Test]
    public function puede_ver_listado_de_marcas()
    {
        $this->actingAs($this->user);

        // Crear algunas marcas
        $marca1 = Parametro::factory()->create(['name' => 'MARCA 1']);
        $marca2 = Parametro::factory()->create(['name' => 'MARCA 2']);
        
        ParametroTema::create([
            'parametro_id' => $marca1->id,
            'tema_id' => $this->temaMarcas->id,
            'status' => true,
            'user_create_id' => $this->user->id,
            'user_edit_id' => $this->user->id,
        ]);

        ParametroTema::create([
            'parametro_id' => $marca2->id,
            'tema_id' => $this->temaMarcas->id,
            'status' => true,
            'user_create_id' => $this->user->id,
            'user_edit_id' => $this->user->id,
        ]);

        $response = $this->get(route(self::ROUTE_INDEX));

        $response->assertStatus(200);
        $response->assertViewIs(self::VIEW_INDEX);
        $response->assertViewHas('marcas');
    }

    #[Test]
    public function puede_buscar_marcas_por_nombre()
    {
        $this->actingAs($this->user);

        $marca = Parametro::factory()->create(['name' => 'SAMSUNG']);
        
        ParametroTema::create([
            'parametro_id' => $marca->id,
            'tema_id' => $this->temaMarcas->id,
            'status' => true,
            'user_create_id' => $this->user->id,
            'user_edit_id' => $this->user->id,
        ]);

        $response = $this->get(route(self::ROUTE_INDEX, ['search' => 'SAMSUNG']));

        $response->assertStatus(200);
        $response->assertSee('SAMSUNG', false);
    }

    #[Test]
    public function puede_ver_formulario_de_creacion()
    {
        $this->user->givePermissionTo(self::PERMISSION_CREAR_MARCA);
        $this->actingAs($this->user);

        $response = $this->get(route(self::ROUTE_CREATE));

        $response->assertStatus(200);
        $response->assertViewIs(self::VIEW_CREATE);
    }

    #[Test]
    public function puede_crear_marca()
    {
        $this->user->givePermissionTo(self::PERMISSION_CREAR_MARCA);
        $this->actingAs($this->user);

        $response = $this->post(route(self::ROUTE_STORE), [
            'name' => 'NUEVA MARCA',
        ]);

        $response->assertRedirect(route(self::ROUTE_INDEX));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('parametros', [
            'name' => 'NUEVA MARCA',
        ]);
    }

    #[Test]
    public function no_puede_crear_marca_sin_permiso()
    {
        $this->actingAs($this->user);

        $response = $this->post(route(self::ROUTE_STORE), [
            'name' => 'MARCA SIN PERMISO',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_ver_detalles_de_marca()
    {
        $this->actingAs($this->user);

        $marca = Parametro::factory()->create(['name' => self::MARCA_TEST]);
        
        ParametroTema::create([
            'parametro_id' => $marca->id,
            'tema_id' => $this->temaMarcas->id,
            'status' => true,
            'user_create_id' => $this->user->id,
            'user_edit_id' => $this->user->id,
        ]);

        $response = $this->get(route(self::ROUTE_SHOW, $marca->id));

        $response->assertStatus(200);
        $response->assertViewIs(self::VIEW_SHOW);
        $response->assertViewHas('marca');
    }

    #[Test]
    public function puede_ver_formulario_de_edicion()
    {
        $this->user->givePermissionTo(self::PERMISSION_EDITAR_MARCA);
        $this->actingAs($this->user);

        $marca = Parametro::factory()->create(['name' => 'MARCA EDITAR']);
        
        ParametroTema::create([
            'parametro_id' => $marca->id,
            'tema_id' => $this->temaMarcas->id,
            'status' => true,
            'user_create_id' => $this->user->id,
            'user_edit_id' => $this->user->id,
        ]);

        $response = $this->get(route(self::ROUTE_EDIT, $marca->id));

        $response->assertStatus(200);
        $response->assertViewIs(self::VIEW_EDIT);
        $response->assertViewHas('marca');
    }

    #[Test]
    public function puede_actualizar_marca()
    {
        $this->user->givePermissionTo(self::PERMISSION_EDITAR_MARCA);
        $this->actingAs($this->user);

        $marca = Parametro::factory()->create(['name' => 'MARCA ORIGINAL']);
        
        ParametroTema::create([
            'parametro_id' => $marca->id,
            'tema_id' => $this->temaMarcas->id,
            'status' => true,
            'user_create_id' => $this->user->id,
            'user_edit_id' => $this->user->id,
        ]);

        $response = $this->put(route(self::ROUTE_UPDATE, $marca->id), [
            'name' => self::MARCA_ACTUALIZADA,
        ]);

        $response->assertRedirect(route(self::ROUTE_INDEX));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('parametros', [
            'id' => $marca->id,
            'name' => self::MARCA_ACTUALIZADA,
        ]);
    }

    #[Test]
    public function no_puede_actualizar_marca_sin_permiso()
    {
        $this->actingAs($this->user);

        $marca = Parametro::factory()->create(['name' => self::MARCA_TEST]);
        
        ParametroTema::create([
            'parametro_id' => $marca->id,
            'tema_id' => $this->temaMarcas->id,
            'status' => true,
            'user_create_id' => $this->user->id,
            'user_edit_id' => $this->user->id,
        ]);

        $response = $this->put(route(self::ROUTE_UPDATE, $marca->id), [
            'name' => self::MARCA_ACTUALIZADA,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_eliminar_marca()
    {
        $this->user->givePermissionTo(self::PERMISSION_ELIMINAR_MARCA);
        $this->actingAs($this->user);

        $marca = Parametro::factory()->create(['name' => 'MARCA ELIMINAR']);
        
        ParametroTema::create([
            'parametro_id' => $marca->id,
            'tema_id' => $this->temaMarcas->id,
            'status' => true,
            'user_create_id' => $this->user->id,
            'user_edit_id' => $this->user->id,
        ]);

        $response = $this->delete(route(self::ROUTE_DESTROY, $marca->id));

        $response->assertRedirect(route(self::ROUTE_INDEX));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function no_puede_eliminar_marca_sin_permiso()
    {
        $this->actingAs($this->user);

        $marca = Parametro::factory()->create(['name' => self::MARCA_TEST]);
        
        ParametroTema::create([
            'parametro_id' => $marca->id,
            'tema_id' => $this->temaMarcas->id,
            'status' => true,
            'user_create_id' => $this->user->id,
            'user_edit_id' => $this->user->id,
        ]);

        $response = $this->delete(route(self::ROUTE_DESTROY, $marca->id));

        $response->assertStatus(403);
    }

    #[Test]
    public function retorna_error_si_no_existe_tema_marcas()
    {
        $this->actingAs($this->user);

        // Eliminar el tema MARCAS
        $this->temaMarcas->delete();

        $response = $this->get(route(self::ROUTE_INDEX));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}

