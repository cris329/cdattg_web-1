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
        Permission::firstOrCreate(['name' => 'VER DASHBOARD INVENTARIO']);

        // Crear usuario con permisos
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER DASHBOARD INVENTARIO');
    }

    #[Test]
    public function puede_ver_dashboard_de_inventario()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('inventario.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('inventario.dashboard.index');
    }

    #[Test]
    public function no_puede_ver_dashboard_sin_permiso()
    {
        $userSinPermiso = User::factory()->create();
        $this->actingAs($userSinPermiso);

        $response = $this->get(route('inventario.dashboard'));

        $response->assertStatus(403);
    }

    #[Test]
    public function requiere_autenticacion_para_ver_dashboard()
    {
        $response = $this->get(route('inventario.dashboard'));

        $response->assertRedirect(route('login'));
    }
}

