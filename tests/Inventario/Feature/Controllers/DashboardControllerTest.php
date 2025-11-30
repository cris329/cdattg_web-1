<?php

namespace Tests\Feature\Inventario;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    // Constantes para permisos
    private const PERMISSION_VER_DASHBOARD = 'VER DASHBOARD INVENTARIO';

    // Constantes para rutas
    private const ROUTE_DASHBOARD = 'inventario.dashboard';

    // Constantes para vistas
    private const VIEW_DASHBOARD = 'inventario.dashboard.index';

    // Constantes para datos
    private const ROUTE_LOGIN = 'verificarLogin';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar migraciones y seeders de todos los módulos
        $this->migrateDatabases();
        
        // Asegurar que los seeders se ejecuten después de RefreshDatabase
        if (!\App\Models\Tema::where('name', 'CATEGORIAS')->exists()) {
            $this->artisan('db:seed', ['--force' => true]);
        }

        // Crear permisos necesarios
        Permission::firstOrCreate(['name' => self::PERMISSION_VER_DASHBOARD]);

        // Crear usuario con permisos
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(self::PERMISSION_VER_DASHBOARD);
    }

    #[Test]
    public function puede_ver_dashboard_de_inventario()
    {
        $this->actingAs($this->user);

        $response = $this->get(route(self::ROUTE_DASHBOARD));

        $response->assertStatus(200);
        $response->assertViewIs(self::VIEW_DASHBOARD);
    }

    #[Test]
    public function no_puede_ver_dashboard_sin_permiso()
    {
        /** @var User $userSinPermiso */
        $userSinPermiso = User::factory()->create();
        $this->actingAs($userSinPermiso);

        $response = $this->get(route(self::ROUTE_DASHBOARD));

        $response->assertStatus(403);
    }

    #[Test]
    public function requiere_autenticacion_para_ver_dashboard()
    {
        $response = $this->get(route(self::ROUTE_DASHBOARD));

        $response->assertRedirect(route(self::ROUTE_LOGIN));
    }
}

