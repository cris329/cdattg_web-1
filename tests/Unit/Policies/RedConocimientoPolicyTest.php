<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\RedConocimientoPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RedConocimientoPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected RedConocimientoPolicy $policy;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->policy = new RedConocimientoPolicy;
        $this->user = User::factory()->create();
    }

    #[Test]
    public function policy_existe(): void
    {
        $this->assertInstanceOf(RedConocimientoPolicy::class, $this->policy);
    }
}
