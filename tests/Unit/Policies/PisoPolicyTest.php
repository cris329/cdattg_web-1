<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\PisoPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PisoPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected PisoPolicy $policy;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->policy = new PisoPolicy;
        $this->user = User::factory()->create();
    }

    #[Test]
    public function policy_existe(): void
    {
        $this->assertInstanceOf(PisoPolicy::class, $this->policy);
    }
}
