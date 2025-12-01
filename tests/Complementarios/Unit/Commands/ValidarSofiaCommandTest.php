<?php

namespace Tests\Complementarios\Unit\Commands;

use App\Console\Commands\Complementarios\ValidarSofiaCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidarSofiaCommandTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function command_existe(): void
    {
        $this->artisan('sofia:validar', ['complementario_id' => 1])
            ->assertExitCode(0);
    }

    #[Test]
    public function tiene_signature_correcto(): void
    {
        // Verificar que el comando tiene el nombre correcto usando getName()
        // getName() devuelve el nombre base del comando sin argumentos
        $command = new ValidarSofiaCommand;
        
        $this->assertEquals('sofia:validar', $command->getName());
    }
}
