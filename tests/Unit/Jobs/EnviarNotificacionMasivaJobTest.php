<?php

namespace Tests\Unit\Jobs;

use App\Jobs\EnviarNotificacionMasivaJob;
use App\Models\Aprendiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EnviarNotificacionMasivaJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);
    }

    #[Test]
    public function puede_crear_job(): void
    {
        $aprendices = Aprendiz::factory()->count(3)->create();
        $job = new EnviarNotificacionMasivaJob($aprendices, 'Mensaje de prueba', 'general');

        $this->assertInstanceOf(EnviarNotificacionMasivaJob::class, $job);
        $this->assertCount(3, $job->aprendices);
        $this->assertEquals('Mensaje de prueba', $job->mensaje);
        $this->assertEquals('general', $job->tipo);
    }

    #[Test]
    public function tiene_configuracion_de_reintentos(): void
    {
        $aprendices = Aprendiz::factory()->count(1)->create();
        $job = new EnviarNotificacionMasivaJob($aprendices, 'Test');

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(600, $job->timeout);
    }
}
