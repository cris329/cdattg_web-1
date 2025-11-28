<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\AspiranteManagementService;
use App\Repositories\AspiranteComplementarioRepository;
use App\Repositories\ComplementarioOfertadoRepository;
use App\Repositories\PersonaRepository;
use App\Models\ComplementarioOfertado;
use App\Models\Persona;
use App\Models\AspiranteComplementario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class AspiranteManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AspiranteManagementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new AspiranteManagementService(
            new AspiranteComplementarioRepository(),
            new ComplementarioOfertadoRepository(),
            new PersonaRepository()
        );
    }

    /** @test */
    public function puede_obtener_programas_para_gestion()
    {
        ComplementarioOfertado::factory()->count(3)->create();
        $programa = ComplementarioOfertado::factory()->create();
        AspiranteComplementario::factory()->count(5)->paraPrograma($programa)->create();

        $programas = $this->service->obtenerProgramasParaGestion();

        $this->assertGreaterThanOrEqual(4, $programas->count());
        $programaData = $programas->firstWhere('id', $programa->id);
        $this->assertNotNull($programaData);
        $this->assertEquals(5, $programaData->aspirantes_count);
    }

    /** @test */
    public function puede_obtener_aspirantes_por_programa_por_nombre()
    {
        $programa = ComplementarioOfertado::factory()->create(['nombre' => 'Auxiliar de Cocina']);
        AspiranteComplementario::factory()->count(3)->paraPrograma($programa)->create();

        $data = $this->service->obtenerAspirantesPorPrograma('Auxiliar-de-Cocina');

        $this->assertEquals($programa->id, $data['programa']->id);
        $this->assertCount(3, $data['aspirantes']);
    }

    /** @test */
    public function puede_obtener_aspirantes_por_programa_por_id()
    {
        $programa = ComplementarioOfertado::factory()->create();
        AspiranteComplementario::factory()->count(4)->paraPrograma($programa)->create();

        $data = $this->service->obtenerAspirantesPorProgramaId($programa->id);

        $this->assertEquals($programa->id, $data['programa']->id);
        $this->assertCount(4, $data['aspirantes']);
    }

    /** @test */
    public function puede_agregar_aspirante_existente()
    {
        $programa = ComplementarioOfertado::factory()->create();
        $persona = Persona::factory()->create(['numero_documento' => '1234567890']);

        $resultado = $this->service->agregarAspirante($programa->id, '1234567890');

        $this->assertTrue($resultado['success']);
        $this->assertDatabaseHas('aspirantes_complementarios', [
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
        ]);
    }

    /** @test */
    public function no_agrega_aspirante_si_no_existe_persona()
    {
        $programa = ComplementarioOfertado::factory()->create();

        $resultado = $this->service->agregarAspirante($programa->id, '9999999999');

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('No se encontró', $resultado['message']);
    }

    /** @test */
    public function no_agrega_aspirante_si_ya_esta_inscrito()
    {
        $programa = ComplementarioOfertado::factory()->create();
        $persona = Persona::factory()->create(['numero_documento' => '1234567890']);
        AspiranteComplementario::factory()->paraPersona($persona)->paraPrograma($programa)->create();

        $resultado = $this->service->agregarAspirante($programa->id, '1234567890');

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('ya se encuentra inscrita', $resultado['message']);
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
