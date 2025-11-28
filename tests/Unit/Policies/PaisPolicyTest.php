<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\PaisPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaisPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected PaisPolicy $policy;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->policy = new PaisPolicy;
        $this->user = User::factory()->create();
    }

    #[Test]
    public function policy_existe(): void
    {
        $this->assertInstanceOf(PaisPolicy::class, $this->policy);
    }
}
