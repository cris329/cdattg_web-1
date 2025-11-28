<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\CacheWarmupCommand;
use App\Core\Services\CacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CacheWarmupCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function command_existe(): void
    {
        $this->artisan('cache:warmup')
            ->assertExitCode(0);
    }

    #[Test]
    public function tiene_signature_correcto(): void
    {
        $command = new CacheWarmupCommand(app(CacheService::class));

        $this->assertStringContainsString('cache:warmup', $command->getSignature());
    }
}
