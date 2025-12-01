<?php

namespace Tests\Complementarios\Unit\Models;

use App\Models\Ambiente;
use App\Models\Complementarios\AspiranteComplementario;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\JornadaFormacion;
use App\Models\Parametro;
use App\Models\ParametroTema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Complementarios\Concerns\SeedsComplementariosDatabase;

class ComplementarioOfertadoModelTest extends TestCase
{
    use RefreshDatabase;
    use SeedsComplementariosDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedComplementariosDatabaseIfNeeded();
    }

    #[Test]
    public function tiene_relacion_con_modalidad(): void
    {
        // Los parámetros de modalidad son 18, 19, 20 (PRESENCIAL, VIRTUAL, MIXTA)
        $parametro = Parametro::whereIn('id', [18, 19, 20])->first();
        if (!$parametro) {
            // Crear un parámetro de modalidad si no existe
            $parametro = Parametro::firstOrCreate(
                ['id' => 18],
                ['name' => 'PRESENCIAL', 'status' => 1]
            );
        }
        
        $tema = \App\Models\Tema::firstOrCreate(
            ['id' => 5],
            ['name' => 'MODALIDADES DE FORMACION', 'status' => 1]
        );
        
        $modalidad = ParametroTema::firstOrCreate([
            'tema_id' => $tema->id,
            'parametro_id' => $parametro->id,
        ]);

        $complementario = ComplementarioOfertado::factory()->create([
            'modalidad_id' => $modalidad->id,
        ]);

        $this->assertInstanceOf(ParametroTema::class, $complementario->modalidad);
    }

    #[Test]
    public function tiene_relacion_con_jornada(): void
    {
        $jornada = JornadaFormacion::factory()->create();
        $complementario = ComplementarioOfertado::factory()->create([
            'jornada_id' => $jornada->id,
        ]);

        $this->assertInstanceOf(JornadaFormacion::class, $complementario->jornada);
        $this->assertEquals($jornada->id, $complementario->jornada->id);
    }

    #[Test]
    public function tiene_relacion_con_ambiente(): void
    {
        $ambiente = Ambiente::factory()->create();
        $complementario = ComplementarioOfertado::factory()->create([
            'ambiente_id' => $ambiente->id,
        ]);

        $this->assertInstanceOf(Ambiente::class, $complementario->ambiente);
        $this->assertEquals($ambiente->id, $complementario->ambiente->id);
    }

    #[Test]
    public function tiene_relacion_muchos_a_muchos_con_dias_formacion(): void
    {
        $complementario = ComplementarioOfertado::factory()->create();
        $parametro = Parametro::first();

        $complementario->diasFormacion()->attach($parametro->id, [
            'hora_inicio' => '08:00:00',
            'hora_fin' => '18:00:00',
        ]);

        $this->assertGreaterThanOrEqual(1, $complementario->diasFormacion->count());
    }

    #[Test]
    public function tiene_relacion_con_aspirantes(): void
    {
        $complementario = ComplementarioOfertado::factory()->create();
        AspiranteComplementario::factory()->count(2)->create([
            'complementario_id' => $complementario->id,
        ]);

        $this->assertCount(2, $complementario->aspirantes);
    }

    #[Test]
    public function obtiene_estado_label(): void
    {
        $complementario = ComplementarioOfertado::factory()->create(['estado' => 1]);

        $this->assertEquals('Con Oferta', $complementario->estado_label);
    }

    #[Test]
    public function obtiene_badge_class(): void
    {
        $complementario = ComplementarioOfertado::factory()->create(['estado' => 1]);

        $this->assertEquals('bg-warning', $complementario->badge_class);
    }

    #[Test]
    public function obtiene_icono_segun_nombre(): void
    {
        $complementario = ComplementarioOfertado::factory()->create(['nombre' => 'Auxiliar de Cocina']);

        $this->assertEquals('fas fa-utensils', $complementario->icono);
    }
}

