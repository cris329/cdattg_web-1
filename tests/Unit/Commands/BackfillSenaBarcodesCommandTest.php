<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\BackfillSenaBarcodes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BackfillSenaBarcodesCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function command_existe(): void
    {
        $this->artisan('productos:backfill-sena-barcodes --dry-run')
            ->assertExitCode(0);
    }

    #[Test]
    public function tiene_signature_correcto(): void
    {
        $command = new BackfillSenaBarcodes;

        $this->assertStringContainsString('productos:backfill-sena-barcodes', $command->getSignature());
    }
}
