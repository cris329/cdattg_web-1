<?php

namespace Tests\Unit\Policies;

use App\Models\Instructor;
use App\Models\User;
use App\Policies\InstructorPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InstructorPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected InstructorPolicy $policy;

    protected User $user;

    protected Instructor $instructor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
        ]);

        $this->policy = new InstructorPolicy;
        $this->user = User::factory()->create();
        $this->instructor = Instructor::factory()->create();
    }

    #[Test]
    public function usuario_sin_permiso_no_puede_ver_listado(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    #[Test]
    public function usuario_con_permiso_puede_ver_listado(): void
    {
        Permission::firstOrCreate(['name' => 'VER INSTRUCTOR']);
        $this->user->givePermissionTo('VER INSTRUCTOR');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    #[Test]
    public function usuario_con_permiso_puede_crear(): void
    {
        Permission::firstOrCreate(['name' => 'CREAR INSTRUCTOR']);
        $this->user->givePermissionTo('CREAR INSTRUCTOR');

        $this->assertTrue($this->policy->create($this->user));
    }
}
