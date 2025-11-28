<?php

namespace Tests\Unit;

use App\Models\InstructorFichaCaracterizacion;
use App\Models\InstructorFichaDias;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstructorFichaDiasModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);
    }

    #[Test]
    public function tiene_relacion_con_instructor_ficha(): void
    {
        $instructorFicha = InstructorFichaCaracterizacion::factory()->create();
        $instructorFichaDia = InstructorFichaDias::factory()->create([
            'instructor_ficha_id' => $instructorFicha->id,
        ]);

        $this->assertInstanceOf(InstructorFichaCaracterizacion::class, $instructorFichaDia->instructorFicha);
        $this->assertEquals($instructorFicha->id, $instructorFichaDia->instructorFicha->id);
    }

    #[Test]
    public function tiene_relacion_con_dia(): void
    {
        $parametro = \App\Models\Parametro::first();
        $instructorFichaDia = InstructorFichaDias::factory()->create([
            'dia_id' => $parametro->id,
        ]);

        $this->assertInstanceOf(\App\Models\Parametro::class, $instructorFichaDia->dia);
    }
}

