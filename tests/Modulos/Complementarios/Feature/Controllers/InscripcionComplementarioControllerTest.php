<?php

namespace Tests\Complementarios\Feature\Controllers;

use Tests\TestCase;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Persona;
use App\Models\Pais;
use App\Models\Departamento;
use App\Models\Municipio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Complementarios\Concerns\SeedsComplementariosDatabase;

class InscripcionComplementarioControllerTest extends TestCase
{
    use RefreshDatabase;
    use SeedsComplementariosDatabase;

    private const TEST_NOMBRE = 'Juan';
    private const TEST_APELLIDO = 'Pérez';
    private const TEST_FECHA_NACIMIENTO = '1990-01-01';
    private const TEST_CELULAR = '3001234567';
    private const TEST_DIRECCION = 'Calle 123';
    private const TEST_EMAIL_DOMAIN = '@test.com';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Desactivar CSRF para tests
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);
        
        $this->seedComplementariosDatabaseIfNeeded();
        
        Storage::fake('google');
    }

    #[Test]
    public function puede_ver_formulario_inscripcion_general()
    {
        $response = $this->get(route('inscripcion.general'));

        $response->assertStatus(200);
        $response->assertViewIs('complementarios.inscripciones.general');
    }

    #[Test]
    public function puede_ver_formulario_inscripcion_programa()
    {
        $programa = ComplementarioOfertado::factory()->create();

        $response = $this->get(route('programas-complementarios.inscripcion', $programa->id));

        $response->assertStatus(200);
        $response->assertViewIs('complementarios.inscripciones.create');
        $response->assertViewHas('programa');
    }

    #[Test]
    public function puede_procesar_inscripcion_general()
    {
        // Obtener datos del seeder
        $pais = Pais::first();
        $departamento = Departamento::where('pais_id', $pais->id)->first();
        $municipio = Municipio::where('departamento_id', $departamento->id)->first();
        
        // Asegurar que existen los parametros_temas necesarios en la base de datos
        $parametroDoc = \App\Models\Parametro::firstOrCreate(
            ['id' => 3],
            ['name' => 'CÉDULA DE CIUDADANÍA', 'status' => 1]
        );
        $temaDoc = \App\Models\Tema::firstOrCreate(
            ['id' => 2],
            ['name' => 'TIPO DE DOCUMENTO', 'status' => 1]
        );
        \App\Models\ParametroTema::firstOrCreate(
            [
                'parametro_id' => $parametroDoc->id,
                'tema_id' => $temaDoc->id,
            ],
            ['status' => 1]
        );

        $parametroGenero = \App\Models\Parametro::firstOrCreate(
            ['id' => 9],
            ['name' => 'MASCULINO', 'status' => 1]
        );
        $temaGenero = \App\Models\Tema::firstOrCreate(
            ['id' => 3],
            ['name' => 'GÉNERO', 'status' => 1]
        );
        \App\Models\ParametroTema::firstOrCreate(
            [
                'parametro_id' => $parametroGenero->id,
                'tema_id' => $temaGenero->id,
            ],
            ['status' => 1]
        );

        $numeroDocumento = uniqid('doc_');
        $email = uniqid('test_') . self::TEST_EMAIL_DOMAIN;

        $data = [
            'tipo_documento' => 3, // CÉDULA DE CIUDADANÍA (parametro_id, el servicio lo convierte a parametros_temas.id)
            'numero_documento' => $numeroDocumento,
            'primer_nombre' => self::TEST_NOMBRE,
            'primer_apellido' => self::TEST_APELLIDO,
            'fecha_nacimiento' => self::TEST_FECHA_NACIMIENTO,
            'genero' => 9, // MASCULINO (parametro_id, el servicio lo convierte a parametros_temas.id)
            'celular' => self::TEST_CELULAR,
            'email' => $email,
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => self::TEST_DIRECCION,
        ];

        $response = $this->post(route('inscripcion.general.store'), $data);

        $response->assertRedirect(route('inscripcion.general'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('personas', [
            'numero_documento' => $numeroDocumento,
            'email' => $email,
        ]);
    }

    #[Test]
    public function no_procesa_inscripcion_general_si_persona_ya_existe()
    {
        // Obtener datos del seeder
        $pais = Pais::first();
        $departamento = Departamento::where('pais_id', $pais->id)->first();
        $municipio = Municipio::where('departamento_id', $departamento->id)->first();
        
        $numeroDocumento = uniqid('doc_');
        $email = uniqid('test_') . self::TEST_EMAIL_DOMAIN;
        
        Persona::factory()->create([
            'numero_documento' => $numeroDocumento,
            'email' => $email,
        ]);

        $data = [
            'tipo_documento' => 3, // CÉDULA DE CIUDADANÍA (parametro_id, el servicio lo convierte a parametros_temas.id)
            'numero_documento' => $numeroDocumento,
            'email' => $email,
            'primer_nombre' => self::TEST_NOMBRE,
            'primer_apellido' => self::TEST_APELLIDO,
            'fecha_nacimiento' => self::TEST_FECHA_NACIMIENTO,
            'genero' => 9, // MASCULINO (parametro_id, el servicio lo convierte a parametros_temas.id)
            'celular' => self::TEST_CELULAR,
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => self::TEST_DIRECCION,
        ];

        $response = $this->post(route('inscripcion.general.store'), $data);

        $response->assertSessionHasErrors(['numero_documento', 'email']);
    }

    #[Test]
    public function puede_procesar_inscripcion_a_programa()
    {
        // Obtener datos del seeder
        $pais = Pais::first();
        $departamento = Departamento::where('pais_id', $pais->id)->first();
        $municipio = Municipio::where('departamento_id', $departamento->id)->first();
        
        $programa = ComplementarioOfertado::factory()->conOferta()->create();

        // Asegurar que existen los parametros_temas necesarios en la base de datos
        $parametroDoc = \App\Models\Parametro::firstOrCreate(
            ['id' => 3],
            ['name' => 'CÉDULA DE CIUDADANÍA', 'status' => 1]
        );
        $temaDoc = \App\Models\Tema::firstOrCreate(
            ['id' => 2],
            ['name' => 'TIPO DE DOCUMENTO', 'status' => 1]
        );
        \App\Models\ParametroTema::firstOrCreate(
            [
                'parametro_id' => $parametroDoc->id,
                'tema_id' => $temaDoc->id,
            ],
            ['status' => 1]
        );

        $parametroGenero = \App\Models\Parametro::firstOrCreate(
            ['id' => 9],
            ['name' => 'MASCULINO', 'status' => 1]
        );
        $temaGenero = \App\Models\Tema::firstOrCreate(
            ['id' => 3],
            ['name' => 'GÉNERO', 'status' => 1]
        );
        \App\Models\ParametroTema::firstOrCreate(
            [
                'parametro_id' => $parametroGenero->id,
                'tema_id' => $temaGenero->id,
            ],
            ['status' => 1]
        );

        $numeroDocumento = uniqid('doc_');
        $email = uniqid('test_') . self::TEST_EMAIL_DOMAIN;

        $data = [
            'tipo_documento' => 3, // CÉDULA DE CIUDADANÍA (parametro_id, el servicio lo convierte a parametros_temas.id)
            'numero_documento' => $numeroDocumento,
            'primer_nombre' => self::TEST_NOMBRE,
            'primer_apellido' => self::TEST_APELLIDO,
            'fecha_nacimiento' => self::TEST_FECHA_NACIMIENTO,
            'genero' => 9, // MASCULINO (parametro_id, el servicio lo convierte a parametros_temas.id)
            'celular' => self::TEST_CELULAR,
            'email' => $email,
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => self::TEST_DIRECCION,
            'documento_identidad' => UploadedFile::fake()->create('documento.pdf', 1000),
            'acepto_privacidad' => '1',
            'acepto_terminos' => '1',
        ];

        $response = $this->post(route('programas-complementarios.procesar-inscripcion', $programa->id), $data);

        $response->assertRedirect(route('login.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('personas', [
            'numero_documento' => $numeroDocumento,
        ]);
        $this->assertDatabaseHas('aspirantes_complementarios', [
            'complementario_id' => $programa->id,
        ]);
    }
}
