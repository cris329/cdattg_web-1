<?php

namespace Tests\Unit;

use App\Models\Aprendiz;
use App\Models\FichaCaracterizacion;
use App\Models\Instructor;
use App\Models\ProgramaFormacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FichaCaracterizacionModelTest extends TestCase
{
    use RefreshDatabase;

    protected FichaCaracterizacion $ficha;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);

        $this->ficha = FichaCaracterizacion::factory()->create();
    }

    #[Test]
    public function tiene_relacion_con_programa_formacion(): void
    {
        $programa = ProgramaFormacion::first();

        $this->assertInstanceOf(ProgramaFormacion::class, $this->ficha->programaFormacion);
        if ($programa) {
            $this->assertNotNull($this->ficha->programaFormacion);
        }
    }

    #[Test]
    public function tiene_relacion_con_instructor(): void
    {
        $instructor = Instructor::factory()->create();
        $ficha = FichaCaracterizacion::factory()->create(['instructor_id' => $instructor->id]);

        $this->assertInstanceOf(Instructor::class, $ficha->instructor);
        $this->assertEquals($instructor->id, $ficha->instructor->id);
    }

    #[Test]
    public function tiene_relacion_con_aprendices(): void
    {
        $aprendiz = Aprendiz::factory()->create([
            'ficha_caracterizacion_id' => $this->ficha->id,
        ]);

        $this->assertTrue($this->ficha->aprendices()->exists());
    }

    #[Test]
    public function puede_contar_aprendices(): void
    {
        Aprendiz::factory()->count(5)->create([
            'ficha_caracterizacion_id' => $this->ficha->id,
        ]);

        $this->assertGreaterThanOrEqual(5, $this->ficha->contarAprendices());
    }

    #[Test]
    public function castea_fechas_correctamente(): void
    {
        $ficha = FichaCaracterizacion::factory()->create([
            'fecha_inicio' => '2024-01-01',
            'fecha_fin' => '2024-12-31',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $ficha->fecha_inicio);
        $this->assertInstanceOf(\Carbon\Carbon::class, $ficha->fecha_fin);
    }

    #[Test]
    public function castea_status_booleano(): void
    {
        $ficha = FichaCaracterizacion::factory()->create(['status' => true]);

        $this->assertIsBool($ficha->status);
        $this->assertTrue($ficha->status);
    }
}
