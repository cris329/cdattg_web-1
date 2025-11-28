<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\ResultadosAprendizajePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ResultadosAprendizajePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected ResultadosAprendizajePolicy $policy;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->policy = new ResultadosAprendizajePolicy;
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
        Permission::firstOrCreate(['name' => 'VER RESULTADO APRENDIZAJE']);
        $this->user->givePermissionTo('VER RESULTADO APRENDIZAJE');

        $this->assertTrue($this->policy->viewAny($this->user));
    }
}
