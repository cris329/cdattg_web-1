<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\BloquePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BloquePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected BloquePolicy $policy;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->policy = new BloquePolicy;
        $this->user = User::factory()->create();
    }

    #[Test]
    public function policy_existe(): void
    {
        $this->assertInstanceOf(BloquePolicy::class, $this->policy);
    }

    #[Test]
    public function tiene_metodo_view_any(): void
    {
        $this->assertTrue(method_exists($this->policy, 'viewAny'));
    }
}
