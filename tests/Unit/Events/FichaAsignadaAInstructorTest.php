<?php

namespace Tests\Unit\Events;

use App\Events\FichaAsignadaAInstructor;
use App\Models\FichaCaracterizacion;
use App\Models\Instructor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FichaAsignadaAInstructorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function puede_crear_evento(): void
    {
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
        ]);

        $ficha = FichaCaracterizacion::factory()->create();
        $instructor = Instructor::factory()->create();

        $event = new FichaAsignadaAInstructor($ficha, $instructor);

        $this->assertInstanceOf(FichaAsignadaAInstructor::class, $event);
        $this->assertEquals($ficha->id, $event->ficha->id);
        $this->assertEquals($instructor->id, $event->instructor->id);
    }
}
