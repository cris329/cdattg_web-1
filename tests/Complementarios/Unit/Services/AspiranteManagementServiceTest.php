<?php

namespace Tests\Complementarios\Unit\Services;

use Tests\TestCase;
use App\Services\AspiranteManagementService;
use App\Repositories\AspiranteComplementarioRepository;
use App\Repositories\ComplementarioOfertadoRepository;
use App\Repositories\PersonaRepository;
use App\Models\ComplementarioOfertado;
use App\Models\Persona;
use App\Models\AspiranteComplementario;
use App\Models\SofiaValidationProgress;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Mockery;

class AspiranteManagementServiceTest extends TestCase
{
    protected AspiranteManagementService $service;
    protected $aspiranteRepositoryMock;
    protected $programaRepositoryMock;
    protected $personaRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->aspiranteRepositoryMock = Mockery::mock(AspiranteComplementarioRepository::class);
        $this->programaRepositoryMock = Mockery::mock(ComplementarioOfertadoRepository::class);
        $this->personaRepositoryMock = Mockery::mock(PersonaRepository::class);
        
        $this->service = new AspiranteManagementService(
            $this->aspiranteRepositoryMock,
            $this->programaRepositoryMock,
            $this->personaRepositoryMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function puede_obtener_programas_para_gestion()
    {
        $programa1 = new ComplementarioOfertado();
        $programa1->setAttribute('id', 1);
        $programa1->setAttribute('nombre', 'Programa 1');
        $programa1->setAttribute('aspirantes_count', 5);
        
        $programa2 = new ComplementarioOfertado();
        $programa2->setAttribute('id', 2);
        $programa2->setAttribute('nombre', 'Programa 2');
        $programa2->setAttribute('aspirantes_count', 3);
        
        $programas = new Collection([$programa1, $programa2]);

        $this->programaRepositoryMock->shouldReceive('getAllWithAspirantesCount')
            ->once()
            ->with(['modalidad.parametro', 'jornada', 'diasFormacion'])
            ->andReturn($programas);

        $resultado = $this->service->obtenerProgramasParaGestion();

        $this->assertCount(2, $resultado);
        $programaEncontrado = $resultado->firstWhere('id', 1);
        $this->assertNotNull($programaEncontrado);
        $this->assertEquals(5, $programaEncontrado->aspirantes_count);
    }

    /** @test */
    public function puede_obtener_aspirantes_por_programa_por_nombre()
    {
        $programa = new ComplementarioOfertado();
        $programa->setAttribute('id', 1);
        $programa->setAttribute('nombre', 'Auxiliar de Cocina');
        
        $aspirantes = new Collection([
            new AspiranteComplementario(['id' => 1, 'complementario_id' => 1]),
            new AspiranteComplementario(['id' => 2, 'complementario_id' => 1]),
            new AspiranteComplementario(['id' => 3, 'complementario_id' => 1]),
        ]);

        $this->programaRepositoryMock->shouldReceive('findByNombre')
            ->once()
            ->with('Auxiliar-de-Cocina')
            ->andReturn($programa);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1, ['modalidad.parametro', 'jornada', 'diasFormacion'])
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('findByPrograma')
            ->once()
            ->with(1, ['persona', 'complementario'])
            ->andReturn($aspirantes);

        // Mock para SofiaValidationProgress usando alias
        $builderMock = Mockery::mock(Builder::class);
        $builderMock->shouldReceive('whereIn')
            ->once()
            ->with('status', ['pending', 'processing'])
            ->andReturnSelf();
        $builderMock->shouldReceive('first')
            ->once()
            ->andReturn(null);
        
        Mockery::mock('alias:' . SofiaValidationProgress::class)
            ->shouldReceive('where')
            ->once()
            ->with('complementario_id', 1)
            ->andReturn($builderMock);

        $data = $this->service->obtenerAspirantesPorPrograma('Auxiliar-de-Cocina');

        $this->assertEquals(1, $data['programa']->id);
        $this->assertCount(3, $data['aspirantes']);
    }

    /** @test */
    public function puede_obtener_aspirantes_por_programa_por_id()
    {
        $programa = new ComplementarioOfertado();
        $programa->setAttribute('id', 1);
        $programa->setAttribute('nombre', 'Programa Test');
        
        $aspirantes = new Collection([
            new AspiranteComplementario(['id' => 1]),
            new AspiranteComplementario(['id' => 2]),
            new AspiranteComplementario(['id' => 3]),
            new AspiranteComplementario(['id' => 4]),
        ]);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1, ['modalidad.parametro', 'jornada', 'diasFormacion'])
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('findByPrograma')
            ->once()
            ->with(1, ['persona', 'complementario'])
            ->andReturn($aspirantes);

