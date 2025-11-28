<?php

namespace Tests\Unit\Policies;

use App\Models\ProgramaFormacion;
use App\Models\User;
use App\Policies\ProgramaFormacionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProgramaFormacionPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected ProgramaFormacionPolicy $policy;

    protected User $user;

    protected ProgramaFormacion $programa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);

        $this->policy = new ProgramaFormacionPolicy;
        $this->user = User::factory()->create();
        $this->programa = ProgramaFormacion::factory()->create();
    }

    #[Test]
    public function usuario_sin_permiso_no_puede_ver_listado(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    #[Test]
    public function usuario_con_permiso_puede_ver_listado(): void
    {
        Permission::firstOrCreate(['name' => 'VER PROGRAMA DE FORMACION']);
        $this->user->givePermissionTo('VER PROGRAMA DE FORMACION');

        $this->assertTrue($this->policy->viewAny($this->user));
    }
}
