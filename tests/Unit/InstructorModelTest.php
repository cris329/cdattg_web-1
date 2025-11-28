<?php

namespace Tests\Unit;

use App\Models\Instructor;
use App\Models\Persona;
use App\Models\Regional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstructorModelTest extends TestCase
{
    use RefreshDatabase;

    protected Instructor $instructor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
        ]);

        $this->instructor = Instructor::factory()->create();
    }

    #[Test]
    public function tiene_relacion_con_persona(): void
    {
        $this->assertInstanceOf(Persona::class, $this->instructor->persona);
        $this->assertNotNull($this->instructor->persona);
    }

    #[Test]
    public function tiene_relacion_con_regional(): void
    {
        $regional = Regional::first();
        if ($regional) {
            $instructor = Instructor::factory()->create(['regional_id' => $regional->id]);

            $this->assertInstanceOf(Regional::class, $instructor->regional);
            $this->assertEquals($regional->id, $instructor->regional->id);
        }
    }

    #[Test]
    public function scope_activos_funciona(): void
    {
        Instructor::factory()->create(['status' => true]);
        Instructor::factory()->create(['status' => false]);

        $activos = Instructor::activos()->get();

        $this->assertGreaterThan(0, $activos->count());
        $activos->each(function ($instructor) {
            $this->assertTrue($instructor->status);
        });
    }

    #[Test]
    public function scope_inactivos_funciona(): void
    {
        Instructor::factory()->create(['status' => false]);

        $inactivos = Instructor::inactivos()->get();

        $inactivos->each(function ($instructor) {
            $this->assertFalse($instructor->status);
        });
    }

    #[Test]
    public function castea_especialidades_como_array(): void
    {
        $instructor = Instructor::factory()->create([
            'especialidades' => ['PHP', 'Laravel'],
        ]);

        $this->assertIsArray($instructor->especialidades);
        $this->assertContains('PHP', $instructor->especialidades);
    }

    #[Test]
    public function actualiza_cache_al_crear(): void
    {
        $persona = Persona::factory()->create();
        $instructor = Instructor::factory()->create(['persona_id' => $persona->id]);

        $this->assertNotNull($instructor->nombre_completo_cache);
        $this->assertNotNull($instructor->numero_documento_cache);
    }
}
