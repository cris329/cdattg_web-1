<?php

namespace Tests\Feature\Inventario;

use Tests\TestCase;
use App\Models\User;
use App\Models\Inventario\ContratoConvenio;
use App\Models\Inventario\Proveedor;
use App\Models\Tema;
use App\Models\Parametro;
use App\Models\ParametroTema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;

class ContratoConvenioControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // Constantes para permisos
    private const PERMISSION_VER_CONTRATO = 'VER CONTRATO';
    private const PERMISSION_CREAR_CONTRATO = 'CREAR CONTRATO';
    private const PERMISSION_EDITAR_CONTRATO = 'EDITAR CONTRATO';
    private const PERMISSION_ELIMINAR_CONTRATO = 'ELIMINAR CONTRATO';

    // Constantes para rutas
    private const ROUTE_INDEX = 'inventario.contratos-convenios.index';
    private const ROUTE_CREATE = 'inventario.contratos-convenios.create';
    private const ROUTE_STORE = 'inventario.contratos-convenios.store';
    private const ROUTE_SHOW = 'inventario.contratos-convenios.show';
    private const ROUTE_EDIT = 'inventario.contratos-convenios.edit';
    private const ROUTE_UPDATE = 'inventario.contratos-convenios.update';
    private const ROUTE_DESTROY = 'inventario.contratos-convenios.destroy';

    // Constantes para nombres de temas y estados
    private const TEMA_ESTADOS = 'ESTADOS';
    private const ESTADO_ACTIVO = 'ACTIVO';
    private const PAIS_COLOMBIA = 'COLOMBIA';
    private const DEPARTAMENTO_ANTIOQUIA = 'ANTIOQUIA';
    private const MUNICIPIO_MEDELLIN = 'MEDELLIN';

    // Constantes para nombres de contratos
    private const CONTRATO_ACTUALIZADO = 'CONTRATO ACTUALIZADO';

    protected User $user;
    protected Proveedor $proveedor;
    protected ParametroTema $estado;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar migraciones y seeders de todos los módulos
        $this->migrateDatabases();
        
        // Desactivar CSRF para tests
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);
        
        // Asegurar que los seeders se ejecuten después de RefreshDatabase
        if (!\App\Models\Pais::where('pais', self::PAIS_COLOMBIA)->exists()) {
            $this->artisan('db:seed', ['--force' => true]);
        }

        // Crear tema ESTADOS si no existe
        $temaEstados = Tema::firstOrCreate(
            ['name' => self::TEMA_ESTADOS],
            [
                'status' => true,
                'user_create_id' => 1,
                'user_edit_id' => 1,
            ]
        );

        // Crear estado para contratos
        $estadoParametro = Parametro::firstOrCreate(
            ['name' => self::ESTADO_ACTIVO],
            [
                'status' => true,
                'user_create_id' => 1,
                'user_edit_id' => 1,
            ]
        );

        $this->estado = ParametroTema::firstOrCreate(
            [
                'parametro_id' => $estadoParametro->id,
                'tema_id' => $temaEstados->id,
            ],
            [
                'status' => true,
                'user_create_id' => 1,
                'user_edit_id' => 1,
            ]
        );

        // Crear país y ubicación para proveedor
        $pais = \App\Models\Pais::firstOrCreate(
            ['pais' => self::PAIS_COLOMBIA],
            ['status' => true]
        );

        $departamento = \App\Models\Departamento::firstOrCreate(
            ['departamento' => self::DEPARTAMENTO_ANTIOQUIA],
            [
                'pais_id' => $pais->id,
                'status' => true,
            ]
        );

        $municipio = \App\Models\Municipio::firstOrCreate(
            [
                'municipio' => self::MUNICIPIO_MEDELLIN,
                'departamento_id' => $departamento->id,
            ],
            ['status' => true]
        );

        // Crear proveedor
        $this->proveedor = Proveedor::factory()->create([
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
        ]);

        // Crear permisos necesarios
        Permission::firstOrCreate(['name' => self::PERMISSION_VER_CONTRATO]);
        Permission::firstOrCreate(['name' => self::PERMISSION_CREAR_CONTRATO]);
        Permission::firstOrCreate(['name' => self::PERMISSION_EDITAR_CONTRATO]);
        Permission::firstOrCreate(['name' => self::PERMISSION_ELIMINAR_CONTRATO]);

        // Crear usuario con permisos
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(self::PERMISSION_VER_CONTRATO);
    }

    #[Test]
    public function puede_ver_listado_de_contratos_convenios()
    {
        $this->actingAs($this->user);

        ContratoConvenio::factory()->count(3)->create([
            'proveedor_id' => $this->proveedor->id,
            'estado_id' => $this->estado->id,
        ]);

        $response = $this->get(route(self::ROUTE_INDEX));

        $response->assertStatus(200);
        $response->assertViewIs('inventario.contratos_convenios.index');
        $response->assertViewHas('contratosConvenios');
        $response->assertViewHas('estados');
        $response->assertViewHas('proveedores');
    }

    #[Test]
    public function puede_buscar_contratos_por_nombre()
    {
        $this->actingAs($this->user);

        // Crear contrato para la búsqueda
        ContratoConvenio::factory()->create([
            'name' => 'CONTRATO ESPECIAL 2024',
            'proveedor_id' => $this->proveedor->id,
            'estado_id' => $this->estado->id,
        ]);

        $response = $this->get(route(self::ROUTE_INDEX, ['search' => 'ESPECIAL']));

        $response->assertStatus(200);
        $response->assertSee('ESPECIAL', false);
    }

    #[Test]
    public function puede_ver_formulario_de_creacion()
    {
        $this->user->givePermissionTo(self::PERMISSION_CREAR_CONTRATO);
        $this->actingAs($this->user);

        $response = $this->get(route(self::ROUTE_CREATE));

        $response->assertStatus(200);
        $response->assertViewIs('inventario.contratos_convenios.create');
        $response->assertViewHas('proveedores');
    }

    #[Test]
    public function puede_crear_contrato_convenio()
    {
        $this->user->givePermissionTo(self::PERMISSION_CREAR_CONTRATO);
        $this->actingAs($this->user);

        $response = $this->post(route(self::ROUTE_STORE), [
            'name' => 'NUEVO CONTRATO 2024',
            'codigo' => 'CT-2024-001',
            'proveedor_id' => $this->proveedor->id,
            'fecha_inicio' => '2024-01-01',
            'fecha_fin' => '2024-12-31',
            'estado_id' => $this->estado->id,
        ]);

        $response->assertRedirect(route('inventario.contratos-convenios.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contratos_convenios', [
            'name' => 'NUEVO CONTRATO 2024',
            'codigo' => 'CT-2024-001',
        ]);
    }

    #[Test]
    public function no_puede_crear_contrato_sin_permiso()
    {
        $this->actingAs($this->user);

        $response = $this->post(route(self::ROUTE_STORE), [
            'name' => 'CONTRATO SIN PERMISO',
            'estado_id' => $this->estado->id,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_ver_detalles_de_contrato()
    {
        $this->actingAs($this->user);

        $contrato = ContratoConvenio::factory()->create([
            'proveedor_id' => $this->proveedor->id,
            'estado_id' => $this->estado->id,
        ]);

        $response = $this->get(route(self::ROUTE_SHOW, $contrato->id));

        $response->assertStatus(200);
        $response->assertViewIs('inventario.contratos_convenios.show');
        $response->assertViewHas('contratoConvenio');
    }

    #[Test]
    public function puede_ver_formulario_de_edicion()
    {
        $this->user->givePermissionTo(self::PERMISSION_EDITAR_CONTRATO);
        $this->actingAs($this->user);

        $contrato = ContratoConvenio::factory()->create([
            'proveedor_id' => $this->proveedor->id,
            'estado_id' => $this->estado->id,
        ]);

        $response = $this->get(route(self::ROUTE_EDIT, $contrato->id));

        $response->assertStatus(200);
        $response->assertViewIs('inventario.contratos_convenios.edit');
        $response->assertViewHas('contratoConvenio');
        $response->assertViewHas('proveedores');
    }

    #[Test]
    public function puede_actualizar_contrato_convenio()
    {
        $this->user->givePermissionTo(self::PERMISSION_EDITAR_CONTRATO);
        $this->actingAs($this->user);

        $contrato = ContratoConvenio::factory()->create([
            'name' => 'CONTRATO ORIGINAL',
            'proveedor_id' => $this->proveedor->id,
            'estado_id' => $this->estado->id,
        ]);

        $response = $this->put(route(self::ROUTE_UPDATE, $contrato->id), [
            'name' => self::CONTRATO_ACTUALIZADO,
            'codigo' => $contrato->codigo,
            'proveedor_id' => $this->proveedor->id,
            'fecha_inicio' => $contrato->fecha_inicio,
            'fecha_fin' => $contrato->fecha_fin,
            'estado_id' => $this->estado->id,
        ]);

        $response->assertRedirect(route(self::ROUTE_INDEX));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contratos_convenios', [
            'id' => $contrato->id,
            'name' => self::CONTRATO_ACTUALIZADO,
        ]);
    }

    #[Test]
    public function no_puede_actualizar_contrato_sin_permiso()
    {
        $this->actingAs($this->user);

        $contrato = ContratoConvenio::factory()->create([
            'proveedor_id' => $this->proveedor->id,
            'estado_id' => $this->estado->id,
        ]);

        $response = $this->put(route(self::ROUTE_UPDATE, $contrato->id), [
            'name' => self::CONTRATO_ACTUALIZADO,
            'estado_id' => $this->estado->id,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_eliminar_contrato_convenio()
    {
        $this->user->givePermissionTo(self::PERMISSION_ELIMINAR_CONTRATO);
        $this->actingAs($this->user);

        $contrato = ContratoConvenio::factory()->create([
            'proveedor_id' => $this->proveedor->id,
            'estado_id' => $this->estado->id,
        ]);

        $response = $this->delete(route(self::ROUTE_DESTROY, $contrato->id));

        $response->assertRedirect(route('inventario.contratos-convenios.index'));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function no_puede_eliminar_contrato_sin_permiso()
    {
        $this->actingAs($this->user);

        $contrato = ContratoConvenio::factory()->create([
            'proveedor_id' => $this->proveedor->id,
            'estado_id' => $this->estado->id,
        ]);

        $response = $this->delete(route(self::ROUTE_DESTROY, $contrato->id));

        $response->assertStatus(403);
    }
}

