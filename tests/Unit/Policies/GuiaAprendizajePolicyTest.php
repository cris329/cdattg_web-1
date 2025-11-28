<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\GuiaAprendizajePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class GuiaAprendizajePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected GuiaAprendizajePolicy $policy;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->policy = new GuiaAprendizajePolicy;
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
        Permission::firstOrCreate(['name' => 'VER GUIA APRENDIZAJE']);
        $this->user->givePermissionTo('VER GUIA APRENDIZAJE');

        $this->assertTrue($this->policy->viewAny($this->user));
    }
}
