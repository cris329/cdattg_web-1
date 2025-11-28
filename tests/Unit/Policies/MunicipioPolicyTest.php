<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\MunicipioPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MunicipioPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected MunicipioPolicy $policy;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->policy = new MunicipioPolicy;
        $this->user = User::factory()->create();
    }

    #[Test]
    public function policy_existe(): void
    {
        $this->assertInstanceOf(MunicipioPolicy::class, $this->policy);
    }
}
