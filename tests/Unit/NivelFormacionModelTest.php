<?php

namespace Tests\Unit;

use App\Models\NivelFormacion;
use App\Models\ProgramaFormacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NivelFormacionModelTest extends TestCase
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
    public function tiene_relacion_con_programas_formacion(): void
    {
        $nivel = NivelFormacion::factory()->create();
        ProgramaFormacion::factory()->count(2)->create([
            'nivel_formacion_id' => $nivel->id,
        ]);

        $this->assertCount(2, $nivel->programasFormacion);
    }

    #[Test]
    public function puede_crear_nivel(): void
    {
        $nivel = NivelFormacion::create([
            'nivel_formacion' => 'Tecnólogo',
        ]);

        $this->assertDatabaseHas('niveles_formacion', [
            'id' => $nivel->id,
            'nivel_formacion' => 'Tecnólogo',
        ]);
    }
}

