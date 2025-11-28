<?php

namespace Tests\Unit\Observers;

use App\Models\AsistenciaAprendiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AsistenciaAprendizObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        Event::fake();
    }

    #[Test]
    public function dispara_evento_al_crear_asistencia(): void
    {
        $instructorFicha = \App\Models\InstructorFichaCaracterizacion::factory()->create();
        $aprendizFicha = \App\Models\AprendizFicha::factory()->create();

        AsistenciaAprendiz::factory()->create([
            'instructor_ficha_id' => $instructorFicha->id,
            'aprendiz_ficha_id' => $aprendizFicha->id,
        ]);

        Event::assertDispatched(\App\Events\AsistenciaCreated::class);
    }
}
