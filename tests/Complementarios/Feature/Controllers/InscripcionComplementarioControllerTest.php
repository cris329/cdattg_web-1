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
        // Usar datos del seeder para evitar problemas de unique constraints
        $pais = Pais::findOrFail(1); // COLOMBIA
        $departamento = Departamento::findOrFail(25); // CUNDINAMARCA
        $municipio = Municipio::where('departamento_id', 25)->first();
        
        // Si no hay municipio de Cundinamarca, crear uno
        if (!$municipio) {
            $municipio = Municipio::create([
                'municipio' => 'Bogotá',
                'departamento_id' => $departamento->id,
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
        // Usar datos del seeder para evitar problemas de unique constraints
        $pais = Pais::findOrFail(1); // COLOMBIA
        $departamento = Departamento::findOrFail(25); // CUNDINAMARCA
        $municipio = Municipio::where('departamento_id', 25)->first();
        
        // Si no hay municipio de Cundinamarca, crear uno
        if (!$municipio) {
            $municipio = Municipio::create([
                'municipio' => 'Bogotá',
                'departamento_id' => $departamento->id,
                'status' => 1,
            ]);
        }
        
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
        // Usar datos del seeder para evitar problemas de unique constraints
        $pais = Pais::findOrFail(1); // COLOMBIA
        $departamento = Departamento::findOrFail(25); // CUNDINAMARCA
        $municipio = Municipio::where('departamento_id', 25)->first();
        
        // Si no hay municipio de Cundinamarca, crear uno
        if (!$municipio) {
            $municipio = Municipio::create([
                'municipio' => 'Bogotá',
                'departamento_id' => $departamento->id,
                'status' => 1,
            ]);
        }
        
        $programa = ComplementarioOfertado::factory()->conOferta()->create();

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
