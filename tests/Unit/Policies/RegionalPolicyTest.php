<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\RegionalPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegionalPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected RegionalPolicy $policy;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->policy = new RegionalPolicy;
        $this->user = User::factory()->create();
    }

    #[Test]
    public function policy_existe(): void
    {
        $this->assertInstanceOf(RegionalPolicy::class, $this->policy);
    }

    #[Test]
    public function tiene_metodo_view_any(): void
    {
        $this->assertTrue(method_exists($this->policy, 'viewAny'));
    }
}
