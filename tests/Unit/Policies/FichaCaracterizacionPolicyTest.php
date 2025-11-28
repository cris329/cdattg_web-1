<?php

namespace Tests\Unit\Policies;

use App\Models\FichaCaracterizacion;
use App\Models\User;
use App\Policies\FichaCaracterizacionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FichaCaracterizacionPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected FichaCaracterizacionPolicy $policy;

    protected User $user;

    protected FichaCaracterizacion $ficha;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);

        $this->policy = new FichaCaracterizacionPolicy;
        $this->user = User::factory()->create();
        $this->ficha = FichaCaracterizacion::factory()->create();
    }

    #[Test]
    public function usuario_sin_permiso_no_puede_ver_listado(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    #[Test]
    public function usuario_con_permiso_puede_ver_listado(): void
    {
        Permission::firstOrCreate(['name' => 'VER FICHA CARACTERIZACION']);
        $this->user->givePermissionTo('VER FICHA CARACTERIZACION');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    #[Test]
    public function super_administrador_puede_todo(): void
    {
        $superAdmin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'SUPER ADMINISTRADOR']);
        $superAdmin->assignRole($role);
        Permission::firstOrCreate(['name' => 'VER FICHA CARACTERIZACION']);
        $superAdmin->givePermissionTo('VER FICHA CARACTERIZACION');

        $this->assertTrue($this->policy->viewAny($superAdmin));
        $this->assertTrue($this->policy->view($superAdmin, $this->ficha));
    }

    #[Test]
    public function usuario_con_permiso_puede_crear(): void
    {
        Permission::firstOrCreate(['name' => 'CREAR FICHA CARACTERIZACION']);
        $this->user->givePermissionTo('CREAR FICHA CARACTERIZACION');

        $this->assertTrue($this->policy->create($this->user));
    }
}
