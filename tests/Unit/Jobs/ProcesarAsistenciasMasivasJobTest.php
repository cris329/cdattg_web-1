<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcesarAsistenciasMasivasJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProcesarAsistenciasMasivasJobTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function puede_crear_job(): void
    {
        $asistencias = [
            ['aprendiz_id' => 1, 'fecha' => '2024-01-01'],
        ];

        $job = new ProcesarAsistenciasMasivasJob($asistencias, 1);

        $this->assertInstanceOf(ProcesarAsistenciasMasivasJob::class, $job);
        $this->assertCount(1, $job->asistencias);
        $this->assertEquals(1, $job->caracterizacionId);
    }

    #[Test]
    public function tiene_configuracion_de_reintentos(): void
    {
        $job = new ProcesarAsistenciasMasivasJob([], 1);

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(600, $job->timeout);
    }
}
