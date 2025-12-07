<?php

namespace Tests\Complementarios\Unit\Services;

use App\Exceptions\AspirantesSinDocumentosException;
use App\Exceptions\DescargaDocumentosException;
use App\Exceptions\ProgramaNoEncontradoException;
use App\Models\Complementarios\AspiranteComplementario;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Repositories\Complementarios\AspiranteComplementarioRepository;
use App\Repositories\Complementarios\ComplementarioOfertadoRepository;
use App\Services\Complementarios\AspiranteComplementarioService;
use App\Services\Complementarios\AspiranteExportService;
use App\Services\Complementarios\AspiranteDocumentoService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use setasign\Fpdi\Fpdi;

class AspiranteExportServiceTest extends TestCase
{
    use DatabaseTransactions;

    private const TEST_NOMBRE_PROGRAMA = 'Programa Test';
    private const TEST_NUMERO_DOCUMENTO = '1234567890';

    protected AspiranteExportService $service;
    protected $aspiranteRepositoryMock;
    protected $programaRepositoryMock;
    protected $aspiranteComplementarioServiceMock;
    protected $documentoServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aspiranteRepositoryMock = Mockery::mock(AspiranteComplementarioRepository::class);
        $this->programaRepositoryMock = Mockery::mock(ComplementarioOfertadoRepository::class);
        $this->aspiranteComplementarioServiceMock = Mockery::mock(AspiranteComplementarioService::class);
        $this->documentoServiceMock = Mockery::mock(AspiranteDocumentoService::class);

        $this->service = new AspiranteExportService(
            $this->aspiranteRepositoryMock,
            $this->programaRepositoryMock,
            $this->aspiranteComplementarioServiceMock,
            $this->documentoServiceMock
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
        $this->assertInstanceOf(AspiranteExportService::class, $this->service);
    }

