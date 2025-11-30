<?php

namespace Tests\Complementarios\Feature\Controllers;

use Tests\TestCase;
use App\Models\ComplementarioOfertado;
use App\Models\Persona;
use App\Models\Pais;
use App\Models\Departamento;
use App\Models\Municipio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class InscripcionComplementarioControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar seeders necesarios para las pruebas
        // Estos datos son requeridos por las claves foráneas en PersonaFactory
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\TemaSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\PersonaSeeder::class,
            \Database\Seeders\UsersSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
            \Database\Seeders\CentroFormacionSeeder::class,
            \Database\Seeders\SedeSeeder::class,
            \Database\Seeders\BloqueSeeder::class,
            \Database\Seeders\PisoSeeder::class,
            \Database\Seeders\AmbienteSeeder::class,
            \Database\Seeders\JornadaFormacionSeeder::class,
        ]);
        
        Storage::fake('google');
    }

    /** @test */
    public function puede_ver_formulario_inscripcion_general()
    {
        $response = $this->get(route('inscripcion.general'));

        $response->assertStatus(200);
        $response->assertViewIs('complementarios.inscripciones.general');
    }

    /** @test */
    public function puede_ver_formulario_inscripcion_programa()
    {
        $programa = ComplementarioOfertado::factory()->create();

        $response = $this->get(route('programas-complementarios.inscripcion', $programa->id));

        $response->assertStatus(200);
        $response->assertViewIs('complementarios.inscripciones.create');
        $response->assertViewHas('programa');
    }

    /** @test */
    public function puede_procesar_inscripcion_general()
    {
        // Obtener datos del seeder
        $pais = Pais::first();
        $departamento = Departamento::where('pais_id', $pais->id)->first();
        $municipio = Municipio::where('departamento_id', $departamento->id)->first();
        
        // Obtener parametros_temas correctos del seeder
        $tipoDocumentoParametroTema = \App\Models\ParametroTema::where('tema_id', 2)
            ->where('parametro_id', 3)
            ->first();
        $generoParametroTema = \App\Models\ParametroTema::where('tema_id', 3)
            ->where('parametro_id', 9)
            ->first();

        // Si no existen, crear los necesarios
        if (!$tipoDocumentoParametroTema) {
            $parametro = \App\Models\Parametro::find(3) ?? \App\Models\Parametro::create(['id' => 3, 'name' => 'CÉDULA DE CIUDADANÍA', 'status' => 1]);
            $tema = \App\Models\Tema::find(2) ?? \App\Models\Tema::create(['id' => 2, 'name' => 'TIPO DE DOCUMENTO', 'status' => 1]);
            $tipoDocumentoParametroTema = \App\Models\ParametroTema::create([
                'parametro_id' => $parametro->id,
                'tema_id' => $tema->id,
                'status' => 1,
            ]);
        }

        if (!$generoParametroTema) {
            $parametro = \App\Models\Parametro::find(9) ?? \App\Models\Parametro::create(['id' => 9, 'name' => 'MASCULINO', 'status' => 1]);
            $tema = \App\Models\Tema::find(3) ?? \App\Models\Tema::create(['id' => 3, 'name' => 'GÉNERO', 'status' => 1]);
            $generoParametroTema = \App\Models\ParametroTema::create([
                'parametro_id' => $parametro->id,
                'tema_id' => $tema->id,
                'status' => 1,
            ]);
        }

        $numeroDocumento = uniqid('doc_');
        $email = uniqid('test_') . '@test.com';

        $data = [
            'tipo_documento' => 3, // CÉDULA DE CIUDADANÍA (parametro_id, el servicio lo convierte a parametros_temas.id)
            'numero_documento' => $numeroDocumento,
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 9, // MASCULINO (parametro_id, el servicio lo convierte a parametros_temas.id)
            'celular' => '3001234567',
            'email' => $email,
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => 'Calle 123',
        ];

        $response = $this->post(route('inscripcion.general.store'), $data);

        $response->assertRedirect(route('inscripcion.general'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('personas', [
            'numero_documento' => $numeroDocumento,
            'email' => $email,
        ]);
    }

    /** @test */
    public function no_procesa_inscripcion_general_si_persona_ya_existe()
    {
        // Obtener datos del seeder
        $pais = Pais::first();
        $departamento = Departamento::where('pais_id', $pais->id)->first();
        $municipio = Municipio::where('departamento_id', $departamento->id)->first();
        
        $numeroDocumento = uniqid('doc_');
        $email = uniqid('test_') . '@test.com';
        
        Persona::factory()->create([
            'numero_documento' => $numeroDocumento,
            'email' => $email,
        ]);

        $data = [
            'tipo_documento' => 3, // CÉDULA DE CIUDADANÍA (parametro_id, el servicio lo convierte a parametros_temas.id)
            'numero_documento' => $numeroDocumento,
            'email' => $email,
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 9, // MASCULINO (parametro_id, el servicio lo convierte a parametros_temas.id)
            'celular' => '3001234567',
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => 'Calle 123',
        ];

        $response = $this->post(route('inscripcion.general.store'), $data);

        $response->assertSessionHasErrors(['numero_documento', 'email']);
    }

    /** @test */
    public function puede_procesar_inscripcion_a_programa()
    {
        // Obtener datos del seeder
        $pais = Pais::first();
        $departamento = Departamento::where('pais_id', $pais->id)->first();
        $municipio = Municipio::where('departamento_id', $departamento->id)->first();
        
        $programa = ComplementarioOfertado::factory()->conOferta()->create();

        // Obtener parametros_temas correctos del seeder
        $tipoDocumentoParametroTema = \App\Models\ParametroTema::where('tema_id', 2)
            ->where('parametro_id', 3)
            ->first();
        $generoParametroTema = \App\Models\ParametroTema::where('tema_id', 3)
            ->where('parametro_id', 9)
            ->first();

        // Si no existen, crear los necesarios
        if (!$tipoDocumentoParametroTema) {
            $parametro = \App\Models\Parametro::find(3) ?? \App\Models\Parametro::create(['id' => 3, 'name' => 'CÉDULA DE CIUDADANÍA', 'status' => 1]);
            $tema = \App\Models\Tema::find(2) ?? \App\Models\Tema::create(['id' => 2, 'name' => 'TIPO DE DOCUMENTO', 'status' => 1]);
            $tipoDocumentoParametroTema = \App\Models\ParametroTema::create([
                'parametro_id' => $parametro->id,
                'tema_id' => $tema->id,
                'status' => 1,
            ]);
        }

        if (!$generoParametroTema) {
            $parametro = \App\Models\Parametro::find(9) ?? \App\Models\Parametro::create(['id' => 9, 'name' => 'MASCULINO', 'status' => 1]);
            $tema = \App\Models\Tema::find(3) ?? \App\Models\Tema::create(['id' => 3, 'name' => 'GÉNERO', 'status' => 1]);
            $generoParametroTema = \App\Models\ParametroTema::create([
                'parametro_id' => $parametro->id,
                'tema_id' => $tema->id,
                'status' => 1,
            ]);
        }

        $numeroDocumento = uniqid('doc_');
        $email = uniqid('test_') . '@test.com';

        $data = [
            'tipo_documento' => 3, // CÉDULA DE CIUDADANÍA (parametro_id, el servicio lo convierte a parametros_temas.id)
            'numero_documento' => $numeroDocumento,
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 9, // MASCULINO (parametro_id, el servicio lo convierte a parametros_temas.id)
            'celular' => '3001234567',
            'email' => $email,
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => 'Calle 123',
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
