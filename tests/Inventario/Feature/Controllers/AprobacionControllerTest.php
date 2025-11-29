<?php

namespace Tests\Feature\Inventario;

use App\Models\Inventario\DetalleOrden;
use App\Models\Inventario\Orden;
use App\Models\Inventario\Producto;
use App\Models\ParametroTema;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AprobacionControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    // Constantes para permisos
    private const PERMISSION_APROBAR_ORDEN = 'APROBAR ORDEN';

    // Constantes para rutas
    private const ROUTE_PENDIENTES = 'inventario.aprobaciones.pendientes';
    private const ROUTE_APROBAR = 'inventario.aprobaciones.aprobar';
    private const ROUTE_RECHAZAR = 'inventario.aprobaciones.rechazar';
    private const ROUTE_APROBAR_ORDEN = 'inventario.aprobaciones.aprobar-orden';
    private const ROUTE_RECHAZAR_ORDEN = 'inventario.aprobaciones.rechazar-orden';

    // Constantes para vistas
    private const VIEW_PENDIENTES = 'inventario.aprobaciones.pendientes';

    // Constantes para datos
    private const ROUTE_LOGIN = 'verificarLogin';
    private const ESTADO_EN_ESPERA = 'EN ESPERA';
    private const ESTADO_DE_ORDEN = 'ESTADOS DE ORDEN';
    private const FALTA_ESTADO = 'Falta estado EN ESPERA';

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
        Permission::firstOrCreate(['name' => self::PERMISSION_APROBAR_ORDEN]);

        // Crear usuario con permisos usando factory
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(self::PERMISSION_APROBAR_ORDEN);
    }

    #[Test]
    public function puede_ver_ordenes_pendientes(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route(self::ROUTE_PENDIENTES));

        $response->assertStatus(200);
        $response->assertViewIs(self::VIEW_PENDIENTES);
        $response->assertViewHas('detalles');
    }

    #[Test]
    public function puede_aprobar_detalle_orden(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::factory()->create(['cantidad' => 10]);
        $orden = Orden::factory()->create();

        $estadoEnEspera = ParametroTema::whereHas('parametro', function ($q) {
            $q->where('name', self::ESTADO_EN_ESPERA);
        })->whereHas('tema', function ($q) {
            $q->where('name', self::ESTADO_DE_ORDEN);
        })->first();

        $estadoAprobada = ParametroTema::whereHas('parametro', function ($q) {
            $q->where('name', 'APROBADA');
        })->whereHas('tema', function ($q) {
            $q->where('name', self::ESTADO_DE_ORDEN);
        })->first();

        if (! $estadoEnEspera || ! $estadoAprobada) {
            $this->markTestSkipped(self::FALTA_ESTADO);
        }

        $detalleOrden = DetalleOrden::factory()->create([
            'orden_id' => $orden->id,
            'producto_id' => $producto->id,
            'cantidad' => 2,
            'estado_orden_id' => $estadoEnEspera->id,
        ]);

        $response = $this->post(route(self::ROUTE_APROBAR, $detalleOrden->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    #[Test]
    public function puede_rechazar_detalle_orden(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::factory()->create(['cantidad' => 10]);
        $orden = Orden::factory()->create();

        $estadoEnEspera = ParametroTema::whereHas('parametro', function ($q) {
            $q->where('name', self::ESTADO_EN_ESPERA);
        })->whereHas('tema', function ($q) {
            $q->where('name', self::ESTADO_DE_ORDEN);
        })->first();

        if (! $estadoEnEspera) {
            $this->markTestSkipped(self::FALTA_ESTADO);
        }

        $detalleOrden = DetalleOrden::factory()->create([
            'orden_id' => $orden->id,
            'producto_id' => $producto->id,
            'cantidad' => 2,
            'estado_orden_id' => $estadoEnEspera->id,
        ]);

        $response = $this->post(route(self::ROUTE_RECHAZAR, $detalleOrden->id), [
            'motivo' => 'Motivo de rechazo de prueba',
        ]);

        $response->assertRedirect();
    }

    #[Test]
    public function no_puede_aprobar_sin_permiso(): void
    {
        /** @var User $userSinPermiso */
        $userSinPermiso = User::factory()->create();
        $this->actingAs($userSinPermiso);

        $response = $this->get(route(self::ROUTE_PENDIENTES));

        $response->assertStatus(403);
    }

    #[Test]
    public function requiere_autenticacion_para_ver_pendientes(): void
    {
        $response = $this->get(route(self::ROUTE_PENDIENTES));

        $response->assertRedirect(route(self::ROUTE_LOGIN));
    }

    #[Test]
    public function puede_aprobar_orden_completa(): void
    {
        $this->actingAs($this->user);

        $producto1 = Producto::factory()->create(['cantidad' => 10]);
        $producto2 = Producto::factory()->create(['cantidad' => 5]);
        $orden = Orden::factory()->create();

        $estadoEnEspera = ParametroTema::whereHas('parametro', function ($q) {
            $q->where('name', self::ESTADO_EN_ESPERA);
        })->whereHas('tema', function ($q) {
            $q->where('name', self::ESTADO_DE_ORDEN);
        })->first();

        if (! $estadoEnEspera) {
            $this->markTestSkipped(self::FALTA_ESTADO);
        }

        DetalleOrden::factory()->create([
            'orden_id' => $orden->id,
            'producto_id' => $producto1->id,
            'cantidad' => 2,
            'estado_orden_id' => $estadoEnEspera->id,
        ]);

        DetalleOrden::factory()->create([
            'orden_id' => $orden->id,
            'producto_id' => $producto2->id,
            'cantidad' => 1,
            'estado_orden_id' => $estadoEnEspera->id,
        ]);

        $response = $this->post(route(self::ROUTE_APROBAR_ORDEN, $orden->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    #[Test]
    public function puede_rechazar_orden_completa(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::factory()->create(['cantidad' => 10]);
        $orden = Orden::factory()->create();

        $estadoEnEspera = ParametroTema::whereHas('parametro', function ($q) {
            $q->where('name', self::ESTADO_EN_ESPERA);
        })->whereHas('tema', function ($q) {
            $q->where('name', self::ESTADO_DE_ORDEN);
        })->first();

        if (! $estadoEnEspera) {
            $this->markTestSkipped(self::FALTA_ESTADO);
        }

        DetalleOrden::factory()->create([
            'orden_id' => $orden->id,
            'producto_id' => $producto->id,
            'cantidad' => 2,
            'estado_orden_id' => $estadoEnEspera->id,
        ]);

        $response = $this->post(route(self::ROUTE_RECHAZAR_ORDEN, $orden->id), [
            'motivo_rechazo' => 'Motivo de rechazo de orden completa',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    #[Test]
    public function no_puede_aprobar_orden_sin_permiso(): void
    {
        /** @var User $userSinPermiso */
        $userSinPermiso = User::factory()->create();
        $this->actingAs($userSinPermiso);

        $orden = Orden::factory()->create();

        $response = $this->post(route(self::ROUTE_APROBAR_ORDEN, $orden->id));

        $response->assertStatus(403);
    }

    #[Test]
    public function no_puede_rechazar_orden_sin_permiso(): void
    {
        /** @var User $userSinPermiso */
        $userSinPermiso = User::factory()->create();
        $this->actingAs($userSinPermiso);

        $orden = Orden::factory()->create();

        $response = $this->post(route(self::ROUTE_RECHAZAR_ORDEN, $orden->id), [
            'motivo_rechazo' => 'Motivo',
        ]);

        $response->assertStatus(403);
    }
}
