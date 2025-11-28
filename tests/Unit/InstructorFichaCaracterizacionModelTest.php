<?php

namespace Tests\Unit;

use App\Models\FichaCaracterizacion;
use App\Models\Instructor;
use App\Models\InstructorFichaCaracterizacion;
use App\Models\InstructorFichaDias;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstructorFichaCaracterizacionModelTest extends TestCase
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
        ]);
    }

    #[Test]
    public function tiene_relacion_con_instructor(): void
    {
        $instructor = Instructor::factory()->create();
        $instructorFicha = InstructorFichaCaracterizacion::factory()->create([
            'instructor_id' => $instructor->id,
        ]);

        $this->assertInstanceOf(Instructor::class, $instructorFicha->instructor);
        $this->assertEquals($instructor->id, $instructorFicha->instructor->id);
    }

    #[Test]
    public function tiene_relacion_con_ficha(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        $instructorFicha = InstructorFichaCaracterizacion::factory()->create([
            'ficha_id' => $ficha->id,
        ]);

        $this->assertInstanceOf(FichaCaracterizacion::class, $instructorFicha->ficha);
        $this->assertEquals($ficha->id, $instructorFicha->ficha->id);
    }

    #[Test]
    public function tiene_relacion_con_instructor_ficha_dias(): void
    {
        $instructorFicha = InstructorFichaCaracterizacion::factory()->create();
        InstructorFichaDias::factory()->count(2)->create([
            'instructor_ficha_id' => $instructorFicha->id,
        ]);

        $this->assertCount(2, $instructorFicha->instructorFichaDias);
    }
}

