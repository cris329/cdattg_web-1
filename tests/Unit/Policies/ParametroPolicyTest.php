<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\ParametroPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ParametroPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected ParametroPolicy $policy;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->policy = new ParametroPolicy;
        $this->user = User::factory()->create();
    }

    #[Test]
    public function policy_existe(): void
    {
        $this->assertInstanceOf(ParametroPolicy::class, $this->policy);
    }
}
