<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\CleanDuplicateRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CleanDuplicateRolesCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function command_existe(): void
    {
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->artisan('roles:cleanup --dry-run')
            ->assertExitCode(0);
    }

    #[Test]
    public function tiene_signature_correcto(): void
    {
        $command = new CleanDuplicateRoles;

        $this->assertStringContainsString('roles:cleanup', $command->getSignature());
    }
}
