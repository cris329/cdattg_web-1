<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\AmbientePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AmbientePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected AmbientePolicy $policy;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->policy = new AmbientePolicy;
        $this->user = User::factory()->create();
    }

    #[Test]
    public function usuario_sin_permiso_no_puede_ver_listado(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    #[Test]
    public function usuario_con_permiso_puede_ver_listado(): void
    {
        Permission::firstOrCreate(['name' => 'VER AMBIENTE']);
        $this->user->givePermissionTo('VER AMBIENTE');

        $this->assertTrue($this->policy->viewAny($this->user));
    }
}