    #[Test]
    public function exporta_aspirantes_a_excel_exitoso(): void
    {
        $complementarioId = 1;
        $programa = new ComplementarioOfertado([
            'id' => $complementarioId,
            'nombre' => self::TEST_NOMBRE_PROGRAMA,
        ]);

        // Crear personas con relaciones
        $persona1 = new \App\Models\Persona([
            'id' => 1,
            'numero_documento' => self::TEST_NUMERO_DOCUMENTO,
        ]);
        $persona1->setRelation('tipoDocumento', null);
        $persona1->setRelation('caracterizacion', null);

        $persona2 = new \App\Models\Persona([
            'id' => 2,
            'numero_documento' => '0987654321',
        ]);
        $persona2->setRelation('tipoDocumento', null);
        $persona2->setRelation('caracterizacion', null);

        $aspirante1 = new AspiranteComplementario(['id' => 1]);
        $aspirante1->setRelation('persona', $persona1);
        $aspirante2 = new AspiranteComplementario(['id' => 2]);
        $aspirante2->setRelation('persona', $persona2);

        $aspirantes = new Collection([$aspirante1, $aspirante2]);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with($complementarioId)
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('findForExport')
            ->once()
            ->with($complementarioId)
            ->andReturn($aspirantes);

        $response = $this->service->exportarAspirantesExcel($complementarioId);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('Content-Type')
        );
    }

    #[Test]
    public function exporta_aspirantes_a_excel_programa_no_encontrado(): void
    {
        $complementarioId = 999;

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with($complementarioId)
            ->andReturn(null);

        $this->expectException(ProgramaNoEncontradoException::class);

        $this->service->exportarAspirantesExcel($complementarioId);
    }

    #[Test]
    public function exporta_aspirantes_a_excel_con_nombre_programa_espacios(): void
    {
        $complementarioId = 1;
        $programa = new ComplementarioOfertado([
            'id' => $complementarioId,
            'nombre' => 'Programa Con Espacios',
        ]);

        $aspirantes = new Collection([]);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with($complementarioId)
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('findForExport')
            ->once()
            ->with($complementarioId)
            ->andReturn($aspirantes);

        $response = $this->service->exportarAspirantesExcel($complementarioId);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
        $this->assertStringContainsString('Programa_Con_Espacios', $response->headers->get('Content-Disposition'));
    }

    #[Test]
    public function descarga_cedulas_exitoso(): void
    {
        $complementarioId = 1;
        $programa = new ComplementarioOfertado([
            'id' => $complementarioId,
            'nombre' => self::TEST_NOMBRE_PROGRAMA,
        ]);

        $aspirantes = new Collection([
            new AspiranteComplementario(['id' => 1]),
        ]);

        $tempDir = sys_get_temp_dir();
        $tempFile = tempnam($tempDir, 'test_') . '.pdf';
        file_put_contents($tempFile, 'PDF content');
        $archivosTemporales = [$tempFile];

        $pdfMock = Mockery::mock(Fpdi::class);
        $pdfMock->shouldIgnoreMissing();

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with($complementarioId)
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('findByProgramaConDocumentos')
            ->once()
            ->with($complementarioId)
            ->andReturn($aspirantes);

        $this->documentoServiceMock->shouldReceive('createTempDirectory')
            ->once()
            ->andReturn($tempDir);

        $serviceMock = Mockery::mock(AspiranteExportService::class, [
            $this->aspiranteRepositoryMock,
            $this->programaRepositoryMock,
            $this->aspiranteComplementarioServiceMock,
            $this->documentoServiceMock
        ])->makePartial();
        $serviceMock->shouldAllowMockingProtectedMethods();
        $serviceMock->shouldReceive('crearFpdi')
            ->once()
            ->andReturn($pdfMock);

        $this->aspiranteComplementarioServiceMock->shouldReceive('procesarDescargaDocumentos')
            ->once()
            ->with($aspirantes, $pdfMock, $tempDir)
            ->andReturn([
                'archivos_agregados' => 1,
                'archivos_temporales' => $archivosTemporales,
            ]);

        $this->aspiranteComplementarioServiceMock->shouldReceive('generarArchivoPDF')
            ->once()
            ->with($programa, $pdfMock, $tempDir, $archivosTemporales)
            ->andReturn(new \Symfony\Component\HttpFoundation\BinaryFileResponse($tempFile));

        $response = $serviceMock->descargarCedulas($complementarioId);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\BinaryFileResponse::class, $response);

        // Limpiar
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }

    #[Test]
    public function descarga_cedulas_programa_no_encontrado(): void
    {
        $complementarioId = 999;

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with($complementarioId)
            ->andReturn(null);

        $this->expectException(ProgramaNoEncontradoException::class);

        $this->service->descargarCedulas($complementarioId);
    }

    #[Test]
    public function descarga_cedulas_sin_aspirantes(): void
    {
        $complementarioId = 1;
        $programa = new ComplementarioOfertado([
            'id' => $complementarioId,
            'nombre' => self::TEST_NOMBRE_PROGRAMA,
        ]);

        $aspirantes = new Collection([]);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with($complementarioId)
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('findByProgramaConDocumentos')
            ->once()
            ->with($complementarioId)
            ->andReturn($aspirantes);

        $this->expectException(AspirantesSinDocumentosException::class);

        $this->service->descargarCedulas($complementarioId);
    }

    #[Test]
    public function descarga_cedulas_sin_archivos_agregados(): void
    {
        $complementarioId = 1;
        $programa = new ComplementarioOfertado([
            'id' => $complementarioId,
            'nombre' => self::TEST_NOMBRE_PROGRAMA,
        ]);

        $aspirantes = new Collection([
            new AspiranteComplementario(['id' => 1]),
        ]);

        $tempDir = sys_get_temp_dir();
        $archivosTemporales = [];

        $pdfMock = Mockery::mock(Fpdi::class);
        $pdfMock->shouldIgnoreMissing();

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with($complementarioId)
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('findByProgramaConDocumentos')
            ->once()
            ->with($complementarioId)
            ->andReturn($aspirantes);

        $this->documentoServiceMock->shouldReceive('createTempDirectory')
            ->once()
            ->andReturn($tempDir);

        $serviceMock = Mockery::mock(AspiranteExportService::class, [
            $this->aspiranteRepositoryMock,
            $this->programaRepositoryMock,
            $this->aspiranteComplementarioServiceMock,
            $this->documentoServiceMock
        ])->makePartial();
        $serviceMock->shouldAllowMockingProtectedMethods();
        $serviceMock->shouldReceive('crearFpdi')
            ->once()
            ->andReturn($pdfMock);

        $this->aspiranteComplementarioServiceMock->shouldReceive('procesarDescargaDocumentos')
            ->once()
            ->with($aspirantes, $pdfMock, $tempDir)
            ->andReturn([
                'archivos_agregados' => 0,
                'archivos_temporales' => $archivosTemporales,
            ]);

        $this->documentoServiceMock->shouldReceive('limpiarArchivosTemporales')
            ->once()
            ->with($archivosTemporales);

        $this->expectException(DescargaDocumentosException::class);

        $serviceMock->descargarCedulas($complementarioId);
    }

    #[Test]
    public function exporta_aspirantes_con_coleccion_vacia(): void
    {
        $complementarioId = 1;
        $programa = new ComplementarioOfertado([
            'id' => $complementarioId,
            'nombre' => self::TEST_NOMBRE_PROGRAMA,
        ]);

        $aspirantes = new Collection([]);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with($complementarioId)
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('findForExport')
            ->once()
            ->with($complementarioId)
            ->andReturn($aspirantes);

        $response = $this->service->exportarAspirantesExcel($complementarioId);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
    }

    #[Test]
    public function exporta_aspirantes_con_error_en_repositorio(): void
    {
        $complementarioId = 1;

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with($complementarioId)
            ->andThrow(new \Exception('Error de base de datos'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error de base de datos');

        $this->service->exportarAspirantesExcel($complementarioId);
    }

    #[Test]
    public function descarga_cedulas_con_error_en_procesamiento(): void
    {
        $complementarioId = 1;
        $programa = new ComplementarioOfertado([
            'id' => $complementarioId,
            'nombre' => self::TEST_NOMBRE_PROGRAMA,
        ]);

        $aspirantes = new Collection([
            new AspiranteComplementario(['id' => 1]),
        ]);

        $tempDir = sys_get_temp_dir();

        $pdfMock = Mockery::mock(Fpdi::class);
        $pdfMock->shouldIgnoreMissing();

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with($complementarioId)
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('findByProgramaConDocumentos')
            ->once()
            ->with($complementarioId)
            ->andReturn($aspirantes);

        $this->documentoServiceMock->shouldReceive('createTempDirectory')
            ->once()
            ->andReturn($tempDir);

        $serviceMock = Mockery::mock(AspiranteExportService::class, [
            $this->aspiranteRepositoryMock,
            $this->programaRepositoryMock,
            $this->aspiranteComplementarioServiceMock,
            $this->documentoServiceMock
        ])->makePartial();
        $serviceMock->shouldAllowMockingProtectedMethods();
        $serviceMock->shouldReceive('crearFpdi')
            ->once()
            ->andReturn($pdfMock);

        $this->aspiranteComplementarioServiceMock->shouldReceive('procesarDescargaDocumentos')
            ->once()
            ->with($aspirantes, $pdfMock, $tempDir)
            ->andThrow(new \Exception('Error procesando documentos'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error procesando documentos');

        $serviceMock->descargarCedulas($complementarioId);
    }

    #[Test]
    public function exporta_aspirantes_con_error_en_creacion_hoja(): void
    {
        $complementarioId = 1;
        $programa = new ComplementarioOfertado([
            'id' => $complementarioId,
            'nombre' => self::TEST_NOMBRE_PROGRAMA,
        ]);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with($complementarioId)
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('findForExport')
            ->once()
            ->with($complementarioId)
            ->andThrow(new \Exception('Error obteniendo aspirantes'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error obteniendo aspirantes');

        $this->service->exportarAspirantesExcel($complementarioId);
    }

    #[Test]
    public function exporta_aspirantes_con_datos_completos(): void
    {
        $complementarioId = 1;
        $programa = new ComplementarioOfertado([
            'id' => $complementarioId,
            'nombre' => self::TEST_NOMBRE_PROGRAMA,
        ]);

        // Crear persona con relaciones
        $tipoDocumento = new \App\Models\Parametro(['id' => 1, 'name' => 'CÉDULA DE CIUDADANÍA']);
        $caracterizacion = new \App\Models\Parametro(['id' => 2, 'nombre' => 'Técnico']);
        $persona = new \App\Models\Persona([
            'id' => 1,
            'numero_documento' => self::TEST_NUMERO_DOCUMENTO,
        ]);
        $persona->setRelation('tipoDocumento', $tipoDocumento);
        $persona->setRelation('caracterizacion', $caracterizacion);

        $aspirante = new AspiranteComplementario(['id' => 1]);
        $aspirante->setRelation('persona', $persona);

        $aspirantes = new Collection([$aspirante]);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with($complementarioId)
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('findForExport')
            ->once()
            ->with($complementarioId)
            ->andReturn($aspirantes);

        $response = $this->service->exportarAspirantesExcel($complementarioId);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
    }

    #[Test]
    public function exporta_aspirantes_con_persona_sin_tipo_documento(): void
    {
        $complementarioId = 1;
        $programa = new ComplementarioOfertado([
            'id' => $complementarioId,
            'nombre' => self::TEST_NOMBRE_PROGRAMA,
        ]);

        $persona = new \App\Models\Persona([
            'id' => 1,
            'numero_documento' => self::TEST_NUMERO_DOCUMENTO,
        ]);
        $persona->setRelation('tipoDocumento', null);
        $persona->setRelation('caracterizacion', null);

        $aspirante = new AspiranteComplementario(['id' => 1]);
        $aspirante->setRelation('persona', $persona);

        $aspirantes = new Collection([$aspirante]);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with($complementarioId)
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('findForExport')
            ->once()
            ->with($complementarioId)
            ->andReturn($aspirantes);

        $response = $this->service->exportarAspirantesExcel($complementarioId);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
    }
}

