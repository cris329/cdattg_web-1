<?php

namespace Tests\Complementarios\Unit\Services;

use App\Models\Persona;
use App\Services\Complementarios\AspiranteDocumentoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Complementarios\Concerns\SeedsComplementariosDatabase;

class AspiranteDocumentoServiceTest extends TestCase
{
    use RefreshDatabase;
    use SeedsComplementariosDatabase;

    private const TEST_APELLIDO = 'Pérez';
    private const TEST_NUMERO_DOCUMENTO = '1234567890';

    private AspiranteDocumentoService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedComplementariosDatabaseIfNeeded();

        $this->service = new AspiranteDocumentoService;
    }

    #[Test]
    public function construye_patron_busqueda(): void
    {
        $persona = Persona::factory()->create([
            'primer_nombre' => 'Juan',
            'primer_apellido' => self::TEST_APELLIDO,
        ]);

        $patron = $this->service->construirPatronBusqueda($persona);

        $this->assertIsString($patron);
        $this->assertStringContainsString($persona->numero_documento, $patron);
    }

    #[Test]
    public function busca_documento_en_google_drive(): void
    {
        $files = ['documento1.pdf', 'documento2.pdf'];
        $patron = 'CC_' . self::TEST_NUMERO_DOCUMENTO . '_Juan_Perez_';

        $resultado = $this->service->buscarDocumentoEnGoogleDrive($files, $patron);

        $this->assertIsBool($resultado);
    }

    #[Test]
    public function crea_directorio_temporal(): void
    {
        $directorio = $this->service->createTempDirectory();

        $this->assertIsString($directorio);
        $this->assertStringContainsString('temp', $directorio);
    }

    #[Test]
    public function construye_patron_busqueda_sin_tipo_documento(): void
    {
        $persona = Persona::factory()->create([
            'primer_nombre' => 'Juan',
            'primer_apellido' => self::TEST_APELLIDO,
            'numero_documento' => self::TEST_NUMERO_DOCUMENTO,
        ]);
        $persona->setRelation('tipoDocumento', null);

        $patron = $this->service->construirPatronBusqueda($persona);

        $this->assertIsString($patron);
        $this->assertStringContainsString('DOC', $patron);
        $this->assertStringContainsString($persona->numero_documento, $patron);
    }

    #[Test]
    public function construye_patron_busqueda_con_nombres_espacios(): void
    {
        $persona = Persona::factory()->create([
            'primer_nombre' => 'Juan Carlos',
            'primer_apellido' => self::TEST_APELLIDO . ' García',
            'numero_documento' => self::TEST_NUMERO_DOCUMENTO,
        ]);

        $patron = $this->service->construirPatronBusqueda($persona);

        $this->assertIsString($patron);
        $this->assertStringNotContainsString(' ', $patron); // Debe reemplazar espacios con guiones bajos
    }

    #[Test]
    public function busca_documento_en_google_drive_encontrado(): void
    {
        $files = [
            'CC_' . self::TEST_NUMERO_DOCUMENTO . '_Juan_Perez_.pdf',
            'documento2.pdf',
        ];
        $patron = 'CC_' . self::TEST_NUMERO_DOCUMENTO . '_Juan_Perez_';

        // Mock de Storage
        \Illuminate\Support\Facades\Storage::shouldReceive('disk')
            ->with('google')
            ->andReturnSelf();
        \Illuminate\Support\Facades\Storage::shouldReceive('exists')
            ->with('CC_' . self::TEST_NUMERO_DOCUMENTO . '_Juan_Perez_.pdf')
            ->andReturn(true);

        $resultado = $this->service->buscarDocumentoEnGoogleDrive($files, $patron);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function busca_documento_en_google_drive_no_encontrado(): void
    {
        $files = ['documento1.pdf', 'documento2.pdf'];
        $patron = 'CC_' . self::TEST_NUMERO_DOCUMENTO . '_Juan_Perez_';

        \Illuminate\Support\Facades\Storage::shouldReceive('disk')
            ->with('google')
            ->andReturnSelf();
        \Illuminate\Support\Facades\Storage::shouldReceive('exists')
            ->andReturn(false);

        $resultado = $this->service->buscarDocumentoEnGoogleDrive($files, $patron);

        $this->assertFalse($resultado);
    }

    #[Test]
    public function busca_documento_con_variantes_patron(): void
    {
        $files = ['CC ' . self::TEST_NUMERO_DOCUMENTO . ' Juan Perez .pdf'];
        $patron = 'CC_' . self::TEST_NUMERO_DOCUMENTO . '_Juan_Perez_';

        \Illuminate\Support\Facades\Storage::shouldReceive('disk')
            ->with('google')
            ->andReturnSelf();
        \Illuminate\Support\Facades\Storage::shouldReceive('exists')
            ->andReturn(true);

        $resultado = $this->service->buscarDocumentoEnGoogleDrive($files, $patron);

        $this->assertIsBool($resultado);
    }

    #[Test]
    public function encuentra_archivo_en_google_drive(): void
    {
        $patron = 'CC_' . self::TEST_NUMERO_DOCUMENTO . '_Juan_Perez_';

        \Illuminate\Support\Facades\Storage::shouldReceive('disk')
            ->with('google')
            ->andReturnSelf();
        \Illuminate\Support\Facades\Storage::shouldReceive('files')
            ->with('documentos_aspirantes')
            ->andReturn(['CC_' . self::TEST_NUMERO_DOCUMENTO . '_Juan_Perez_.pdf']);

        $resultado = $this->service->encontrarArchivoEnGoogleDrive($patron);

        $this->assertIsString($resultado);
        $this->assertEquals('CC_' . self::TEST_NUMERO_DOCUMENTO . '_Juan_Perez_.pdf', $resultado);
    }

    #[Test]
    public function encuentra_archivo_en_google_drive_no_encontrado(): void
    {
        $patron = 'CC_9999999999_Test_User_';

        \Illuminate\Support\Facades\Storage::shouldReceive('disk')
            ->with('google')
            ->andReturnSelf();
        \Illuminate\Support\Facades\Storage::shouldReceive('files')
            ->with('documentos_aspirantes')
            ->andReturn(['CC_' . self::TEST_NUMERO_DOCUMENTO . '_Juan_Perez_.pdf']);

        $resultado = $this->service->encontrarArchivoEnGoogleDrive($patron);

        $this->assertNull($resultado);
    }

    #[Test]
    public function encuentra_archivo_con_patron_sin_nombres(): void
    {
        $patron = 'CC_' . self::TEST_NUMERO_DOCUMENTO . '_Juan_Perez_';

        \Illuminate\Support\Facades\Storage::shouldReceive('disk')
            ->with('google')
            ->andReturnSelf();
        \Illuminate\Support\Facades\Storage::shouldReceive('files')
            ->with('documentos_aspirantes')
            ->andReturn(['CC_' . self::TEST_NUMERO_DOCUMENTO . '_.pdf']);

        $resultado = $this->service->encontrarArchivoEnGoogleDrive($patron);

        $this->assertIsString($resultado);
    }

    #[Test]
    public function obtiene_archivos_google_drive(): void
    {
        $files = ['documento1.pdf', 'documento2.pdf'];

        \Illuminate\Support\Facades\Storage::shouldReceive('disk')
            ->with('google')
            ->andReturnSelf();
        \Illuminate\Support\Facades\Storage::shouldReceive('files')
            ->with('documentos_aspirantes')
            ->andReturn($files);

        $resultado = $this->service->getGoogleDriveFiles();

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);
    }

    #[Test]
    public function obtiene_archivos_google_drive_con_error(): void
    {
        \Illuminate\Support\Facades\Storage::shouldReceive('disk')
            ->with('google')
            ->andReturnSelf();
        \Illuminate\Support\Facades\Storage::shouldReceive('files')
            ->with('documentos_aspirantes')
            ->andThrow(new \Exception('Error de conexión'));

        $this->expectException(\App\Exceptions\Complementarios\GoogleDriveException::class);

        $this->service->getGoogleDriveFiles();
    }

    #[Test]
    public function limpia_archivos_temporales(): void
    {
        $tempDir = sys_get_temp_dir();
        $archivo1 = $tempDir . '/temp1.pdf';
        $archivo2 = $tempDir . '/temp2.pdf';

        // Crear archivos temporales
        file_put_contents($archivo1, 'test');
        file_put_contents($archivo2, 'test');

        $this->assertFileExists($archivo1);
        $this->assertFileExists($archivo2);

        $this->service->limpiarArchivosTemporales([$archivo1, $archivo2]);

        $this->assertFileDoesNotExist($archivo1);
        $this->assertFileDoesNotExist($archivo2);
    }

    #[Test]
    public function limpia_archivos_temporales_inexistentes(): void
    {
        $archivos = ['/ruta/inexistente/archivo.pdf'];

        // No debe lanzar excepción si el archivo no existe
        $this->service->limpiarArchivosTemporales($archivos);

        $this->assertTrue(true); // Si llegamos aquí, no hubo excepción
    }

    #[Test]
    public function agrega_paginas_a_pdf(): void
    {
        // Crear un PDF temporal de prueba
        $tempDir = $this->service->createTempDirectory();
        $tempFilePath = $tempDir . '/test_' . uniqid() . '.pdf';

        // Crear un PDF simple usando FPDI
        $pdfOrigen = new \setasign\Fpdi\Fpdi();
        $pdfOrigen->AddPage();
        $pdfOrigen->SetFont('Arial', 'B', 16);
        $pdfOrigen->Cell(40, 10, 'Test PDF');
        $pdfOrigen->Output('F', $tempFilePath);

        $this->assertFileExists($tempFilePath);

        // Crear un nuevo PDF para agregar páginas
        $pdfNuevo = new \setasign\Fpdi\Fpdi();

        // Agregar páginas del PDF temporal
        $this->service->agregarPaginasAPDF($pdfNuevo, $tempFilePath);

        // Verificar que el PDF tiene al menos una página
        $this->assertGreaterThan(0, $pdfNuevo->getNumPages());

        // Limpiar
        if (file_exists($tempFilePath)) {
            unlink($tempFilePath);
        }
    }

    #[Test]
    public function busca_documento_con_error_en_verificacion(): void
    {
        $files = ['CC_' . self::TEST_NUMERO_DOCUMENTO . '_Juan_Perez_.pdf'];
        $patron = 'CC_' . self::TEST_NUMERO_DOCUMENTO . '_Juan_Perez_';

        \Illuminate\Support\Facades\Storage::shouldReceive('disk')
            ->with('google')
            ->andReturnSelf();
        \Illuminate\Support\Facades\Storage::shouldReceive('exists')
            ->andThrow(new \Exception('Error de conexión'));

        $resultado = $this->service->buscarDocumentoEnGoogleDrive($files, $patron);

        // Debe retornar false cuando hay error
        $this->assertFalse($resultado);
    }

    #[Test]
    public function encuentra_archivo_con_patron_con_espacios(): void
    {
        $patron = 'CC ' . self::TEST_NUMERO_DOCUMENTO . ' Juan Perez ';

        \Illuminate\Support\Facades\Storage::shouldReceive('disk')
            ->with('google')
            ->andReturnSelf();
        \Illuminate\Support\Facades\Storage::shouldReceive('files')
            ->with('documentos_aspirantes')
            ->andReturn(['CC_' . self::TEST_NUMERO_DOCUMENTO . '_Juan_Perez_.pdf']);

        $resultado = $this->service->encontrarArchivoEnGoogleDrive($patron);

        $this->assertIsString($resultado);
    }

    #[Test]
    public function encuentra_archivo_con_patron_sin_nombres_con_espacios(): void
    {
        $patron = 'CC_' . self::TEST_NUMERO_DOCUMENTO . '_Juan_Perez_';

        \Illuminate\Support\Facades\Storage::shouldReceive('disk')
            ->with('google')
            ->andReturnSelf();
        \Illuminate\Support\Facades\Storage::shouldReceive('files')
            ->with('documentos_aspirantes')
            ->andReturn(['CC ' . self::TEST_NUMERO_DOCUMENTO . ' .pdf']);

        $resultado = $this->service->encontrarArchivoEnGoogleDrive($patron);

        $this->assertIsString($resultado);
    }

    #[Test]
    public function encuentra_archivo_con_patron_invalido(): void
    {
        // Patrón con menos de 4 partes (no se puede crear patrón sin nombres)
        $patron = 'CC_123';

        \Illuminate\Support\Facades\Storage::shouldReceive('disk')
            ->with('google')
            ->andReturnSelf();
        \Illuminate\Support\Facades\Storage::shouldReceive('files')
            ->with('documentos_aspirantes')
            ->andReturn(['documento1.pdf']);

        $resultado = $this->service->encontrarArchivoEnGoogleDrive($patron);

        // Debe retornar null si no encuentra
        $this->assertNull($resultado);
    }

    #[Test]
    public function construye_patron_busqueda_con_tipo_documento_con_espacios(): void
    {
        $tipoDocumento = new \App\Models\Parametro([
            'id' => 1,
            'name' => 'CÉDULA DE CIUDADANÍA',
        ]);

        $persona = Persona::factory()->create([
            'primer_nombre' => 'Juan',
            'primer_apellido' => self::TEST_APELLIDO,
            'numero_documento' => self::TEST_NUMERO_DOCUMENTO,
        ]);
        $persona->setRelation('tipoDocumento', $tipoDocumento);

        $patron = $this->service->construirPatronBusqueda($persona);

        $this->assertIsString($patron);
        $this->assertStringNotContainsString(' ', $patron); // Debe reemplazar espacios con guiones bajos
        $this->assertStringContainsString('CÉDULA_DE_CIUDADANÍA', $patron);
    }

    #[Test]
    public function busca_documento_con_ruta_completa(): void
    {
        $files = ['documentos_aspirantes/CC_' . self::TEST_NUMERO_DOCUMENTO . '_Juan_Perez_.pdf'];
        $patron = 'CC_' . self::TEST_NUMERO_DOCUMENTO . '_Juan_Perez_';

        \Illuminate\Support\Facades\Storage::shouldReceive('disk')
            ->with('google')
            ->andReturnSelf();
        \Illuminate\Support\Facades\Storage::shouldReceive('exists')
            ->with('documentos_aspirantes/CC_' . self::TEST_NUMERO_DOCUMENTO . '_Juan_Perez_.pdf')
            ->andReturn(true);

        $resultado = $this->service->buscarDocumentoEnGoogleDrive($files, $patron);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function crea_directorio_temporal_cuando_ya_existe(): void
    {
        // Crear el directorio primero
        $directorio1 = $this->service->createTempDirectory();
        $this->assertDirectoryExists($directorio1);

        // Intentar crear de nuevo (debe retornar el mismo directorio)
        $directorio2 = $this->service->createTempDirectory();

        $this->assertEquals($directorio1, $directorio2);
        $this->assertDirectoryExists($directorio2);
    }

    #[Test]
    public function busca_documento_con_array_vacio(): void
    {
        $files = [];
        $patron = 'CC_' . self::TEST_NUMERO_DOCUMENTO . '_Juan_Perez_';

        $resultado = $this->service->buscarDocumentoEnGoogleDrive($files, $patron);

        $this->assertFalse($resultado);
    }

    #[Test]
    public function encuentra_archivo_con_patron_sin_numero_documento(): void
    {
        // Patrón sin número de documento numérico
        $patron = 'CC_ABC_Juan_Perez_';

        \Illuminate\Support\Facades\Storage::shouldReceive('disk')
            ->with('google')
            ->andReturnSelf();
        \Illuminate\Support\Facades\Storage::shouldReceive('files')
            ->with('documentos_aspirantes')
            ->andReturn(['documento1.pdf']);

        $resultado = $this->service->encontrarArchivoEnGoogleDrive($patron);

        // Debe retornar null si no puede crear patrón sin nombres
        $this->assertNull($resultado);
    }

    #[Test]
    public function limpia_archivos_temporales_con_array_vacio(): void
    {
        // No debe lanzar excepción con array vacío
        $this->service->limpiarArchivosTemporales([]);

        $this->assertTrue(true);
    }

    #[Test]
    public function busca_documento_con_multiples_variantes_patron(): void
    {
        $files = [
            'CC ' . self::TEST_NUMERO_DOCUMENTO . ' Juan Perez .pdf', // Con espacios
            'CC-' . self::TEST_NUMERO_DOCUMENTO . '-Juan-Perez-.pdf', // Con guiones
        ];
        $patron = 'CC_' . self::TEST_NUMERO_DOCUMENTO . '_Juan_Perez_';

        \Illuminate\Support\Facades\Storage::shouldReceive('disk')
            ->with('google')
            ->andReturnSelf();
        \Illuminate\Support\Facades\Storage::shouldReceive('exists')
            ->andReturn(true);

        $resultado = $this->service->buscarDocumentoEnGoogleDrive($files, $patron);

        $this->assertTrue($resultado);
    }
}

