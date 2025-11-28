<?php

namespace Tests\Unit\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckUserPermissionsCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function command_existe(): void
    {
        $this->artisan('check:user-permissions')
            ->assertExitCode(0);
    }
}
