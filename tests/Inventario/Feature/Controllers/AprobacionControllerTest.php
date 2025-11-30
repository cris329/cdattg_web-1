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

        // Ejecutar migraciones y seeders de todos los módulos
        $this->migrateDatabases();
        
        // Desactivar CSRF para tests (después de migrateDatabases)
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);
        
        // Asegurar que los seeders se ejecuten después de RefreshDatabase
        if (!\App\Models\Tema::where('name', self::ESTADO_DE_ORDEN)->exists()) {
            $this->artisan('db:seed', ['--force' => true]);
        }

        // Crear tema ESTADOS DE ORDEN si no existe
        $temaEstados = \App\Models\Tema::firstOrCreate(
            ['name' => self::ESTADO_DE_ORDEN],
            [
                'status' => true,
                'user_create_id' => null,
                'user_edit_id' => null,
            ]
        );

        // Crear estados necesarios si no existen
        $estados = ['EN ESPERA', 'APROBADA', 'RECHAZADA'];
        foreach ($estados as $nombreEstado) {
            $parametro = \App\Models\Parametro::firstOrCreate(
                ['name' => $nombreEstado],
                [
                    'status' => true,
                    'user_create_id' => null,
                    'user_edit_id' => null,
                ]
            );
            
            \App\Models\ParametroTema::firstOrCreate(
                [
                    'parametro_id' => $parametro->id,
                    'tema_id' => $temaEstados->id,
                ],
                [
                    'status' => true,
                    'user_create_id' => null,
                    'user_edit_id' => null,
                ]
            );
        }

        // Crear permisos necesarios
        Permission::firstOrCreate(['name' => self::PERMISSION_APROBAR_ORDEN]);

        // Crear usuario con permisos usando factory
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(self::PERMISSION_APROBAR_ORDEN);
    }

    private function obtenerEstadoEnEspera(): ?ParametroTema
    {
        return ParametroTema::whereHas('parametro', function ($q) {
            $q->where('name', self::ESTADO_EN_ESPERA);
        })->whereHas('tema', function ($q) {
            $q->where('name', self::ESTADO_DE_ORDEN);
        })->first();
    }

    private function obtenerEstadoAprobada(): ?ParametroTema
    {
        return ParametroTema::whereHas('parametro', function ($q) {
            $q->where('name', 'APROBADA');
        })->whereHas('tema', function ($q) {
            $q->where('name', self::ESTADO_DE_ORDEN);
        })->first();
    }

    private function omitirSiFaltaEstado(?ParametroTema $estado): void
    {
        if (! $estado) {
            $this->markTestSkipped(self::FALTA_ESTADO);
        }
    }

    private function omitirSiFaltanEstados(?ParametroTema $estado1, ?ParametroTema $estado2): void
    {
        if (! $estado1 || ! $estado2) {
            $this->markTestSkipped(self::FALTA_ESTADO);
        }
    }

    private function crearUsuarioSinPermiso(): User
    {
        return User::factory()->create();
    }

    private function crearDetalleOrdenConEstado(
        Orden $orden,
        Producto $producto,
        ParametroTema $estado,
        int $cantidad = 2
    ): DetalleOrden {
        return DetalleOrden::factory()->create([
            'orden_id' => $orden->id,
            'producto_id' => $producto->id,
            'cantidad' => $cantidad,
            'estado_orden_id' => $estado->id,
        ]);
    }

    #[Test]
    public function puede_ver_ordenes_pendientes(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route(self::ROUTE_PENDIENTES));

        // Si hay un error 500, mostrar el contenido para debug
        if ($response->status() === 500) {
            $content = $response->getContent();
            $this->fail("Error 500: " . substr($content, 0, 1000));
        }

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

        $estadoEnEspera = $this->obtenerEstadoEnEspera();
        $estadoAprobada = $this->obtenerEstadoAprobada();
        $this->omitirSiFaltanEstados($estadoEnEspera, $estadoAprobada);

        $detalleOrden = $this->crearDetalleOrdenConEstado($orden, $producto, $estadoEnEspera);

        $response = $this->from(route(self::ROUTE_PENDIENTES))
            ->post(route(self::ROUTE_APROBAR, $detalleOrden->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    #[Test]
    public function puede_rechazar_detalle_orden(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::factory()->create(['cantidad' => 10]);
        $orden = Orden::factory()->create();

        $estadoEnEspera = $this->obtenerEstadoEnEspera();
        $this->omitirSiFaltaEstado($estadoEnEspera);

        $detalleOrden = $this->crearDetalleOrdenConEstado($orden, $producto, $estadoEnEspera);

        $response = $this->from(route(self::ROUTE_PENDIENTES))
            ->post(route(self::ROUTE_RECHAZAR, $detalleOrden->id), [
                'motivo_rechazo' => 'Motivo de rechazo de prueba',
            ]);

        $response->assertRedirect();
    }

    #[Test]
    public function no_puede_aprobar_sin_permiso(): void
    {
        $userSinPermiso = $this->crearUsuarioSinPermiso();
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

        $estadoEnEspera = $this->obtenerEstadoEnEspera();
        $this->omitirSiFaltaEstado($estadoEnEspera);

        $this->crearDetalleOrdenConEstado($orden, $producto1, $estadoEnEspera);
        $this->crearDetalleOrdenConEstado($orden, $producto2, $estadoEnEspera, 1);

        $response = $this->from(route(self::ROUTE_PENDIENTES))
            ->post(route(self::ROUTE_APROBAR_ORDEN, $orden->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    #[Test]
    public function puede_rechazar_orden_completa(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::factory()->create(['cantidad' => 10]);
        $orden = Orden::factory()->create();

        $estadoEnEspera = $this->obtenerEstadoEnEspera();
        $this->omitirSiFaltaEstado($estadoEnEspera);

        $this->crearDetalleOrdenConEstado($orden, $producto, $estadoEnEspera);

        $response = $this->from(route(self::ROUTE_PENDIENTES))
            ->post(route(self::ROUTE_RECHAZAR_ORDEN, $orden->id), [
                'motivo_rechazo' => 'Motivo de rechazo de orden completa',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    #[Test]
    public function no_puede_aprobar_orden_sin_permiso(): void
    {
        $userSinPermiso = $this->crearUsuarioSinPermiso();
        $this->actingAs($userSinPermiso);

        $orden = Orden::factory()->create();

        $response = $this->post(route(self::ROUTE_APROBAR_ORDEN, $orden->id));

        $response->assertStatus(403);
    }

    #[Test]
    public function no_puede_rechazar_orden_sin_permiso(): void
    {
        $userSinPermiso = $this->crearUsuarioSinPermiso();
        $this->actingAs($userSinPermiso);

        $orden = Orden::factory()->create();

        $response = $this->post(route(self::ROUTE_RECHAZAR_ORDEN, $orden->id), [
            'motivo_rechazo' => 'Motivo',
        ]);

        $response->assertStatus(403);
    }
}
