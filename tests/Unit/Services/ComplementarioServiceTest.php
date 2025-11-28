<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ComplementarioService;
use App\Repositories\TemaRepository;
use App\Repositories\ComplementarioOfertadoRepository;
use App\Repositories\AspiranteComplementarioRepository;
use App\Models\ComplementarioOfertado;
use App\Models\AspiranteComplementario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class ComplementarioServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ComplementarioService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new ComplementarioService(
            Mockery::mock(TemaRepository::class),
            new ComplementarioOfertadoRepository(),
            new AspiranteComplementarioRepository()
        );
    }

    /** @test */
    public function puede_obtener_icono_para_programa()
    {
        $icono = $this->service->getIconoForPrograma('Auxiliar de Cocina');
        
        $this->assertEquals('fas fa-utensils', $icono);
    }

    /** @test */
    public function retorna_icono_por_defecto_si_no_existe()
    {
        $icono = $this->service->getIconoForPrograma('Programa Desconocido');
        
        $this->assertEquals('fas fa-graduation-cap', $icono);
    }

    /** @test */
    public function puede_obtener_clase_badge_por_estado()
    {
        $clase0 = $this->service->getBadgeClassForEstado(0);
        $clase1 = $this->service->getBadgeClassForEstado(1);
        $clase2 = $this->service->getBadgeClassForEstado(2);
        
        $this->assertEquals('bg-secondary', $clase0);
        $this->assertEquals('bg-success', $clase1);
        $this->assertEquals('bg-warning', $clase2);
    }

    /** @test */
    public function puede_obtener_label_estado()
    {
        $label0 = $this->service->getEstadoLabel(0);
        $label1 = $this->service->getEstadoLabel(1);
        $label2 = $this->service->getEstadoLabel(2);
        
        $this->assertEquals('Sin Oferta', $label0);
        $this->assertEquals('Con Oferta', $label1);
        $this->assertEquals('Cupos Llenos', $label2);
    }

    /** @test */
    public function puede_enriquecer_programa()
    {
        $programa = ComplementarioOfertado::factory()->create([
            'nombre' => 'Auxiliar de Cocina',
            'estado' => 1,
        ]);

        $enriquecido = $this->service->enriquecerPrograma($programa);

        $this->assertEquals('fas fa-utensils', $enriquecido->icono);
        $this->assertEquals('bg-success', $enriquecido->badge_class);
        $this->assertEquals('Con Oferta', $enriquecido->estado_label);
    }

    /** @test */
    public function puede_enriquecer_coleccion_programas()
    {
        ComplementarioOfertado::factory()->count(3)->create();

        $programas = $this->service->obtenerProgramas();
        $enriquecidos = $this->service->enriquecerProgramas($programas);

        $this->assertCount(3, $enriquecidos);
        $enriquecidos->each(function ($programa) {
            $this->assertNotNull($programa->icono);
            $this->assertNotNull($programa->badge_class);
            $this->assertNotNull($programa->estado_label);
        });
    }

    /** @test */
    public function puede_obtener_programas_con_filtro_estado()
    {
        ComplementarioOfertado::factory()->count(3)->conOferta()->create();
        ComplementarioOfertado::factory()->count(2)->sinOferta()->create();

        $activos = $this->service->obtenerProgramas([], 1);
        $sinOferta = $this->service->obtenerProgramas([], 0);

        $this->assertCount(3, $activos);
        $this->assertCount(2, $sinOferta);
    }

    /** @test */
    public function puede_verificar_inscripcion_existente()
    {
        $persona = \App\Models\Persona::factory()->create();
        $programa = ComplementarioOfertado::factory()->create();
        AspiranteComplementario::factory()->paraPersona($persona)->paraPrograma($programa)->create();

        $existe = $this->service->verificarInscripcionExistente($persona->id, $programa->id);
        $noExiste = $this->service->verificarInscripcionExistente($persona->id, ComplementarioOfertado::factory()->create()->id);

        $this->assertTrue($existe);
        $this->assertFalse($noExiste);
    }

    /** @test */
    public function puede_crear_aspirante()
    {
        $persona = \App\Models\Persona::factory()->create();
        $programa = ComplementarioOfertado::factory()->create();

        $aspirante = $this->service->crearAspirante($persona->id, $programa->id, 'Observaciones test');

        $this->assertDatabaseHas('aspirantes_complementarios', [
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
            'observaciones' => 'Observaciones test',
            'estado' => 1,
        ]);
    }

    /** @test */
    public function puede_obtener_estadisticas_programa()
    {
        $programa = ComplementarioOfertado::factory()->create(['cupos' => 30]);
        AspiranteComplementario::factory()->count(5)->enProceso()->paraPrograma($programa)->create();
        AspiranteComplementario::factory()->count(3)->admitido()->paraPrograma($programa)->create();

        $estadisticas = $this->service->obtenerEstadisticasPrograma($programa->id);

        $this->assertEquals(8, $estadisticas['total_aspirantes']);
        $this->assertEquals(5, $estadisticas['aspirantes_activos']);
        $this->assertEquals(3, $estadisticas['aspirantes_aceptados']);
        $this->assertEquals(22, $estadisticas['cupos_disponibles']);
    }
}
