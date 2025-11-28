<?php

namespace Tests\Unit\Listeners;

use App\Events\FichaAsignadaAInstructor;
use App\Listeners\EnviarNotificacionFichaAsignada;
use App\Models\FichaCaracterizacion;
use App\Models\Instructor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EnviarNotificacionFichaAsignadaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
        ]);
    }

    #[Test]
    public function puede_manejar_evento(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        $instructor = Instructor::factory()->create();
        $event = new FichaAsignadaAInstructor($instructor, $ficha);

        $listener = app(EnviarNotificacionFichaAsignada::class);

        // No debe lanzar excepción
        $listener->handle($event);

        $this->assertTrue(true);
    }
}
