<?php

namespace Tests\Complementarios\Unit\Services;

use App\Models\Complementarios\AspiranteComplementario;
use App\Repositories\Complementarios\AspiranteComplementarioRepository;
use App\Repositories\PersonaRepository;
use App\Services\Complementarios\AspiranteComplementarioService;
use App\Services\Complementarios\AspiranteDocumentoService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AspiranteComplementarioServiceTest extends TestCase
{
    protected AspiranteComplementarioService $service;
    protected $documentoServiceMock;
    protected $aspiranteRepositoryMock;
    protected $personaRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->documentoServiceMock = Mockery::mock(AspiranteDocumentoService::class);
        $this->aspiranteRepositoryMock = Mockery::mock(AspiranteComplementarioRepository::class);
        $this->personaRepositoryMock = Mockery::mock(PersonaRepository::class);

        $this->service = new AspiranteComplementarioService(
            $this->documentoServiceMock,
            $this->aspiranteRepositoryMock,
            $this->personaRepositoryMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function puede_instanciar_servicio(): void
    {
        $this->assertInstanceOf(AspiranteComplementarioService::class, $this->service);
    }

    #[Test]
    public function obtiene_aspirantes_con_documentos(): void
    {
        $complementarioId = 1;
        $aspirantes = new Collection([
            new AspiranteComplementario(['id' => 1, 'complementario_id' => $complementarioId]),
            new AspiranteComplementario(['id' => 2, 'complementario_id' => $complementarioId]),
        ]);

        $this->aspiranteRepositoryMock->shouldReceive('findByProgramaConDocumentosExcluyendoRechazados')
            ->once()
            ->with($complementarioId)
            ->andReturn($aspirantes);

        $resultado = $this->service->getAspirantesConDocumentos($complementarioId);

        $this->assertCount(2, $resultado);
        $this->assertInstanceOf(Collection::class, $resultado);
    }

    #[Test]
    public function obtiene_aspirantes_para_exportacion(): void
    {
        $complementarioId = 1;
        $aspirantes = new Collection([
            new AspiranteComplementario(['id' => 1, 'complementario_id' => $complementarioId]),
        ]);

        $this->aspiranteRepositoryMock->shouldReceive('findByProgramaParaExportacion')
            ->once()
            ->with($complementarioId)
            ->andReturn($aspirantes);

        $resultado = $this->service->getAspirantesParaExportacion($complementarioId);

        $this->assertCount(1, $resultado);
        $this->assertInstanceOf(Collection::class, $resultado);
    }

    #[Test]
    public function obtiene_estadisticas_exclusion(): void
    {
        $complementarioId = 1;
        $estadisticas = [
            'total' => 10,
            'rechazados' => 2,
            'sin_documento' => 3,
            'no_registrados_sofia' => 1,
            'validos' => 4,
        ];

        $this->aspiranteRepositoryMock->shouldReceive('getEstadisticasExclusion')
            ->once()
            ->with($complementarioId)
            ->andReturn($estadisticas);

        $resultado = $this->service->getEstadisticasExclusion($complementarioId);

        $this->assertEquals(10, $resultado['total']);
        $this->assertEquals(2, $resultado['rechazados']);
        $this->assertEquals(3, $resultado['sin_documento']);
        $this->assertEquals(1, $resultado['no_registrados_sofia']);
        $this->assertEquals(4, $resultado['validos']);
    }

    #[Test]
    public function procesa_validacion_documentos(): void
    {
        $persona = new \App\Models\Persona(['id' => 1, 'numero_documento' => '1234567890']);
        $aspirante = new AspiranteComplementario(['id' => 1, 'persona_id' => 1]);
        $aspirante->setRelation('persona', $persona);
        
        $aspirantes = new Collection([$aspirante]);
        $files = ['documento1.pdf', 'documento2.pdf'];

        $this->documentoServiceMock->shouldReceive('construirPatronBusqueda')
            ->once()
            ->with($persona)
            ->andReturn('CC_1234567890_Test_User_');

        $this->documentoServiceMock->shouldReceive('buscarDocumentoEnGoogleDrive')
            ->once()
            ->with($files, 'CC_1234567890_Test_User_')
            ->andReturn(true);

        $this->personaRepositoryMock->shouldReceive('updateDocumentoStatus')
            ->once()
            ->with($persona, true)
            ->andReturn(true);

        $resultado = $this->service->procesarValidacionDocumentos($aspirantes, $files);

        $this->assertEquals(1, $resultado['total']);
        $this->assertEquals(1, $resultado['con_documento']);
        $this->assertEquals(0, $resultado['sin_documento']);
        $this->assertEquals(0, $resultado['errores']);
    }
}


