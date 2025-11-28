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

    protected User $user;
    protected Proveedor $proveedor;
    protected ParametroTema $estado;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar migraciones y seeders de todos los módulos
        $this->migrateDatabases();
        
        // Asegurar que los seeders se ejecuten después de RefreshDatabase
        if (!\App\Models\Pais::where('pais', 'COLOMBIA')->exists()) {
            $this->artisan('db:seed', ['--force' => true]);
        }

        // Crear tema ESTADOS si no existe
        $temaEstados = Tema::firstOrCreate(
            ['name' => 'ESTADOS'],
            [
                'status' => true,
                'user_create_id' => 1,
                'user_edit_id' => 1,
            ]
        );

        // Crear estado para contratos
        $estadoParametro = Parametro::firstOrCreate(
            ['name' => 'ACTIVO'],
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
            ['pais' => 'COLOMBIA'],
            ['status' => true]
        );

        $departamento = \App\Models\Departamento::firstOrCreate(
            ['departamento' => 'ANTIOQUIA'],
            [
                'pais_id' => $pais->id,
                'status' => true,
            ]
        );

        $municipio = \App\Models\Municipio::firstOrCreate(
            [
                'municipio' => 'MEDELLIN',
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
        Permission::firstOrCreate(['name' => 'VER CONTRATO']);
        Permission::firstOrCreate(['name' => 'CREAR CONTRATO']);
        Permission::firstOrCreate(['name' => 'EDITAR CONTRATO']);
        Permission::firstOrCreate(['name' => 'ELIMINAR CONTRATO']);

        // Crear usuario con permisos
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER CONTRATO');
    }

    #[Test]
    public function puede_ver_listado_de_contratos_convenios()
    {
        $this->actingAs($this->user);

        ContratoConvenio::factory()->count(3)->create([
            'proveedor_id' => $this->proveedor->id,
            'estado_id' => $this->estado->id,
        ]);

        $response = $this->get(route('inventario.contratos-convenios.index'));

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

        $contrato = ContratoConvenio::factory()->create([
            'name' => 'CONTRATO ESPECIAL 2024',
            'proveedor_id' => $this->proveedor->id,
            'estado_id' => $this->estado->id,
        ]);

        $response = $this->get(route('inventario.contratos-convenios.index', ['search' => 'ESPECIAL']));

        $response->assertStatus(200);
        $response->assertSee('ESPECIAL', false);
    }

    #[Test]
    public function puede_ver_formulario_de_creacion()
    {
        $this->user->givePermissionTo('CREAR CONTRATO');
        $this->actingAs($this->user);

        $response = $this->get(route('inventario.contratos-convenios.create'));

        $response->assertStatus(200);
        $response->assertViewIs('inventario.contratos_convenios.create');
        $response->assertViewHas('proveedores');
    }

    #[Test]
    public function puede_crear_contrato_convenio()
    {
        $this->user->givePermissionTo('CREAR CONTRATO');
        $this->actingAs($this->user);

        $response = $this->post(route('inventario.contratos-convenios.store'), [
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

        $response = $this->post(route('inventario.contratos-convenios.store'), [
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

        $response = $this->get(route('inventario.contratos-convenios.show', $contrato->id));

        $response->assertStatus(200);
        $response->assertViewIs('inventario.contratos_convenios.show');
        $response->assertViewHas('contratoConvenio');
    }

    #[Test]
    public function puede_ver_formulario_de_edicion()
    {
        $this->user->givePermissionTo('EDITAR CONTRATO');
        $this->actingAs($this->user);

        $contrato = ContratoConvenio::factory()->create([
            'proveedor_id' => $this->proveedor->id,
            'estado_id' => $this->estado->id,
        ]);

        $response = $this->get(route('inventario.contratos-convenios.edit', $contrato->id));

        $response->assertStatus(200);
        $response->assertViewIs('inventario.contratos_convenios.edit');
        $response->assertViewHas('contratoConvenio');
        $response->assertViewHas('proveedores');
    }

    #[Test]
    public function puede_actualizar_contrato_convenio()
    {
        $this->user->givePermissionTo('EDITAR CONTRATO');
        $this->actingAs($this->user);

        $contrato = ContratoConvenio::factory()->create([
            'name' => 'CONTRATO ORIGINAL',
            'proveedor_id' => $this->proveedor->id,
            'estado_id' => $this->estado->id,
        ]);

        $response = $this->put(route('inventario.contratos-convenios.update', $contrato->id), [
            'name' => 'CONTRATO ACTUALIZADO',
            'codigo' => $contrato->codigo,
            'proveedor_id' => $this->proveedor->id,
            'fecha_inicio' => $contrato->fecha_inicio,
            'fecha_fin' => $contrato->fecha_fin,
            'estado_id' => $this->estado->id,
        ]);

        $response->assertRedirect(route('inventario.contratos-convenios.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contratos_convenios', [
            'id' => $contrato->id,
            'name' => 'CONTRATO ACTUALIZADO',
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

        $response = $this->put(route('inventario.contratos-convenios.update', $contrato->id), [
            'name' => 'CONTRATO ACTUALIZADO',
            'estado_id' => $this->estado->id,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_eliminar_contrato_convenio()
    {
        $this->user->givePermissionTo('ELIMINAR CONTRATO');
        $this->actingAs($this->user);

        $contrato = ContratoConvenio::factory()->create([
            'proveedor_id' => $this->proveedor->id,
            'estado_id' => $this->estado->id,
        ]);

        $response = $this->delete(route('inventario.contratos-convenios.destroy', $contrato->id));

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

        $response = $this->delete(route('inventario.contratos-convenios.destroy', $contrato->id));

        $response->assertStatus(403);
    }
}

