<?php

namespace Tests\Unit;

use App\Models\ProgramaFormacion;
use App\Models\TipoPrograma;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TipoProgramaModelTest extends TestCase
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
        $tipo = TipoPrograma::factory()->create();
        ProgramaFormacion::factory()->count(2)->create([
            'tipo_programa_id' => $tipo->id,
        ]);

        $this->assertCount(2, $tipo->programasFormacion);
    }
}

