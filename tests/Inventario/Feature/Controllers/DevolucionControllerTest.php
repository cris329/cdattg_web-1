<?php

namespace Tests\Feature\Inventario;

use Tests\TestCase;
use App\Models\User;
use App\Models\Inventario\Devolucion;
use App\Models\Inventario\DetalleOrden;
use App\Models\Inventario\Orden;
use App\Models\Inventario\Producto;
use App\Models\Tema;
use App\Models\Parametro;
use App\Models\ParametroTema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;

class DevolucionControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // Permisos
    private const PERMISO_DEVOLVER_PRESTAMO = 'DEVOLVER PRESTAMO';

    // Rutas
    private const RUTA_INDEX = 'inventario.devoluciones.index';
    private const RUTA_CREATE = 'inventario.devoluciones.create';
    private const RUTA_STORE = 'inventario.devoluciones.store';

    // Vistas
    private const VISTA_INDEX = 'inventario.devoluciones.index';
    private const VISTA_CREATE = 'inventario.devoluciones.create';

    protected User $user;
    protected DetalleOrden $detalleOrden;
    protected ParametroTema $estadoAprobada;

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

        // Crear estado APROBADA para órdenes
        $estadoParametro = Parametro::firstOrCreate(
            ['name' => 'APROBADA'],
            [
                'status' => true,
                'user_create_id' => 1,
                'user_edit_id' => 1,
            ]
        );

        $this->estadoAprobada = ParametroTema::firstOrCreate(
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

        // Crear tipo de orden PRESTAMO
        $temaTipoOrden = Tema::firstOrCreate(
            ['name' => 'TIPO ORDEN'],
            [
                'status' => true,
                'user_create_id' => 1,
                'user_edit_id' => 1,
            ]
        );

        $tipoPrestamoParametro = Parametro::firstOrCreate(
            ['name' => 'PRESTAMO'],
            [
                'status' => true,
                'user_create_id' => 1,
                'user_edit_id' => 1,
            ]
        );

        $tipoPrestamo = ParametroTema::firstOrCreate(
            [
                'parametro_id' => $tipoPrestamoParametro->id,
                'tema_id' => $temaTipoOrden->id,
            ],
            [
                'status' => true,
                'user_create_id' => 1,
                'user_edit_id' => 1,
            ]
        );

        // Crear orden y detalle orden
        $orden = Orden::factory()->create([
            'tipo_orden_id' => $tipoPrestamo->id,
        ]);

        $producto = Producto::factory()->create(['cantidad' => 10]);

        $this->detalleOrden = DetalleOrden::factory()->create([
            'orden_id' => $orden->id,
            'producto_id' => $producto->id,
            'cantidad' => 5,
            'estado_orden_id' => $this->estadoAprobada->id,
        ]);

        // Crear permisos necesarios
        Permission::firstOrCreate(['name' => self::PERMISO_DEVOLVER_PRESTAMO]);

        // Crear usuario con permisos
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(self::PERMISO_DEVOLVER_PRESTAMO);
    }

    #[Test]
    public function puede_ver_listado_de_prestamos_pendientes()
    {
        $this->actingAs($this->user);

        $response = $this->get(route(self::RUTA_INDEX));

        $response->assertStatus(200);
        $response->assertViewIs(self::VISTA_INDEX);
        $response->assertViewHas('prestamos');
    }

    #[Test]
    public function no_puede_ver_listado_sin_permiso()
    {
        /** @var User $userSinPermiso */
        $userSinPermiso = User::factory()->create();
        $this->actingAs($userSinPermiso);

        $response = $this->get(route(self::RUTA_INDEX));

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_ver_formulario_de_devolucion()
    {
        $this->actingAs($this->user);

        $response = $this->get(route(self::RUTA_CREATE, $this->detalleOrden->id));

        $response->assertStatus(200);
        $response->assertViewIs(self::VISTA_CREATE);
        $response->assertViewHas('detalleOrden');
    }

    #[Test]
    public function no_puede_ver_formulario_si_prestamo_ya_devuelto()
    {
        $this->actingAs($this->user);

        // Crear devolución completa
        Devolucion::factory()->create([
            'detalle_orden_id' => $this->detalleOrden->id,
            'cantidad_devuelta' => $this->detalleOrden->cantidad,
            'cierra_sin_stock' => true,
        ]);

        $response = $this->get(route(self::RUTA_CREATE, $this->detalleOrden->id));

        $response->assertRedirect(route(self::RUTA_INDEX));
        $response->assertSessionHas('error');
    }

    #[Test]
    public function puede_registrar_devolucion()
    {
        $this->actingAs($this->user);

        $response = $this->post(route(self::RUTA_STORE), [
            'detalle_orden_id' => $this->detalleOrden->id,
            'cantidad_devuelta' => 3,
            'observaciones' => 'Devolución parcial de prueba',
        ]);

        $response->assertRedirect(route(self::RUTA_INDEX));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('devoluciones', [
            'detalle_orden_id' => $this->detalleOrden->id,
            'cantidad_devuelta' => 3,
        ]);
    }

    #[Test]
    public function puede_registrar_devolucion_completa()
    {
        $this->actingAs($this->user);

        $response = $this->post(route(self::RUTA_STORE), [
            'detalle_orden_id' => $this->detalleOrden->id,
            'cantidad_devuelta' => $this->detalleOrden->cantidad,
            'observaciones' => 'Devolución completa',
        ]);

        $response->assertRedirect(route(self::RUTA_INDEX));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function requiere_observaciones_si_cantidad_devuelta_es_cero()
    {
        $this->actingAs($this->user);

        $response = $this->post(route(self::RUTA_STORE), [
            'detalle_orden_id' => $this->detalleOrden->id,
            'cantidad_devuelta' => 0,
            'observaciones' => '', // Sin observaciones
        ]);

        $response->assertSessionHasErrors(['observaciones']);
    }

    #[Test]
    public function puede_registrar_devolucion_cero_con_observaciones()
    {
        $this->actingAs($this->user);

        $response = $this->post(route(self::RUTA_STORE), [
            'detalle_orden_id' => $this->detalleOrden->id,
            'cantidad_devuelta' => 0,
            'observaciones' => 'Producto perdido o dañado',
        ]);

        $response->assertRedirect(route(self::RUTA_INDEX));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function no_puede_registrar_devolucion_sin_permiso()
    {
        /** @var User $userSinPermiso */
        $userSinPermiso = User::factory()->create();
        $this->actingAs($userSinPermiso);

        $response = $this->post(route(self::RUTA_STORE), [
            'detalle_orden_id' => $this->detalleOrden->id,
            'cantidad_devuelta' => 2,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function valida_detalle_orden_existente()
    {
        $this->actingAs($this->user);

        $response = $this->post(route(self::RUTA_STORE), [
            'detalle_orden_id' => 99999, // ID inexistente
            'cantidad_devuelta' => 2,
        ]);

        $response->assertSessionHasErrors(['detalle_orden_id']);
    }

    #[Test]
    public function valida_cantidad_devuelta_minima()
    {
        $this->actingAs($this->user);

        $response = $this->post(route(self::RUTA_STORE), [
            'detalle_orden_id' => $this->detalleOrden->id,
            'cantidad_devuelta' => -1, // Cantidad negativa
        ]);

        $response->assertSessionHasErrors(['cantidad_devuelta']);
    }
}

