<?php

namespace Tests\Complementarios\Unit\Commands;

use App\Console\Commands\ValidarSofiaCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidarSofiaCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function command_existe(): void
    {
        $this->artisan('sofia:validar', ['complementario_id' => 1])
            ->assertExitCode(0);
    }

    #[Test]
    public function tiene_signature_correcto(): void
    {
        $command = new ValidarSofiaCommand;

        $this->assertStringContainsString('sofia:validar', $command->getSignature());
    }
}
