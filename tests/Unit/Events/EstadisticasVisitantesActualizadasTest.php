<?php

namespace Tests\Unit\Events;

use App\Events\EstadisticasVisitantesActualizadas;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EstadisticasVisitantesActualizadasTest extends TestCase
{
    #[Test]
    public function puede_crear_evento(): void
    {
        $estadisticas = ['total' => 10, 'activos' => 5];

        $event = new EstadisticasVisitantesActualizadas($estadisticas);

        $this->assertInstanceOf(EstadisticasVisitantesActualizadas::class, $event);
        $this->assertEquals($estadisticas, $event->estadisticas);
    }

    #[Test]
    public function implementa_should_broadcast(): void
    {
        $event = new EstadisticasVisitantesActualizadas([]);

        $this->assertInstanceOf(\Illuminate\Contracts\Broadcasting\ShouldBroadcast::class, $event);
    }
}
