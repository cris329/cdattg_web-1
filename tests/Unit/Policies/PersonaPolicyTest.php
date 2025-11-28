<?php

namespace Tests\Unit\Policies;

use App\Models\Persona;
use App\Models\User;
use App\Policies\PersonaPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PersonaPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected PersonaPolicy $policy;

    protected User $user;

    protected Persona $persona;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
        ]);

        $this->policy = new PersonaPolicy;
        $this->user = User::factory()->create();
        $this->persona = Persona::factory()->create();
    }

    #[Test]
    public function super_administrador_puede_todo(): void
    {
        $superAdmin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'SUPER ADMINISTRADOR']);
        $superAdmin->assignRole($role);

        $this->assertTrue($this->policy->viewAny($superAdmin));
        $this->assertTrue($this->policy->view($superAdmin, $this->persona));
        $this->assertTrue($this->policy->create($superAdmin));
        $this->assertTrue($this->policy->update($superAdmin, $this->persona));
    }

    #[Test]
    public function usuario_sin_permiso_no_puede_ver_listado(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    #[Test]
    public function usuario_con_permiso_puede_ver_listado(): void
    {
        Permission::firstOrCreate(['name' => 'VER PERSONA']);
        $this->user->givePermissionTo('VER PERSONA');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    #[Test]
    public function usuario_puede_ver_su_propio_perfil(): void
    {
        Permission::firstOrCreate(['name' => 'VER PERFIL']);
        $this->user->update(['persona_id' => $this->persona->id]);
        $this->user->givePermissionTo('VER PERFIL');

        $this->assertTrue($this->policy->view($this->user, $this->persona));
    }

    #[Test]
    public function usuario_sin_permiso_no_puede_crear(): void
    {
        $this->assertFalse($this->policy->create($this->user));
    }

    #[Test]
    public function usuario_con_permiso_puede_crear(): void
    {
        Permission::firstOrCreate(['name' => 'CREAR PERSONA']);
        $this->user->givePermissionTo('CREAR PERSONA');

        $this->assertTrue($this->policy->create($this->user));
    }
}
