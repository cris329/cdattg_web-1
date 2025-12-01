<?php

declare(strict_types=1);

namespace Tests\Inventario\Feature\Routes;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;

class InventarioRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Desactivar CSRF para tests
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);
        
        $this->migrateDatabases();
        
        // Asegurar que los seeders se ejecuten después de RefreshDatabase
        if (!\App\Models\Tema::where('name', 'CATEGORIAS')->exists()) {
            $this->artisan('db:seed', ['--force' => true, '--quiet' => true]);
        }

        // Crear usuario con todos los permisos de inventario
        $this->user = User::factory()->create();
        $this->assignAllInventarioPermissions($this->user);
    }


    protected function assignAllInventarioPermissions(User $user): void
    {
        // Obtener todos los permisos de inventario del seeder
        $permissions = Permission::whereIn('name', [
            'VER DASHBOARD INVENTARIO',
            'VER PRODUCTO',
            'VER PRODUCTOS',
            'CREAR PRODUCTO',
            'EDITAR PRODUCTO',
            'ELIMINAR PRODUCTO',
            'BUSCAR PRODUCTO',
            'VER CATALOGO PRODUCTO',
            'VER ORDEN',
            'CREAR ORDEN',
            'EDITAR ORDEN',
            'ELIMINAR ORDEN',
            'APROBAR ORDEN',
            'COMPLETAR ORDEN',
            'VER PROVEEDOR',
            'CREAR PROVEEDOR',
            'EDITAR PROVEEDOR',
            'ELIMINAR PROVEEDOR',
            'VER CATEGORIA',
            'CREAR CATEGORIA',
            'EDITAR CATEGORIA',
            'ELIMINAR CATEGORIA',
            'VER MARCA',
            'CREAR MARCA',
            'EDITAR MARCA',
            'ELIMINAR MARCA',
            'VER DEVOLUCION',
            'CREAR DEVOLUCION',
            'PROCESAR DEVOLUCION',
            'VER NOTIFICACION',
            'VER CONTRATO',
            'CREAR CONTRATO',
            'EDITAR CONTRATO',
            'ELIMINAR CONTRATO',
            'VER CARRITO',
            'AGREGAR CARRITO',
            'ACTUALIZAR CARRITO',
            'ELIMINAR CARRITO',
            'VACIAR CARRITO',
        ])->get();

        $user->givePermissionTo($permissions);
    }

    #[Test]
    public function dashboard_routes_exist_and_respond(): void
    {
        $this->actingAs($this->user);

        $this->assertTrue(Route::has('inventario.dashboard'));
        
        $response = $this->get(route('inventario.dashboard'));
        $response->assertStatus(200);
    }

    #[Test]
    public function productos_routes_exist_and_respond(): void
    {
        $this->actingAs($this->user);

        // Rutas específicas de productos
        $this->assertTrue(Route::has('inventario.productos.catalogo'));
        $this->assertTrue(Route::has('inventario.productos.buscar'));
        $this->assertTrue(Route::has('inventario.productos.agregar-carrito'));

        // Rutas resource de productos
        $this->assertTrue(Route::has('inventario.productos.index'));
        $this->assertTrue(Route::has('inventario.productos.create'));
        $this->assertTrue(Route::has('inventario.productos.store'));
        $this->assertTrue(Route::has('inventario.productos.show'));
        $this->assertTrue(Route::has('inventario.productos.edit'));
        $this->assertTrue(Route::has('inventario.productos.update'));
        $this->assertTrue(Route::has('inventario.productos.destroy'));

        // Verificar que responden
        // Nota: Las vistas pueden generar output buffers, pero PHPUnit los maneja automáticamente
        $response = $this->get(route('inventario.productos.catalogo'));
        $this->assertContains($response->status(), [200, 302, 403]);

        $response = $this->get(route('inventario.productos.index'));
        $this->assertContains($response->status(), [200, 302, 403]);
        
        // Asegurar que no haya output buffers abiertos al final del test
        // Esto evita el warning de "risky test"
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
    }

    #[Test]
    public function ordenes_routes_exist_and_respond(): void
    {
        $this->actingAs($this->user);

        // Rutas de órdenes
        $this->assertTrue(Route::has('inventario.prestamos-salidas'));
        $this->assertTrue(Route::has('inventario.prestamos-salidas.store'));
        $this->assertTrue(Route::has('inventario.ordenes.index'));
        $this->assertTrue(Route::has('inventario.ordenes.pendientes'));
        $this->assertTrue(Route::has('inventario.ordenes.completadas'));
        $this->assertTrue(Route::has('inventario.ordenes.rechazadas'));

        // Verificar que responden
        $response = $this->get(route('inventario.ordenes.index'));
        $this->assertContains($response->status(), [200, 302, 403]);

        $response = $this->get(route('inventario.prestamos-salidas'));
        $this->assertContains($response->status(), [200, 302, 403]);
    }

    #[Test]
    public function aprobaciones_routes_exist_and_respond(): void
    {
        $this->actingAs($this->user);

        // Rutas de aprobaciones
        $this->assertTrue(Route::has('inventario.aprobaciones.pendientes'));
        $this->assertTrue(Route::has('inventario.aprobaciones.aprobar'));
        $this->assertTrue(Route::has('inventario.aprobaciones.rechazar'));
        $this->assertTrue(Route::has('inventario.aprobaciones.aprobar-orden'));
        $this->assertTrue(Route::has('inventario.aprobaciones.rechazar-orden'));

        // Verificar que responden (puede ser 403 si no tiene permiso específico)
        $response = $this->get(route('inventario.aprobaciones.pendientes'));
        $this->assertContains($response->status(), [200, 302, 403]);
    }

    #[Test]
    public function carrito_routes_exist_and_respond(): void
    {
        $this->actingAs($this->user);

        // Rutas del carrito
        $this->assertTrue(Route::has('inventario.carrito.ecommerce'));
        $this->assertTrue(Route::has('inventario.carrito.agregar'));
        $this->assertTrue(Route::has('inventario.carrito.actualizar'));
        $this->assertTrue(Route::has('inventario.carrito.eliminar'));
        $this->assertTrue(Route::has('inventario.carrito.vaciar'));
        $this->assertTrue(Route::has('inventario.carrito.contenido'));

        // Verificar que responden
        $response = $this->get(route('inventario.carrito.ecommerce'));
        $this->assertContains($response->status(), [200, 302, 403]);
    }

    #[Test]
    public function proveedores_routes_exist_and_respond(): void
    {
        $this->actingAs($this->user);

        // Rutas de proveedores
        $this->assertTrue(Route::has('inventario.proveedores.municipios'));
        $this->assertTrue(Route::has('inventario.proveedores.index'));
        $this->assertTrue(Route::has('inventario.proveedores.create'));
        $this->assertTrue(Route::has('inventario.proveedores.store'));
        $this->assertTrue(Route::has('inventario.proveedores.show'));
        $this->assertTrue(Route::has('inventario.proveedores.edit'));
        $this->assertTrue(Route::has('inventario.proveedores.update'));
        $this->assertTrue(Route::has('inventario.proveedores.destroy'));

        // Verificar que responden
        $response = $this->get(route('inventario.proveedores.index'));
        $this->assertContains($response->status(), [200, 302, 403]);
    }

    #[Test]
    public function categorias_routes_exist_and_respond(): void
    {
        $this->actingAs($this->user);

        // Rutas de categorías
        $this->assertTrue(Route::has('inventario.categorias.index'));
        $this->assertTrue(Route::has('inventario.categorias.create'));
        $this->assertTrue(Route::has('inventario.categorias.store'));
        $this->assertTrue(Route::has('inventario.categorias.show'));
        $this->assertTrue(Route::has('inventario.categorias.edit'));
        $this->assertTrue(Route::has('inventario.categorias.update'));
        $this->assertTrue(Route::has('inventario.categorias.destroy'));

        // Verificar que responden
        $response = $this->get(route('inventario.categorias.index'));
        $this->assertContains($response->status(), [200, 302, 403]);
    }

    #[Test]
    public function marcas_routes_exist_and_respond(): void
    {
        $this->actingAs($this->user);

        // Rutas de marcas
        $this->assertTrue(Route::has('inventario.marcas.index'));
        $this->assertTrue(Route::has('inventario.marcas.create'));
        $this->assertTrue(Route::has('inventario.marcas.store'));
        $this->assertTrue(Route::has('inventario.marcas.show'));
        $this->assertTrue(Route::has('inventario.marcas.edit'));
        $this->assertTrue(Route::has('inventario.marcas.update'));
        $this->assertTrue(Route::has('inventario.marcas.destroy'));

        // Verificar que responden
        $response = $this->get(route('inventario.marcas.index'));
        $this->assertContains($response->status(), [200, 302, 403]);
    }

    #[Test]
    public function devoluciones_routes_exist_and_respond(): void
    {
        $this->actingAs($this->user);

        // Rutas de devoluciones
        $this->assertTrue(Route::has('inventario.devoluciones.index'));
        $this->assertTrue(Route::has('inventario.devoluciones.create'));
        $this->assertTrue(Route::has('inventario.devoluciones.store'));
        $this->assertTrue(Route::has('inventario.devoluciones.show'));
        $this->assertTrue(Route::has('inventario.devoluciones.historial'));
        $this->assertTrue(Route::has('inventario.prestamos.mis'));
        $this->assertTrue(Route::has('inventario.prestamos.historial'));

        // Verificar que responden
        $response = $this->get(route('inventario.devoluciones.index'));
        $this->assertContains($response->status(), [200, 302, 403]);
    }

    #[Test]
    public function notificaciones_routes_exist_and_respond(): void
    {
        $this->actingAs($this->user);

        // Rutas de notificaciones
        $this->assertTrue(Route::has('inventario.notificaciones.index'));
        $this->assertTrue(Route::has('inventario.notificaciones.unread'));
        $this->assertTrue(Route::has('inventario.notificaciones.read'));
        $this->assertTrue(Route::has('inventario.notificaciones.read-all'));
        $this->assertTrue(Route::has('inventario.notificaciones.destroy-all'));
        $this->assertTrue(Route::has('inventario.notificaciones.destroy'));

        // Verificar que responden
        $response = $this->get(route('inventario.notificaciones.index'));
        $this->assertContains($response->status(), [200, 302, 403]);
    }

    #[Test]
    public function contratos_convenios_routes_exist_and_respond(): void
    {
        $this->actingAs($this->user);

        // Rutas de contratos y convenios
        $this->assertTrue(Route::has('inventario.contratos-convenios.index'));
        $this->assertTrue(Route::has('inventario.contratos-convenios.create'));
        $this->assertTrue(Route::has('inventario.contratos-convenios.store'));
        $this->assertTrue(Route::has('inventario.contratos-convenios.show'));
        $this->assertTrue(Route::has('inventario.contratos-convenios.edit'));
        $this->assertTrue(Route::has('inventario.contratos-convenios.update'));
        $this->assertTrue(Route::has('inventario.contratos-convenios.destroy'));

        // Verificar que responden
        $response = $this->get(route('inventario.contratos-convenios.index'));
        $this->assertContains($response->status(), [200, 302, 403]);
    }

    #[Test]
    public function todas_las_rutas_requieren_autenticacion(): void
    {
        // Verificar que las rutas principales redirigen sin autenticación
        $routes = [
            'inventario.dashboard',
            'inventario.productos.index',
            'inventario.ordenes.index',
            'inventario.proveedores.index',
            'inventario.categorias.index',
            'inventario.marcas.index',
            'inventario.devoluciones.index',
            'inventario.notificaciones.index',
            'inventario.contratos-convenios.index',
        ];

        foreach ($routes as $routeName) {
            if (Route::has($routeName)) {
                $response = $this->get(route($routeName));
                // Debe redirigir a login o retornar 403
                $this->assertContains(
                    $response->status(),
                    [302, 401, 403],
                    "La ruta {$routeName} no requiere autenticación correctamente"
                );
            }
        }
    }
}

