<?php

namespace Tests\Unit\Events;

use App\Events\AsistenciaCreated;
use App\Models\AsistenciaAprendiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AsistenciaCreatedTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function puede_crear_evento(): void
    {
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $instructorFicha = \App\Models\InstructorFichaCaracterizacion::factory()->create();
        $aprendizFicha = \App\Models\AprendizFicha::factory()->create();

        $asistencia = AsistenciaAprendiz::factory()->create([
            'instructor_ficha_id' => $instructorFicha->id,
            'aprendiz_ficha_id' => $aprendizFicha->id,
        ]);

        $event = new AsistenciaCreated($asistencia);

        $this->assertInstanceOf(AsistenciaCreated::class, $event);
        $this->assertEquals($asistencia->id, $event->asistencia->id);
    }
}
