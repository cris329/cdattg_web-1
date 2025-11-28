<?php

namespace Tests\Unit\Events;

use App\Events\NuevaAsistenciaRegistrada;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NuevaAsistenciaRegistradaTest extends TestCase
{
    #[Test]
    public function puede_crear_evento(): void
    {
        $data = [
            'id' => 1,
            'aprendiz' => 'Juan Pérez',
            'estado' => 'entrada',
            'timestamp' => now()->toISOString(),
        ];

        $event = new NuevaAsistenciaRegistrada($data);

        $this->assertInstanceOf(NuevaAsistenciaRegistrada::class, $event);
        $this->assertEquals($data, $event->asistenciaData);
    }

    #[Test]
    public function implementa_should_broadcast(): void
    {
        $data = ['id' => 1];
        $event = new NuevaAsistenciaRegistrada($data);

        $this->assertInstanceOf(\Illuminate\Contracts\Broadcasting\ShouldBroadcast::class, $event);
    }

    #[Test]
    public function define_canal_broadcast(): void
    {
        $data = ['id' => 1];
        $event = new NuevaAsistenciaRegistrada($data);

        $channels = $event->broadcastOn();

        $this->assertIsArray($channels);
        $this->assertNotEmpty($channels);
    }
}
