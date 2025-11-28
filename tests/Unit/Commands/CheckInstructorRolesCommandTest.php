<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\CheckInstructorRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckInstructorRolesCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function command_existe(): void
    {
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->artisan('instructors:check-roles')
            ->assertExitCode(0);
    }

    #[Test]
    public function tiene_signature_correcto(): void
    {
        $command = new CheckInstructorRoles;

        $this->assertStringContainsString('instructors:check-roles', $command->getSignature());
    }
}
