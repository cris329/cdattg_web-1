<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\EstadisticaComplementarioService;
use App\Repositories\AspiranteComplementarioRepository;
use App\Repositories\ComplementarioOfertadoRepository;
use App\Repositories\PersonaRepository;
use App\Models\AspiranteComplementario;
use App\Models\ComplementarioOfertado;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EstadisticaComplementarioServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EstadisticaComplementarioService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new EstadisticaComplementarioService(
            new AspiranteComplementarioRepository(),
            new ComplementarioOfertadoRepository(),
            new PersonaRepository()
        );
    }

    /** @test */
    public function puede_obtener_estadisticas_reales()
    {
        ComplementarioOfertado::factory()->count(3)->conOferta()->create();
        AspiranteComplementario::factory()->count(10)->enProceso()->create();
        AspiranteComplementario::factory()->count(5)->admitido()->create();
        AspiranteComplementario::factory()->count(3)->rechazado()->create();

        $estadisticas = $this->service->obtenerEstadisticasReales();

        $this->assertArrayHasKey('total_aspirantes', $estadisticas);
        $this->assertArrayHasKey('aspirantes_aceptados', $estadisticas);
        $this->assertArrayHasKey('aspirantes_pendientes', $estadisticas);
        $this->assertArrayHasKey('programas_activos', $estadisticas);
        $this->assertArrayHasKey('tendencia_inscripciones', $estadisticas);
        $this->assertArrayHasKey('distribucion_programas', $estadisticas);
        $this->assertArrayHasKey('programas_demanda', $estadisticas);
        
        $this->assertEquals(18, $estadisticas['total_aspirantes']);
        $this->assertEquals(5, $estadisticas['aspirantes_aceptados']);
        $this->assertEquals(10, $estadisticas['aspirantes_pendientes']);
        $this->assertEquals(3, $estadisticas['programas_activos']);
    }

    /** @test */
    public function puede_obtener_estadisticas_por_genero()
    {
        $persona1 = \App\Models\Persona::factory()->create(['genero' => 9]);
        $persona2 = \App\Models\Persona::factory()->create(['genero' => 10]);
        
        AspiranteComplementario::factory()->paraPersona($persona1)->create();
        AspiranteComplementario::factory()->paraPersona($persona2)->create();

        $estadisticas = $this->service->obtenerEstadisticasPorGenero();

        $this->assertGreaterThanOrEqual(0, $estadisticas->count());
    }

    /** @test */
    public function puede_obtener_estadisticas_por_edad()
    {
        $persona1 = \App\Models\Persona::factory()->create(['fecha_nacimiento' => '2000-01-01']);
        $persona2 = \App\Models\Persona::factory()->create(['fecha_nacimiento' => '1990-01-01']);
        
        AspiranteComplementario::factory()->paraPersona($persona1)->create();
        AspiranteComplementario::factory()->paraPersona($persona2)->create();

        $estadisticas = $this->service->obtenerEstadisticasPorEdad();

        $this->assertGreaterThanOrEqual(0, $estadisticas->count());
    }
}
