<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\VerifyUserEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VerifyUserEmailCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function command_existe(): void
    {
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->artisan('user:verify-email', ['email' => 'test@example.com'])
            ->assertExitCode(0);
    }

    #[Test]
    public function tiene_signature_correcto(): void
    {
        $command = new VerifyUserEmail;

        $this->assertStringContainsString('user:verify-email', $command->getSignature());
    }
}
