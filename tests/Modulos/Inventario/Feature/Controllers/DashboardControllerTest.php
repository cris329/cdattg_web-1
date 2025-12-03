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

    private const PERMISSION_VER_DASHBOARD = 'VER DASHBOARD INVENTARIO';
    private const ROUTE_DASHBOARD = 'inventario.dashboard';
    private const VIEW_DASHBOARD = 'inventario.dashboard.index';
    private const ROUTE_LOGIN = 'verificarLogin';

    protected function setUp(): void
    {
        parent::setUp();
        $this->ejecutarSeedersNecesarios();
        $this->crearPermisos();
        $this->crearUsuarioConPermisos();
    }

    private function ejecutarSeedersNecesarios(): void
    {
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\TemaSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\PersonaSeeder::class,
            \Database\Seeders\UsersSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
            \Database\Seeders\SedeSeeder::class,
            \Database\Seeders\BloqueSeeder::class,
            \Database\Seeders\PisoSeeder::class,
            \Database\Seeders\AmbienteSeeder::class,
        ]);
    }

    private function crearPermisos(): void
    {
        Permission::firstOrCreate(['name' => self::PERMISSION_VER_DASHBOARD]);
    }

    private function crearUsuarioConPermisos(): void
    {
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(self::PERMISSION_VER_DASHBOARD);
    }

    private function crearUsuarioSinPermisos(): User
    {
        return User::factory()->create();
    }

    private function verificarAccesoConPermisos(): void
    {
        $this->actingAs($this->user);
        $response = $this->get(route(self::ROUTE_DASHBOARD));
        $response->assertStatus(200);
        $response->assertViewIs(self::VIEW_DASHBOARD);
    }

    private function verificarAccesoSinPermisos(): void
    {
        $userSinPermiso = $this->crearUsuarioSinPermisos();
        $this->actingAs($userSinPermiso);
        $response = $this->get(route(self::ROUTE_DASHBOARD));
        $response->assertStatus(403);
    }

    private function verificarRedireccionAutenticacion(): void
    {
        $response = $this->get(route(self::ROUTE_DASHBOARD));
        $response->assertRedirect(route(self::ROUTE_LOGIN));
    }

    #[Test]
    public function puede_ver_dashboard_de_inventario(): void
    {
        $this->verificarAccesoConPermisos();
    }

    #[Test]
    public function no_puede_ver_dashboard_sin_permiso(): void
    {
        $this->verificarAccesoSinPermisos();
    }

    #[Test]
    public function requiere_autenticacion_para_ver_dashboard(): void
    {
        $this->verificarRedireccionAutenticacion();
    }
}

