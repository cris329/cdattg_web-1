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

class CategoriaControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Tema $temaCategorias;

    // Constantes para permisos
    private const PERMISSION_VER_CATEGORIA = 'VER CATEGORIA';
    private const PERMISSION_CREAR_CATEGORIA = 'CREAR CATEGORIA';
    private const PERMISSION_EDITAR_CATEGORIA = 'EDITAR CATEGORIA';
    private const PERMISSION_ELIMINAR_CATEGORIA = 'ELIMINAR CATEGORIA';

    // Constantes para rutas
    private const ROUTE_INDEX = 'inventario.categorias.index';
    private const ROUTE_CREATE = 'inventario.categorias.create';
    private const ROUTE_STORE = 'inventario.categorias.store';
    private const ROUTE_SHOW = 'inventario.categorias.show';
    private const ROUTE_EDIT = 'inventario.categorias.edit';
    private const ROUTE_UPDATE = 'inventario.categorias.update';
    private const ROUTE_DESTROY = 'inventario.categorias.destroy';

    // Constantes para vistas
    private const VIEW_INDEX = 'inventario.categorias.index';
    private const VIEW_CREATE = 'inventario.categorias.create';
    private const VIEW_SHOW = 'inventario.categorias.show';
    private const VIEW_EDIT = 'inventario.categorias.edit';

    // Constantes para datos
    private const TEMA_CATEGORIAS = 'CATEGORIAS';
    private const CATEGORIA_TEST = 'CATEGORIA TEST';
    private const CATEGORIA_ACTUALIZADA = 'CATEGORIA ACTUALIZADA';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Desactivar CSRF para tests
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);
        
        // Ejecutar solo los seeders necesarios para categorías
        // RefreshDatabase ya ejecuta las migraciones automáticamente
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\TemaSeeder::class,
        ]);

        // Obtener tema CATEGORIAS (el repositorio busca 'CATEGORIAS' sin tilde)
        $this->temaCategorias = Tema::where('name', 'CATEGORIAS')
            ->orWhere('name', 'CATEGORÍAS')
            ->first();
        
        if (!$this->temaCategorias) {
            // Si no existe, crear con el nombre que el repositorio espera
            $this->temaCategorias = Tema::create([
                'name' => 'CATEGORIAS',
                'status' => true,
                'user_create_id' => null,
                'user_edit_id' => null,
            ]);
        } elseif ($this->temaCategorias->name === 'CATEGORÍAS') {
            // Si existe con tilde, actualizar el nombre para que coincida con lo que busca el repositorio
            $this->temaCategorias->update(['name' => 'CATEGORIAS']);
        }

        // Crear permisos necesarios
        Permission::firstOrCreate(['name' => self::PERMISSION_VER_CATEGORIA]);
        Permission::firstOrCreate(['name' => self::PERMISSION_CREAR_CATEGORIA]);
        Permission::firstOrCreate(['name' => self::PERMISSION_EDITAR_CATEGORIA]);
        Permission::firstOrCreate(['name' => self::PERMISSION_ELIMINAR_CATEGORIA]);

        // Crear usuario con permisos
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(self::PERMISSION_VER_CATEGORIA);
    }

    #[Test]
    public function puede_ver_listado_de_categorias()
    {
        $this->actingAs($this->user);

        // Crear algunas categorías
        $categoria1 = Parametro::factory()->create(['name' => 'CATEGORIA 1']);
        $categoria2 = Parametro::factory()->create(['name' => 'CATEGORIA 2']);
        
        ParametroTema::create([
            'parametro_id' => $categoria1->id,
            'tema_id' => $this->temaCategorias->id,
            'status' => true,
            'user_create_id' => $this->user->id,
            'user_edit_id' => $this->user->id,
        ]);

        ParametroTema::create([
            'parametro_id' => $categoria2->id,
            'tema_id' => $this->temaCategorias->id,
            'status' => true,
            'user_create_id' => $this->user->id,
            'user_edit_id' => $this->user->id,
        ]);

        $response = $this->get(route(self::ROUTE_INDEX));

        $response->assertStatus(200);
        $response->assertViewIs(self::VIEW_INDEX);
        $response->assertViewHas('categorias');
    }

    #[Test]
    public function puede_buscar_categorias_por_nombre()
    {
        $this->actingAs($this->user);

        $categoria = Parametro::factory()->create(['name' => 'ELECTRONICA']);
        
        ParametroTema::create([
            'parametro_id' => $categoria->id,
            'tema_id' => $this->temaCategorias->id,
            'status' => true,
            'user_create_id' => $this->user->id,
            'user_edit_id' => $this->user->id,
        ]);

        $response = $this->get(route(self::ROUTE_INDEX, ['search' => 'ELECTRONICA']));

        $response->assertStatus(200);
        $response->assertSee('ELECTRONICA', false);
    }

    #[Test]
    public function puede_ver_formulario_de_creacion()
    {
        $this->user->givePermissionTo(self::PERMISSION_CREAR_CATEGORIA);
        $this->actingAs($this->user);

        $response = $this->get(route(self::ROUTE_CREATE));

        $response->assertStatus(200);
        $response->assertViewIs(self::VIEW_CREATE);
    }

    #[Test]
    public function puede_crear_categoria()
    {
        $this->user->givePermissionTo(self::PERMISSION_CREAR_CATEGORIA);
        $this->actingAs($this->user);

        $response = $this->post(route(self::ROUTE_STORE), [
            'name' => 'NUEVA CATEGORIA',
        ]);

        $response->assertRedirect(route(self::ROUTE_INDEX));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('parametros', [
            'name' => 'NUEVA CATEGORIA',
        ]);
    }

    #[Test]
    public function no_puede_crear_categoria_sin_permiso()
    {
        $this->actingAs($this->user);

        $response = $this->post(route(self::ROUTE_STORE), [
            'name' => 'CATEGORIA SIN PERMISO',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_ver_detalles_de_categoria()
    {
        $this->actingAs($this->user);

        $categoria = Parametro::factory()->create(['name' => self::CATEGORIA_TEST]);
        
        ParametroTema::create([
            'parametro_id' => $categoria->id,
            'tema_id' => $this->temaCategorias->id,
            'status' => true,
            'user_create_id' => $this->user->id,
            'user_edit_id' => $this->user->id,
        ]);

        $response = $this->get(route(self::ROUTE_SHOW, $categoria->id));

        $response->assertStatus(200);
        $response->assertViewIs(self::VIEW_SHOW);
        $response->assertViewHas('categoria');
    }

    #[Test]
    public function puede_ver_formulario_de_edicion()
    {
        $this->user->givePermissionTo(self::PERMISSION_EDITAR_CATEGORIA);
        $this->actingAs($this->user);

        $categoria = Parametro::factory()->create(['name' => 'CATEGORIA EDITAR']);
        
        ParametroTema::create([
            'parametro_id' => $categoria->id,
            'tema_id' => $this->temaCategorias->id,
            'status' => true,
            'user_create_id' => $this->user->id,
            'user_edit_id' => $this->user->id,
        ]);

        $response = $this->get(route(self::ROUTE_EDIT, $categoria->id));

        $response->assertStatus(200);
        $response->assertViewIs(self::VIEW_EDIT);
        $response->assertViewHas('categoria');
    }

    #[Test]
    public function puede_actualizar_categoria()
    {
        $this->user->givePermissionTo(self::PERMISSION_EDITAR_CATEGORIA);
        $this->actingAs($this->user);

        $categoria = Parametro::factory()->create(['name' => 'CATEGORIA ORIGINAL']);
        
        ParametroTema::create([
            'parametro_id' => $categoria->id,
            'tema_id' => $this->temaCategorias->id,
            'status' => true,
            'user_create_id' => $this->user->id,
            'user_edit_id' => $this->user->id,
        ]);

        $response = $this->put(route(self::ROUTE_UPDATE, $categoria->id), [
            'name' => self::CATEGORIA_ACTUALIZADA,
        ]);

        $response->assertRedirect(route(self::ROUTE_INDEX));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('parametros', [
            'id' => $categoria->id,
            'name' => self::CATEGORIA_ACTUALIZADA,
        ]);
    }

    #[Test]
    public function no_puede_actualizar_categoria_sin_permiso()
    {
        $this->actingAs($this->user);

        $categoria = Parametro::factory()->create(['name' => self::CATEGORIA_TEST]);
        
        ParametroTema::create([
            'parametro_id' => $categoria->id,
            'tema_id' => $this->temaCategorias->id,
            'status' => true,
            'user_create_id' => $this->user->id,
            'user_edit_id' => $this->user->id,
        ]);

        $response = $this->put(route(self::ROUTE_UPDATE, $categoria->id), [
            'name' => self::CATEGORIA_ACTUALIZADA,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_eliminar_categoria()
    {
        $this->user->givePermissionTo(self::PERMISSION_ELIMINAR_CATEGORIA);
        $this->actingAs($this->user);

        $categoria = Parametro::factory()->create(['name' => 'CATEGORIA ELIMINAR']);
        
        ParametroTema::create([
            'parametro_id' => $categoria->id,
            'tema_id' => $this->temaCategorias->id,
            'status' => true,
            'user_create_id' => $this->user->id,
            'user_edit_id' => $this->user->id,
        ]);

        $response = $this->delete(route(self::ROUTE_DESTROY, $categoria->id));

        $response->assertRedirect(route(self::ROUTE_INDEX));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function no_puede_eliminar_categoria_sin_permiso()
    {
        $this->actingAs($this->user);

        $categoria = Parametro::factory()->create(['name' => self::CATEGORIA_TEST]);
        
        ParametroTema::create([
            'parametro_id' => $categoria->id,
            'tema_id' => $this->temaCategorias->id,
            'status' => true,
            'user_create_id' => $this->user->id,
            'user_edit_id' => $this->user->id,
        ]);

        $response = $this->delete(route(self::ROUTE_DESTROY, $categoria->id));

        $response->assertStatus(403);
    }

    #[Test]
    public function retorna_error_si_no_existe_tema_categorias()
    {
        $this->actingAs($this->user);

        // Eliminar el tema CATEGORIAS
        $this->temaCategorias->delete();

        $response = $this->get(route(self::ROUTE_INDEX));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}

