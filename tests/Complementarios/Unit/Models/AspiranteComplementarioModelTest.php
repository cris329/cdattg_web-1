<?php

namespace Tests\Complementarios\Unit\Models;

use App\Models\Complementarios\AspiranteComplementario;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AspiranteComplementarioModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\TemaSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
        ]);
    }

    #[Test]
    public function tiene_relacion_con_persona(): void
    {
        $persona = Persona::factory()->create();
        $aspirante = AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
        ]);

        $this->assertInstanceOf(Persona::class, $aspirante->persona);
        $this->assertEquals($persona->id, $aspirante->persona->id);
    }

    #[Test]
    public function tiene_relacion_con_complementario(): void
    {
        $complementario = ComplementarioOfertado::factory()->create();
        $aspirante = AspiranteComplementario::factory()->create([
            'complementario_id' => $complementario->id,
        ]);

        $this->assertInstanceOf(ComplementarioOfertado::class, $aspirante->complementario);
        $this->assertEquals($complementario->id, $aspirante->complementario->id);
    }

    #[Test]
    public function obtiene_estado_label(): void
    {
        $aspirante = AspiranteComplementario::factory()->create(['estado' => 1]);

        $this->assertEquals('En proceso', $aspirante->estado_label);
    }
}

