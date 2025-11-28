<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\EvidenciasPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EvidenciasPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected EvidenciasPolicy $policy;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->policy = new EvidenciasPolicy;
        $this->user = User::factory()->create();
    }

    #[Test]
    public function no_permite_ninguna_accion(): void
    {
        $evidencia = \App\Models\evidencias::factory()->create();

        $this->assertFalse($this->policy->viewAny($this->user));
        $this->assertFalse($this->policy->view($this->user, $evidencia));
        $this->assertFalse($this->policy->create($this->user));
    }
}
