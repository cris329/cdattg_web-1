<?php

namespace Tests\Feature\Inventario;

use App\Models\Inventario\Orden;
use App\Models\Inventario\Producto;
use App\Models\ParametroTema;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OrdenControllerTest extends TestCase
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
        Permission::firstOrCreate(['name' => 'VER ORDEN']);
        Permission::firstOrCreate(['name' => 'CREAR ORDEN']);
        Permission::firstOrCreate(['name' => 'EDITAR ORDEN']);
        Permission::firstOrCreate(['name' => 'ELIMINAR ORDEN']);
        Permission::firstOrCreate(['name' => 'APROBAR ORDEN']);
        Permission::firstOrCreate(['name' => 'COMPLETAR ORDEN']);

        // Crear usuario con permisos usando factory
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER ORDEN');
    }

    #[Test]
    public function puede_ver_listado_de_ordenes(): void
    {
        $this->actingAs($this->user);

        Orden::factory()->count(5)->create();

        $response = $this->get(route('inventario.ordenes.index'));

        $response->assertStatus(200);
        $response->assertViewIs('inventario.ordenes.index');
        $response->assertViewHas('ordenes');
    }

    #[Test]
    public function puede_buscar_ordenes(): void
    {
        $this->actingAs($this->user);

        $orden = Orden::factory()->create(['descripcion_orden' => 'Orden Test']);

        $response = $this->get(route('inventario.ordenes.index', ['search' => 'Test']));

        $response->assertStatus(200);
        $response->assertViewHas('ordenes');
    }

    #[Test]
    public function puede_crear_orden(): void
    {
        $this->user->givePermissionTo('CREAR ORDEN');
        $this->actingAs($this->user);

        $producto = Producto::factory()->create(['cantidad' => 10]);
        $tipoOrden = ParametroTema::whereHas('tema', function ($q) {
            $q->where('name', 'TIPOS DE ORDEN');
        })->first();

        $estadoOrden = ParametroTema::whereHas('tema', function ($q) {
            $q->where('name', 'ESTADOS DE ORDEN');
        })->first();

        if (! $tipoOrden || ! $estadoOrden) {
            $this->markTestSkipped('Faltan parámetros necesarios (requiere seeders completos)');
        }

        $response = $this->post(route('inventario.ordenes.store'), [
            'descripcion_orden' => 'Orden de Prueba',
            'tipo_orden_id' => $tipoOrden->id,
            'fecha_devolucion' => now()->addDays(7)->format('Y-m-d'),
            'productos' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 2,
                    'estado_orden_id' => $estadoOrden->id,
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    #[Test]
    public function no_puede_crear_orden_sin_permiso(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('inventario.ordenes.store'), []);

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_ver_detalles_de_orden(): void
    {
        $this->actingAs($this->user);

        $orden = Orden::factory()->create();

        $response = $this->get(route('inventario.ordenes.show', $orden->id));

        $response->assertStatus(200);
        $response->assertViewHas('orden');
    }

    #[Test]
    public function puede_eliminar_orden(): void
    {
        $this->user->givePermissionTo('ELIMINAR ORDEN');
        $this->actingAs($this->user);

        $orden = Orden::factory()->create();

        $response = $this->delete(route('inventario.ordenes.destroy', $orden->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('ordenes', [
            'id' => $orden->id,
        ]);
    }

    #[Test]
    public function requiere_autenticacion_para_ver_ordenes(): void
    {
        $response = $this->get(route('inventario.ordenes.index'));

        $response->assertRedirect(route('login'));
    }
}
