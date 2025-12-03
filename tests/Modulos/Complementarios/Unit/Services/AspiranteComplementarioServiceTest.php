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
    private const TEST_NUMERO_DOCUMENTO = '1234567890';
    private const TEST_NUMERO_DOCUMENTO_ALT = '1111111111';
    private const TEST_PATRON_BUSQUEDA = 'CC_1234567890_Test_User_';
    private const TEST_PATRON_BUSQUEDA_ALT = 'CC_1111111111_Test_User_';
    private const TEST_RUTA_DOCUMENTO = 'documentos_aspirantes/CC_1234567890_Test_User_.pdf';
    private const TEST_RUTA_DOCUMENTO_ALT = 'documentos_aspirantes/CC_1111111111_Test_User_.pdf';
    private const TEST_CONTENIDO_PDF = 'PDF content';
    private const TEST_ERROR_CONEXION = 'Error de conexión';

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
        $persona = new \App\Models\Persona(['id' => 1, 'numero_documento' => self::TEST_NUMERO_DOCUMENTO]);
        $aspirante = new AspiranteComplementario(['id' => 1, 'persona_id' => 1]);
        $aspirante->setRelation('persona', $persona);
        
        $aspirantes = new Collection([$aspirante]);
        $files = ['documento1.pdf', 'documento2.pdf'];

        $this->documentoServiceMock->shouldReceive('construirPatronBusqueda')
            ->once()
            ->with($persona)
            ->andReturn(self::TEST_PATRON_BUSQUEDA);

        $this->documentoServiceMock->shouldReceive('buscarDocumentoEnGoogleDrive')
            ->once()
            ->with($files, self::TEST_PATRON_BUSQUEDA)
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

    #[Test]
    public function procesa_validacion_documentos_sin_documento(): void
    {
        $persona = new \App\Models\Persona(['id' => 1, 'numero_documento' => self::TEST_NUMERO_DOCUMENTO]);
        $aspirante = new AspiranteComplementario(['id' => 1, 'persona_id' => 1]);
        $aspirante->setRelation('persona', $persona);
        
        $aspirantes = new Collection([$aspirante]);
        $files = ['documento1.pdf', 'documento2.pdf'];

        $this->documentoServiceMock->shouldReceive('construirPatronBusqueda')
            ->once()
            ->with($persona)
            ->andReturn(self::TEST_PATRON_BUSQUEDA);

        $this->documentoServiceMock->shouldReceive('buscarDocumentoEnGoogleDrive')
            ->once()
            ->with($files, self::TEST_PATRON_BUSQUEDA)
            ->andReturn(false);

        $this->personaRepositoryMock->shouldReceive('updateDocumentoStatus')
            ->once()
            ->with($persona, false)
            ->andReturn(true);

        $resultado = $this->service->procesarValidacionDocumentos($aspirantes, $files);

        $this->assertEquals(1, $resultado['total']);
        $this->assertEquals(0, $resultado['con_documento']);
        $this->assertEquals(1, $resultado['sin_documento']);
        $this->assertEquals(0, $resultado['errores']);
    }

    #[Test]
    public function procesa_validacion_documentos_con_error(): void
    {
        $persona = new \App\Models\Persona(['id' => 1, 'numero_documento' => self::TEST_NUMERO_DOCUMENTO]);
        $aspirante = new AspiranteComplementario(['id' => 1, 'persona_id' => 1]);
        $aspirante->setRelation('persona', $persona);
        
        $aspirantes = new Collection([$aspirante]);
        $files = ['documento1.pdf'];

        $this->documentoServiceMock->shouldReceive('construirPatronBusqueda')
            ->once()
            ->with($persona)
            ->andThrow(new \Exception(self::TEST_ERROR_CONEXION));

        $resultado = $this->service->procesarValidacionDocumentos($aspirantes, $files);

        $this->assertEquals(1, $resultado['total']);
        $this->assertEquals(0, $resultado['con_documento']);
        $this->assertEquals(0, $resultado['sin_documento']);
        $this->assertEquals(1, $resultado['errores']);
    }

    #[Test]
    public function procesa_validacion_documentos_multiple(): void
    {
        $persona1 = new \App\Models\Persona(['id' => 1, 'numero_documento' => self::TEST_NUMERO_DOCUMENTO_ALT]);
        $persona2 = new \App\Models\Persona(['id' => 2, 'numero_documento' => '2222222222']);
        
        $aspirante1 = new AspiranteComplementario(['id' => 1, 'persona_id' => 1]);
        $aspirante2 = new AspiranteComplementario(['id' => 2, 'persona_id' => 2]);
        
        $aspirante1->setRelation('persona', $persona1);
        $aspirante2->setRelation('persona', $persona2);
        
        $aspirantes = new Collection([$aspirante1, $aspirante2]);
        $files = ['documento1.pdf', 'documento2.pdf'];

        $this->documentoServiceMock->shouldReceive('construirPatronBusqueda')
            ->twice()
            ->andReturn(self::TEST_PATRON_BUSQUEDA_ALT, 'CC_2222222222_Test_User_');

        $this->documentoServiceMock->shouldReceive('buscarDocumentoEnGoogleDrive')
            ->twice()
            ->andReturn(true, false);

        $this->personaRepositoryMock->shouldReceive('updateDocumentoStatus')
            ->twice()
            ->andReturn(true);

        $resultado = $this->service->procesarValidacionDocumentos($aspirantes, $files);

        $this->assertEquals(2, $resultado['total']);
        $this->assertEquals(1, $resultado['con_documento']);
        $this->assertEquals(1, $resultado['sin_documento']);
        $this->assertEquals(0, $resultado['errores']);
    }

    #[Test]
    public function procesa_descarga_documentos_exitoso(): void
    {
        $persona = new \App\Models\Persona(['id' => 1, 'numero_documento' => self::TEST_NUMERO_DOCUMENTO]);
        $aspirante = new AspiranteComplementario(['id' => 1, 'persona_id' => 1]);
        $aspirante->setRelation('persona', $persona);
        
        $aspirantes = new Collection([$aspirante]);
        $tempDir = sys_get_temp_dir();
        $pdf = Mockery::mock('stdClass');
        $pdf->shouldIgnoreMissing();

        $this->documentoServiceMock->shouldReceive('construirPatronBusqueda')
            ->once()
            ->with($persona)
            ->andReturn(self::TEST_PATRON_BUSQUEDA);

        $this->documentoServiceMock->shouldReceive('encontrarArchivoEnGoogleDrive')
            ->once()
            ->with(self::TEST_PATRON_BUSQUEDA)
            ->andReturn(self::TEST_RUTA_DOCUMENTO);

        \Illuminate\Support\Facades\Storage::shouldReceive('disk')
            ->with('google')
            ->andReturnSelf();
        \Illuminate\Support\Facades\Storage::shouldReceive('exists')
            ->with(self::TEST_RUTA_DOCUMENTO)
            ->andReturn(true);
        \Illuminate\Support\Facades\Storage::shouldReceive('get')
            ->with(self::TEST_RUTA_DOCUMENTO)
            ->andReturn(self::TEST_CONTENIDO_PDF);

        $this->documentoServiceMock->shouldReceive('agregarPaginasAPDF')
            ->once()
            ->with($pdf, Mockery::type('string'));

        $resultado = $this->service->procesarDescargaDocumentos($aspirantes, $pdf, $tempDir);

        $this->assertEquals(1, $resultado['archivos_agregados']);
        $this->assertCount(1, $resultado['archivos_temporales']);
        $this->assertStringContainsString('temp_0_' . self::TEST_NUMERO_DOCUMENTO . '.pdf', $resultado['archivos_temporales'][0]);
    }

    #[Test]
    public function procesa_descarga_documentos_archivo_no_encontrado(): void
    {
        $persona = new \App\Models\Persona(['id' => 1, 'numero_documento' => self::TEST_NUMERO_DOCUMENTO]);
        $aspirante = new AspiranteComplementario(['id' => 1, 'persona_id' => 1]);
        $aspirante->setRelation('persona', $persona);
        
        $aspirantes = new Collection([$aspirante]);
        $tempDir = sys_get_temp_dir();
        $pdf = Mockery::mock('stdClass');
        $pdf->shouldIgnoreMissing();

        $this->documentoServiceMock->shouldReceive('construirPatronBusqueda')
            ->once()
            ->with($persona)
            ->andReturn(self::TEST_PATRON_BUSQUEDA);

        $this->documentoServiceMock->shouldReceive('encontrarArchivoEnGoogleDrive')
            ->once()
            ->with(self::TEST_PATRON_BUSQUEDA)
            ->andReturn(null);

        $resultado = $this->service->procesarDescargaDocumentos($aspirantes, $pdf, $tempDir);

        $this->assertEquals(0, $resultado['archivos_agregados']);
        $this->assertCount(0, $resultado['archivos_temporales']);
    }

    #[Test]
    public function procesa_descarga_documentos_archivo_no_existe_en_storage(): void
    {
        $persona = new \App\Models\Persona(['id' => 1, 'numero_documento' => self::TEST_NUMERO_DOCUMENTO]);
        $aspirante = new AspiranteComplementario(['id' => 1, 'persona_id' => 1]);
        $aspirante->setRelation('persona', $persona);
        
        $aspirantes = new Collection([$aspirante]);
        $tempDir = sys_get_temp_dir();
        $pdf = Mockery::mock('stdClass');
        $pdf->shouldIgnoreMissing();

        $this->documentoServiceMock->shouldReceive('construirPatronBusqueda')
            ->once()
            ->with($persona)
            ->andReturn(self::TEST_PATRON_BUSQUEDA);

        $this->documentoServiceMock->shouldReceive('encontrarArchivoEnGoogleDrive')
            ->once()
            ->with(self::TEST_PATRON_BUSQUEDA)
            ->andReturn(self::TEST_RUTA_DOCUMENTO);

        \Illuminate\Support\Facades\Storage::shouldReceive('disk')
            ->with('google')
            ->andReturnSelf();
        \Illuminate\Support\Facades\Storage::shouldReceive('exists')
            ->with(self::TEST_RUTA_DOCUMENTO)
            ->andReturn(false);

        $resultado = $this->service->procesarDescargaDocumentos($aspirantes, $pdf, $tempDir);

        $this->assertEquals(0, $resultado['archivos_agregados']);
        $this->assertCount(0, $resultado['archivos_temporales']);
    }

    #[Test]
    public function procesa_descarga_documentos_con_error(): void
    {
        $persona = new \App\Models\Persona(['id' => 1, 'numero_documento' => self::TEST_NUMERO_DOCUMENTO]);
        $aspirante = new AspiranteComplementario(['id' => 1, 'persona_id' => 1]);
        $aspirante->setRelation('persona', $persona);
        
        $aspirantes = new Collection([$aspirante]);
        $tempDir = sys_get_temp_dir();
        $pdf = Mockery::mock('stdClass');
        $pdf->shouldIgnoreMissing();

        $this->documentoServiceMock->shouldReceive('construirPatronBusqueda')
            ->once()
            ->with($persona)
            ->andThrow(new \Exception(self::TEST_ERROR_CONEXION));

        $resultado = $this->service->procesarDescargaDocumentos($aspirantes, $pdf, $tempDir);

        $this->assertEquals(0, $resultado['archivos_agregados']);
        $this->assertCount(0, $resultado['archivos_temporales']);
    }

    #[Test]
    public function procesa_descarga_documentos_multiple_aspirantes(): void
    {
        $persona1 = new \App\Models\Persona(['id' => 1, 'numero_documento' => self::TEST_NUMERO_DOCUMENTO_ALT]);
        $persona2 = new \App\Models\Persona(['id' => 2, 'numero_documento' => '2222222222']);
        
        $aspirante1 = new AspiranteComplementario(['id' => 1, 'persona_id' => 1]);
        $aspirante2 = new AspiranteComplementario(['id' => 2, 'persona_id' => 2]);
        
        $aspirante1->setRelation('persona', $persona1);
        $aspirante2->setRelation('persona', $persona2);
        
        $aspirantes = new Collection([$aspirante1, $aspirante2]);
        $tempDir = sys_get_temp_dir();
        $pdf = Mockery::mock('stdClass');
        $pdf->shouldIgnoreMissing();

        $this->documentoServiceMock->shouldReceive('construirPatronBusqueda')
            ->twice()
            ->andReturn(self::TEST_PATRON_BUSQUEDA_ALT, 'CC_2222222222_Test_User_');

        $this->documentoServiceMock->shouldReceive('encontrarArchivoEnGoogleDrive')
            ->twice()
            ->andReturn(self::TEST_RUTA_DOCUMENTO_ALT, null);

        \Illuminate\Support\Facades\Storage::shouldReceive('disk')
            ->with('google')
            ->andReturnSelf();
        \Illuminate\Support\Facades\Storage::shouldReceive('exists')
            ->with(self::TEST_RUTA_DOCUMENTO_ALT)
            ->andReturn(true);
        \Illuminate\Support\Facades\Storage::shouldReceive('get')
            ->with(self::TEST_RUTA_DOCUMENTO_ALT)
            ->andReturn(self::TEST_CONTENIDO_PDF);

        $this->documentoServiceMock->shouldReceive('agregarPaginasAPDF')
            ->once()
            ->with($pdf, Mockery::type('string'));

        $resultado = $this->service->procesarDescargaDocumentos($aspirantes, $pdf, $tempDir);

        $this->assertEquals(1, $resultado['archivos_agregados']);
        $this->assertCount(1, $resultado['archivos_temporales']);
    }

    #[Test]
    public function genera_archivo_pdf(): void
    {
        $programa = new \App\Models\Complementarios\ComplementarioOfertado([
            'id' => 1,
            'nombre' => 'Programa Test',
        ]);
        
        $tempDir = sys_get_temp_dir() . '/test_pdf_' . uniqid();
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $archivosTemporales = [];
        
        $pdf = Mockery::mock('stdClass');
        $pdf->shouldIgnoreMissing();
        $pdf->shouldReceive('Output')
            ->once()
            ->with('F', Mockery::on(function ($path) use ($tempDir) {
                // Crear el archivo dummy para que response()->download() funcione
                file_put_contents($path, self::TEST_CONTENIDO_PDF);
                return str_starts_with($path, $tempDir);
            }));

        $this->documentoServiceMock->shouldReceive('limpiarArchivosTemporales')
            ->once()
            ->with($archivosTemporales);

        $response = $this->service->generarArchivoPDF($programa, $pdf, $tempDir, $archivosTemporales);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\BinaryFileResponse::class, $response);
        
        // Limpiar
        if (file_exists($tempDir)) {
            array_map('unlink', glob("$tempDir/*"));
            rmdir($tempDir);
        }
    }

    #[Test]
    public function genera_archivo_pdf_con_nombre_programa_espacios(): void
    {
        $programa = new \App\Models\Complementarios\ComplementarioOfertado([
            'id' => 1,
            'nombre' => 'Programa Con Espacios',
        ]);
        
        $tempDir = sys_get_temp_dir() . '/test_pdf_' . uniqid();
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $archivosTemporales = [];
        
        $pdf = Mockery::mock('stdClass');
        $pdf->shouldIgnoreMissing();
        $pdf->shouldReceive('Output')
            ->once()
            ->with('F', Mockery::on(function ($path) use ($tempDir) {
                // Crear el archivo dummy para que response()->download() funcione
                file_put_contents($path, self::TEST_CONTENIDO_PDF);
                return str_contains($path, 'cedulas_Programa_Con_Espacios_') && str_starts_with($path, $tempDir);
            }));

        $this->documentoServiceMock->shouldReceive('limpiarArchivosTemporales')
            ->once()
            ->with($archivosTemporales);

        $response = $this->service->generarArchivoPDF($programa, $pdf, $tempDir, $archivosTemporales);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\BinaryFileResponse::class, $response);
        
        // Limpiar
        if (file_exists($tempDir)) {
            array_map('unlink', glob("$tempDir/*"));
            rmdir($tempDir);
        }
    }

    #[Test]
    public function procesa_descarga_documentos_lista_vacia(): void
    {
        $aspirantes = new Collection([]);
        $tempDir = sys_get_temp_dir();
        $pdf = Mockery::mock('stdClass');
        $pdf->shouldIgnoreMissing();

        $resultado = $this->service->procesarDescargaDocumentos($aspirantes, $pdf, $tempDir);

        $this->assertEquals(0, $resultado['archivos_agregados']);
        $this->assertCount(0, $resultado['archivos_temporales']);
    }

    #[Test]
    public function procesa_validacion_documentos_lista_vacia(): void
    {
        $aspirantes = new Collection([]);
        $files = [];

        $resultado = $this->service->procesarValidacionDocumentos($aspirantes, $files);

        $this->assertEquals(0, $resultado['total']);
        $this->assertEquals(0, $resultado['con_documento']);
        $this->assertEquals(0, $resultado['sin_documento']);
        $this->assertEquals(0, $resultado['errores']);
    }

    #[Test]
    public function procesa_validacion_documentos_con_persona_id_null(): void
    {
        $persona = new \App\Models\Persona(['id' => 1, 'numero_documento' => self::TEST_NUMERO_DOCUMENTO]);
        $aspirante = new AspiranteComplementario(['id' => 1, 'persona_id' => null]);
        $aspirante->setRelation('persona', $persona);
        
        $aspirantes = new Collection([$aspirante]);
        $files = ['documento1.pdf'];

        $this->documentoServiceMock->shouldReceive('construirPatronBusqueda')
            ->once()
            ->with($persona)
            ->andThrow(new \Exception(self::TEST_ERROR_CONEXION));

        $resultado = $this->service->procesarValidacionDocumentos($aspirantes, $files);

        $this->assertEquals(1, $resultado['total']);
        $this->assertEquals(1, $resultado['errores']);
    }
}


