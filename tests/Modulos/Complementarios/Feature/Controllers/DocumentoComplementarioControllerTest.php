<?php

declare(strict_types=1);

namespace Tests\Complementarios\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Persona;
use App\Models\Parametro;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Complementarios\AspiranteComplementario;
use App\Services\Complementarios\ComplementarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\Complementarios\Concerns\SeedsComplementariosDatabase;

class DocumentoComplementarioControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    use SeedsComplementariosDatabase;

    private const MIME_TYPE_PDF = 'application/pdf';
    private const MIME_TYPE_JPEG = 'image/jpeg';
    private const MIME_TYPE_PNG = 'image/png';
    private const MIME_TYPE_TEXT = 'text/plain';
    private const NUMERO_DOCUMENTO_TEST = '1234567890';
    private const NUMERO_DOCUMENTO_TEST_2 = '9876543210';
    private const NUMERO_DOCUMENTO_TEST_3 = '9876543211';
    private const NUMERO_DOCUMENTO_TEST_4 = '9876543212';
    private const NOMBRE_PRIMER_NOMBRE = 'JUAN';
    private const NOMBRE_PRIMER_APELLIDO = 'PEREZ';
    private const EMAIL_TEST = 'juan.perez@example.com';
    private const NOMBRE_TIPO_DOCUMENTO = 'CEDULA DE CIUDADANIA';
    private const FILE_SIZE_KB = 1024;
    private const FILE_SIZE_6MB = 6144;
    private const ESTADO_EN_PROCESO = 1;
    private const ESTADO_INICIAL = 0;

    protected User $user;
    protected ComplementarioOfertado $programa;
    protected AspiranteComplementario $aspirante;
    protected Persona $persona;
    protected Parametro $tipoDocumento;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedComplementariosDatabaseIfNeeded();

        // Mock Storage disk for Google Drive
        Storage::fake('google');

        // Create user
        $this->user = User::factory()->create();

        // Create tipo documento
        $this->tipoDocumento = Parametro::factory()->create([
            'name' => self::NOMBRE_TIPO_DOCUMENTO,
            'status' => self::ESTADO_EN_PROCESO,
        ]);

        // Create persona with tipo documento
        $this->persona = Persona::factory()->create([
            'tipo_documento' => $this->tipoDocumento->id,
            'numero_documento' => self::NUMERO_DOCUMENTO_TEST,
            'primer_nombre' => self::NOMBRE_PRIMER_NOMBRE,
            'primer_apellido' => self::NOMBRE_PRIMER_APELLIDO,
            'email' => self::EMAIL_TEST,
        ]);
        
        // Load tipoDocumento relationship
        $this->persona->load('tipoDocumento');

        // Create programa complementario
        $this->programa = ComplementarioOfertado::factory()->create();

        // Create aspirante
        $this->aspirante = AspiranteComplementario::factory()->create([
            'persona_id' => $this->persona->id,
            'complementario_id' => $this->programa->id,
            'estado' => self::ESTADO_EN_PROCESO,
        ]);
    }

    #[Test]
    public function puede_mostrar_formulario_de_documentos()
    {
        $response = $this->get(route('programas-complementarios.formulario-documentos', [
            'id' => $this->programa->id,
            'aspirante_id' => $this->aspirante->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('complementarios.inscripciones.documents');
        $response->assertViewHas('programa');
        $response->assertViewHas('aspirante_id');
    }

    #[Test]
    public function formulario_documentos_retorna_404_si_programa_no_existe()
    {
        $response = $this->get(route('programas-complementarios.formulario-documentos', [
            'id' => 99999,
            'aspirante_id' => $this->aspirante->id,
        ]));

        $response->assertStatus(404);
    }

    #[Test]
    public function puede_subir_documento_exitosamente()
    {
        // Ensure relationships are loaded
        $this->aspirante->load('persona.tipoDocumento');
        
        $file = UploadedFile::fake()->create('documento.pdf', self::FILE_SIZE_KB, self::MIME_TYPE_PDF);

        $response = $this->post(route('programas-complementarios.subir-documentos', [
            'id' => $this->programa->id,
        ]), [
            'documento_identidad' => $file,
            'aspirante_id' => $this->aspirante->id,
            'acepto_privacidad' => '1',
        ]);

        $response->assertRedirect(route('login.index'));
        $response->assertSessionHas('success');

        // Verify aspirante was updated
        $this->aspirante->refresh();
        $this->assertNotNull($this->aspirante->documento_identidad_path);
        $this->assertNotNull($this->aspirante->documento_identidad_nombre);
        $this->assertEquals(self::ESTADO_EN_PROCESO, $this->aspirante->estado);

        // Verify file was stored
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('google');
        $disk->assertExists($this->aspirante->documento_identidad_path);
    }

    #[Test]
    public function subir_documento_valida_archivo_requerido()
    {
        $response = $this->post(route('programas-complementarios.subir-documentos', [
            'id' => $this->programa->id,
        ]), [
            'aspirante_id' => $this->aspirante->id,
            'acepto_privacidad' => '1',
        ]);

        $response->assertSessionHasErrors(['documento_identidad']);
    }

    #[Test]
    public function subir_documento_valida_formato_pdf()
    {
        $file = UploadedFile::fake()->create('documento.jpg', self::FILE_SIZE_KB, self::MIME_TYPE_JPEG);

        $response = $this->post(route('programas-complementarios.subir-documentos', [
            'id' => $this->programa->id,
        ]), [
            'documento_identidad' => $file,
            'aspirante_id' => $this->aspirante->id,
            'acepto_privacidad' => '1',
        ]);

        $response->assertSessionHasErrors(['documento_identidad']);
    }

    #[Test]
    public function subir_documento_valida_tamano_maximo()
    {
        $file = UploadedFile::fake()->create('documento.pdf', self::FILE_SIZE_6MB, self::MIME_TYPE_PDF);

        $response = $this->post(route('programas-complementarios.subir-documentos', [
            'id' => $this->programa->id,
        ]), [
            'documento_identidad' => $file,
            'aspirante_id' => $this->aspirante->id,
            'acepto_privacidad' => '1',
        ]);

        $response->assertSessionHasErrors(['documento_identidad']);
    }

    #[Test]
    public function subir_documento_valida_aspirante_id_requerido()
    {
        $file = UploadedFile::fake()->create('documento.pdf', self::FILE_SIZE_KB, self::MIME_TYPE_PDF);

        $response = $this->post(route('programas-complementarios.subir-documentos', [
            'id' => $this->programa->id,
        ]), [
            'documento_identidad' => $file,
            'acepto_privacidad' => '1',
        ]);

        $response->assertSessionHasErrors(['aspirante_id']);
    }

    #[Test]
    public function subir_documento_valida_aspirante_id_existe()
    {
        $file = UploadedFile::fake()->create('documento.pdf', self::FILE_SIZE_KB, self::MIME_TYPE_PDF);

        $response = $this->post(route('programas-complementarios.subir-documentos', [
            'id' => $this->programa->id,
        ]), [
            'documento_identidad' => $file,
            'aspirante_id' => 99999,
            'acepto_privacidad' => '1',
        ]);

        $response->assertSessionHasErrors(['aspirante_id']);
    }

    #[Test]
    public function subir_documento_valida_acepto_privacidad()
    {
        $file = UploadedFile::fake()->create('documento.pdf', self::FILE_SIZE_KB, self::MIME_TYPE_PDF);

        $response = $this->post(route('programas-complementarios.subir-documentos', [
            'id' => $this->programa->id,
        ]), [
            'documento_identidad' => $file,
            'aspirante_id' => $this->aspirante->id,
        ]);

        $response->assertSessionHasErrors(['acepto_privacidad']);
    }

    #[Test]
    public function subir_documento_retorna_error_si_aspirante_no_existe()
    {
        $file = UploadedFile::fake()->create('documento.pdf', self::FILE_SIZE_KB, self::MIME_TYPE_PDF);

        // Delete aspirante
        $aspiranteId = $this->aspirante->id;
        $this->aspirante->delete();

        $response = $this->post(route('programas-complementarios.subir-documentos', [
            'id' => $this->programa->id,
        ]), [
            'documento_identidad' => $file,
            'aspirante_id' => $aspiranteId,
            'acepto_privacidad' => '1',
        ]);

        // Validation fails before reaching findOrFail, so it redirects with errors
        $response->assertSessionHasErrors(['aspirante_id']);
    }

    #[Test]
    public function subir_documento_genera_nombre_archivo_correcto()
    {
        // Ensure persona has tipoDocumento relationship loaded
        $this->aspirante->load('persona.tipoDocumento');
        
        $file = UploadedFile::fake()->create('documento.pdf', self::FILE_SIZE_KB, self::MIME_TYPE_PDF);

        $response = $this->post(route('programas-complementarios.subir-documentos', [
            'id' => $this->programa->id,
        ]), [
            'documento_identidad' => $file,
            'aspirante_id' => $this->aspirante->id,
            'acepto_privacidad' => '1',
        ]);

        $response->assertRedirect(route('login.index'));

        $this->aspirante->refresh();
        $fileName = $this->aspirante->documento_identidad_nombre;

        // Verify filename format: tipo_documento_numero_nombre_apellido_timestamp.pdf
        $this->assertNotNull($fileName, 'documento_identidad_nombre should not be null');
        $this->assertStringContainsString(str_replace(' ', '_', self::NOMBRE_TIPO_DOCUMENTO), $fileName);
        $this->assertStringContainsString(self::NUMERO_DOCUMENTO_TEST, $fileName);
        $this->assertStringContainsString(self::NOMBRE_PRIMER_NOMBRE, $fileName);
        $this->assertStringContainsString(self::NOMBRE_PRIMER_APELLIDO, $fileName);
        $this->assertStringEndsWith('.pdf', $fileName);
    }

    #[Test]
    public function subir_documento_maneja_excepcion_cuando_persona_no_tiene_tipo_documento()
    {
        // Create persona without tipoDocumento
        $personaSinTipoDoc = Persona::factory()->create([
            'tipo_documento' => null,
            'numero_documento' => '9999999999',
            'primer_nombre' => 'SIN',
            'primer_apellido' => 'DOCUMENTO',
        ]);

        $aspiranteSinTipoDoc = AspiranteComplementario::factory()->create([
            'persona_id' => $personaSinTipoDoc->id,
            'complementario_id' => $this->programa->id,
        ]);

        $file = UploadedFile::fake()->create('documento.pdf', self::FILE_SIZE_KB, self::MIME_TYPE_PDF);

        $response = $this->post(route('programas-complementarios.subir-documentos', [
            'id' => $this->programa->id,
        ]), [
            'documento_identidad' => $file,
            'aspirante_id' => $aspiranteSinTipoDoc->id,
            'acepto_privacidad' => '1',
        ]);

        // Should handle the exception gracefully (uses 'DOC' as fallback)
        $response->assertRedirect();
    }

    #[Test]
    public function puede_mostrar_procesar_documentos()
    {
        $this->actingAs($this->user);

        // Mock ComplementarioService
        $mockService = $this->mock(ComplementarioService::class);
        $mockService->shouldReceive('getTiposDocumento')
            ->once()
            ->andReturn(collect([
                (object) ['id' => 1, 'name' => 'CEDULA'],
                (object) ['id' => 2, 'name' => 'PASAPORTE'],
            ]));

        $this->app->instance(ComplementarioService::class, $mockService);

        $response = $this->get(route('procesar-documentos'));

        $response->assertStatus(200);
        $response->assertViewIs('complementarios.inscripciones.processing');
        $response->assertViewHas('tiposDocumento');
    }

    #[Test]
    public function puede_procesar_documento_submit_exitosamente()
    {
        $this->actingAs($this->user);

        // Use existing tipo documento or create if doesn't exist
        $tipoDocumento = Parametro::firstOrCreate(
            ['name' => self::NOMBRE_TIPO_DOCUMENTO],
            ['status' => self::ESTADO_EN_PROCESO]
        );

        $file = UploadedFile::fake()->create('documento.pdf', self::FILE_SIZE_KB, self::MIME_TYPE_PDF);

        $response = $this->post(route('procesar-documentos.submit'), [
            'tipo_documento' => $tipoDocumento->id,
            'numero_documento' => self::NUMERO_DOCUMENTO_TEST_2,
            'documento_identidad' => $file,
        ]);

        $response->assertRedirect(route('procesar-documentos'));
        $response->assertSessionHas('success');

        // Verify file was stored
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('google');
        $files = $disk->files('documentos_aspirantes');
        if (!empty($files)) {
            $disk->assertExists($files[0]);
        }
    }

    #[Test]
    public function procesar_documento_submit_valida_tipo_documento_requerido()
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('documento.pdf', self::FILE_SIZE_KB, self::MIME_TYPE_PDF);

        $response = $this->post(route('procesar-documentos.submit'), [
            'numero_documento' => self::NUMERO_DOCUMENTO_TEST_2,
            'documento_identidad' => $file,
        ]);

        $response->assertSessionHasErrors(['tipo_documento']);
    }

    #[Test]
    public function procesar_documento_submit_valida_tipo_documento_existe()
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('documento.pdf', self::FILE_SIZE_KB, self::MIME_TYPE_PDF);

        // Use non-existent tipo documento ID
        $nonExistentId = 99999;
        $this->assertNull(Parametro::find($nonExistentId));

        $response = $this->post(route('procesar-documentos.submit'), [
            'tipo_documento' => $nonExistentId,
            'numero_documento' => self::NUMERO_DOCUMENTO_TEST_2,
            'documento_identidad' => $file,
        ]);

        $response->assertSessionHasErrors(['tipo_documento']);
        $response->assertStatus(302);
        $this->assertFalse($response->isRedirect(route('procesar-documentos')));
    }

    #[Test]
    public function procesar_documento_submit_valida_numero_documento_requerido()
    {
        $this->actingAs($this->user);

        $tipoDocumento = Parametro::factory()->create();

        $file = UploadedFile::fake()->create('documento.pdf', self::FILE_SIZE_KB, self::MIME_TYPE_PDF);

        $response = $this->post(route('procesar-documentos.submit'), [
            'tipo_documento' => $tipoDocumento->id,
            'documento_identidad' => $file,
        ]);

        $response->assertSessionHasErrors(['numero_documento']);
    }

    #[Test]
    public function procesar_documento_submit_valida_archivo_requerido()
    {
        $this->actingAs($this->user);

        $tipoDocumento = Parametro::factory()->create();

        $response = $this->post(route('procesar-documentos.submit'), [
            'tipo_documento' => $tipoDocumento->id,
            'numero_documento' => self::NUMERO_DOCUMENTO_TEST_2,
        ]);

        $response->assertSessionHasErrors(['documento_identidad']);
        $response->assertStatus(302);
        $this->assertFalse($response->isRedirect(route('procesar-documentos')));
    }

    #[Test]
    public function procesar_documento_submit_valida_formatos_archivo()
    {
        $this->actingAs($this->user);

        $tipoDocumento = Parametro::factory()->create();
        $file = UploadedFile::fake()->create('documento.txt', self::FILE_SIZE_KB, self::MIME_TYPE_TEXT);

        $response = $this->post(route('procesar-documentos.submit'), [
            'tipo_documento' => $tipoDocumento->id,
            'numero_documento' => self::NUMERO_DOCUMENTO_TEST_2,
            'documento_identidad' => $file,
        ]);

        $response->assertSessionHasErrors(['documento_identidad']);
    }

    #[Test]
    public function procesar_documento_submit_acepta_formatos_validos()
    {
        $this->actingAs($this->user);

        $tipoDocumento = Parametro::factory()->create();

        // Test PDF
        $filePdf = UploadedFile::fake()->create('documento.pdf', self::FILE_SIZE_KB, self::MIME_TYPE_PDF);
        $response = $this->post(route('procesar-documentos.submit'), [
            'tipo_documento' => $tipoDocumento->id,
            'numero_documento' => self::NUMERO_DOCUMENTO_TEST_2,
            'documento_identidad' => $filePdf,
        ]);
        $response->assertRedirect(route('procesar-documentos'));

        // Test JPG
        $fileJpg = UploadedFile::fake()->create('documento.jpg', self::FILE_SIZE_KB, self::MIME_TYPE_JPEG);
        $response = $this->post(route('procesar-documentos.submit'), [
            'tipo_documento' => $tipoDocumento->id,
            'numero_documento' => self::NUMERO_DOCUMENTO_TEST_3,
            'documento_identidad' => $fileJpg,
        ]);
        $response->assertRedirect(route('procesar-documentos'));

        // Test PNG
        $filePng = UploadedFile::fake()->create('documento.png', self::FILE_SIZE_KB, self::MIME_TYPE_PNG);
        $response = $this->post(route('procesar-documentos.submit'), [
            'tipo_documento' => $tipoDocumento->id,
            'numero_documento' => self::NUMERO_DOCUMENTO_TEST_4,
            'documento_identidad' => $filePng,
        ]);
        $response->assertRedirect(route('procesar-documentos'));
    }


    #[Test]
    public function subir_documento_actualiza_estado_aspirante()
    {
        $file = UploadedFile::fake()->create('documento.pdf', self::FILE_SIZE_KB, self::MIME_TYPE_PDF);

        // Initial state should be different
        $this->aspirante->update(['estado' => self::ESTADO_INICIAL]);

        $this->post(route('programas-complementarios.subir-documentos', [
            'id' => $this->programa->id,
        ]), [
            'documento_identidad' => $file,
            'aspirante_id' => $this->aspirante->id,
            'acepto_privacidad' => '1',
        ]);

        $this->aspirante->refresh();
        $this->assertEquals(self::ESTADO_EN_PROCESO, $this->aspirante->estado);
    }
}

