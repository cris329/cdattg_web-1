<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\LoginPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected LoginPolicy $policy;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->policy = new LoginPolicy;
        $this->user = User::factory()->create();
    }

    #[Test]
    public function policy_existe(): void
    {
        $this->assertInstanceOf(LoginPolicy::class, $this->policy);
    }
}
