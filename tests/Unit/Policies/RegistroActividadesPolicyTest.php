<?php

namespace Tests\Unit\Policies;

use App\Models\RegistroActividades;
use App\Models\User;
use App\Policies\RegistroActividadesPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistroActividadesPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected RegistroActividadesPolicy $policy;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->policy = new RegistroActividadesPolicy;
        $this->user = User::factory()->create();
    }

    #[Test]
    public function no_permite_ninguna_accion(): void
    {
        $registro = RegistroActividades::factory()->create();

        $this->assertFalse($this->policy->viewAny($this->user));
        $this->assertFalse($this->policy->view($this->user, $registro));
        $this->assertFalse($this->policy->create($this->user));
    }
}
