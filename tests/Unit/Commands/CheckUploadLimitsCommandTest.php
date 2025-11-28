<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\CheckUploadLimitsCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckUploadLimitsCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function command_existe(): void
    {
        $this->artisan('upload:check-limits')
            ->assertExitCode(0);
    }

    #[Test]
    public function tiene_signature_correcto(): void
    {
        $command = new CheckUploadLimitsCommand;

        $this->assertStringContainsString('upload:check-limits', $command->getSignature());
    }
}
