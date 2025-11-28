<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\DepartamentoPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DepartamentoPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected DepartamentoPolicy $policy;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->policy = new DepartamentoPolicy;
        $this->user = User::factory()->create();
    }

    #[Test]
    public function policy_existe(): void
    {
        $this->assertInstanceOf(DepartamentoPolicy::class, $this->policy);
    }
}
