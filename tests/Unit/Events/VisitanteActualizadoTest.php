<?php

namespace Tests\Unit\Events;

use App\Events\VisitanteActualizado;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VisitanteActualizadoTest extends TestCase
{
    #[Test]
    public function puede_crear_evento(): void
    {
        $visitante = ['id' => 1, 'nombre' => 'Juan'];

        $event = new VisitanteActualizado($visitante, 'entrada');

        $this->assertInstanceOf(VisitanteActualizado::class, $event);
        $this->assertEquals($visitante, $event->visitante);
        $this->assertEquals('entrada', $event->tipo);
    }

    #[Test]
    public function implementa_should_broadcast(): void
    {
        $event = new VisitanteActualizado([], 'salida');

        $this->assertInstanceOf(\Illuminate\Contracts\Broadcasting\ShouldBroadcast::class, $event);
    }
}
