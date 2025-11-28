<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\TestEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TestEmailCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function command_existe(): void
    {
        Mail::fake();

        $this->artisan('test:email', ['email' => 'test@example.com'])
            ->assertExitCode(0);
    }

    #[Test]
    public function tiene_signature_correcto(): void
    {
        $command = new TestEmail;

        $this->assertStringContainsString('test:email', $command->getSignature());
    }
}