        // Mock para SofiaValidationProgress usando alias
        $builderMock = Mockery::mock(Builder::class);
        $builderMock->shouldReceive('whereIn')
            ->once()
            ->with('status', ['pending', 'processing'])
            ->andReturnSelf();
        $builderMock->shouldReceive('first')
            ->once()
            ->andReturn(null);
        
        Mockery::mock('alias:' . SofiaValidationProgress::class)
            ->shouldReceive('where')
            ->once()
            ->with('complementario_id', 1)
            ->andReturn($builderMock);

        $data = $this->service->obtenerAspirantesPorProgramaId(1);

        $this->assertEquals(1, $data['programa']->id);
        $this->assertCount(4, $data['aspirantes']);
    }

    /** @test */
    public function puede_agregar_aspirante_existente()
    {
        $programa = new ComplementarioOfertado();
        $programa->setAttribute('id', 1);
        $programa->setAttribute('nombre', 'Programa Test');
        
        $persona = new Persona();
        $persona->setAttribute('id', 1);
        $persona->setAttribute('numero_documento', '1234567890');
        $persona->setAttribute('primer_nombre', 'Juan');
        $persona->setAttribute('primer_apellido', 'Pérez');
        
        $aspirante = new AspiranteComplementario(['id' => 1, 'persona_id' => 1, 'complementario_id' => 1]);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1)
            ->andReturn($programa);

        $this->personaRepositoryMock->shouldReceive('findByNumeroDocumento')
            ->twice()
            ->with('1234567890')
            ->andReturn($persona);

        $this->aspiranteRepositoryMock->shouldReceive('existeInscripcion')
            ->once()
            ->with(1, 1)
            ->andReturn(false);

        $this->aspiranteRepositoryMock->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['persona_id'] === 1 &&
                       $data['complementario_id'] === 1 &&
                       $data['estado'] === 1;
            }))
            ->andReturn($aspirante);

        $resultado = $this->service->agregarAspirante(1, '1234567890');

        $this->assertTrue($resultado['success']);
        $this->assertStringContainsString('Juan', $resultado['message']);
    }

    /** @test */
    public function no_agrega_aspirante_si_no_existe_persona()
    {
        $programa = new ComplementarioOfertado();
        $programa->setAttribute('id', 1);
        $programa->setAttribute('nombre', 'Programa Test');

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1)
            ->andReturn($programa);

        $this->personaRepositoryMock->shouldReceive('findByNumeroDocumento')
            ->once()
            ->with('9999999999')
            ->andReturn(null);

        $resultado = $this->service->agregarAspirante(1, '9999999999');

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('No se encontró', $resultado['message']);
    }

    /** @test */
    public function no_agrega_aspirante_si_ya_esta_inscrito()
    {
        $programa = new ComplementarioOfertado();
        $programa->setAttribute('id', 1);
        $programa->setAttribute('nombre', 'Programa Test');
        
        $persona = new Persona();
        $persona->setAttribute('id', 1);
        $persona->setAttribute('numero_documento', '1234567890');

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1)
            ->andReturn($programa);

        $this->personaRepositoryMock->shouldReceive('findByNumeroDocumento')
            ->once()
            ->with('1234567890')
            ->andReturn($persona);

        $this->aspiranteRepositoryMock->shouldReceive('existeInscripcion')
            ->once()
            ->with(1, 1)
            ->andReturn(true);

        $resultado = $this->service->agregarAspirante(1, '1234567890');

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('ya se encuentra inscrita', $resultado['message']);
    }

    /** @test */
    public function puede_obtener_estadisticas_programa()
    {
        $programa = new ComplementarioOfertado();
        $programa->setAttribute('id', 1);
        $programa->setAttribute('cupos', 30);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1)
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('countByPrograma')
            ->once()
            ->with(1)
            ->andReturn(8);

        $this->aspiranteRepositoryMock->shouldReceive('countByEstado')
            ->once()
            ->with(1, 1)
            ->andReturn(5);

        $this->aspiranteRepositoryMock->shouldReceive('countByEstado')
            ->once()
            ->with(1, 3)
            ->andReturn(3);

        $estadisticas = $this->service->obtenerEstadisticasPrograma(1);

        $this->assertEquals(8, $estadisticas['total_aspirantes']);
        $this->assertEquals(5, $estadisticas['aspirantes_activos']);
        $this->assertEquals(3, $estadisticas['aspirantes_aceptados']);
        $this->assertEquals(22, $estadisticas['cupos_disponibles']);
    }
}
