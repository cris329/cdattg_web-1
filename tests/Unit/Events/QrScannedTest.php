<?php

namespace Tests\Unit\Events;

use App\Events\QrScanned;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QrScannedTest extends TestCase
{
    #[Test]
    public function puede_crear_evento(): void
    {
        $qrData = ['tipo' => 'APRENDIZ', 'id' => 1];

        $event = new QrScanned($qrData);

        $this->assertInstanceOf(QrScanned::class, $event);
        $this->assertEquals($qrData, $event->qrData);
    }

    #[Test]
    public function implementa_should_broadcast(): void
    {
        $event = new QrScanned([]);

        $this->assertInstanceOf(\Illuminate\Contracts\Broadcasting\ShouldBroadcast::class, $event);
    }
}
