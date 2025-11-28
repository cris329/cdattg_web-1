<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\FixInstructorRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FixInstructorRolesCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function command_existe(): void
    {
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->artisan('roles:fix-instructors --dry-run')
            ->assertExitCode(0);
    }

    #[Test]
    public function tiene_signature_correcto(): void
    {
        $command = new FixInstructorRoles;

        $this->assertStringContainsString('roles:fix-instructors', $command->getSignature());
    }
}
