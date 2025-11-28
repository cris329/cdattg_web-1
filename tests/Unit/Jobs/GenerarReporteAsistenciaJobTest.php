<?php

namespace Tests\Unit\Jobs;

use App\Jobs\GenerarReporteAsistenciaJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GenerarReporteAsistenciaJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);
    }

    #[Test]
    public function puede_crear_job(): void
    {
        $user = User::factory()->create();

        $job = new GenerarReporteAsistenciaJob(
            1,
            '2024-01-01',
            '2024-01-31',
            'pdf',
            $user
        );

        $this->assertInstanceOf(GenerarReporteAsistenciaJob::class, $job);
        $this->assertEquals(1, $job->fichaId);
        $this->assertEquals('2024-01-01', $job->fechaInicio);
        $this->assertEquals('2024-01-31', $job->fechaFin);
        $this->assertEquals('pdf', $job->formato);
    }

    #[Test]
    public function tiene_configuracion_de_reintentos(): void
    {
        $user = User::factory()->create();
        $job = new GenerarReporteAsistenciaJob(1, '2024-01-01', '2024-01-31', 'pdf', $user);

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(300, $job->timeout);
    }
}
