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

class InscripcionComplementarioControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
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
        $pais = Pais::create(['pais' => 'Colombia', 'status' => 1]);
        $departamento = Departamento::create(['departamento' => 'Cundinamarca', 'pais_id' => $pais->id, 'status' => 1]);
        $municipio = Municipio::create(['municipio' => 'Bogotá', 'departamento_id' => $departamento->id, 'status' => 1]);

        $data = [
            'tipo_documento' => 1,
            'numero_documento' => '1234567890',
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 1,
            'celular' => '3001234567',
            'email' => 'juan@test.com',
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => 'Calle 123',
        ];

        $response = $this->post(route('inscripcion.general.store'), $data);

        $response->assertRedirect(route('inscripcion.general'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('personas', [
            'numero_documento' => '1234567890',
            'email' => 'juan@test.com',
        ]);
    }

    /** @test */
    public function no_procesa_inscripcion_general_si_persona_ya_existe()
    {
        $pais = Pais::create(['pais' => 'Colombia', 'status' => 1]);
        $departamento = Departamento::create(['departamento' => 'Cundinamarca', 'pais_id' => $pais->id, 'status' => 1]);
        $municipio = Municipio::create(['municipio' => 'Bogotá', 'departamento_id' => $departamento->id, 'status' => 1]);
        
        Persona::factory()->create([
            'numero_documento' => '1234567890',
            'email' => 'juan@test.com',
        ]);

        $data = [
            'tipo_documento' => 1,
            'numero_documento' => '1234567890',
            'email' => 'juan@test.com',
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 1,
            'celular' => '3001234567',
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => 'Calle 123',
        ];

        $response = $this->post(route('inscripcion.general.store'), $data);

        $response->assertSessionHas('error');
    }

    /** @test */
    public function puede_procesar_inscripcion_a_programa()
    {
        $pais = Pais::create(['pais' => 'Colombia', 'status' => 1]);
        $departamento = Departamento::create(['departamento' => 'Cundinamarca', 'pais_id' => $pais->id, 'status' => 1]);
        $municipio = Municipio::create(['municipio' => 'Bogotá', 'departamento_id' => $departamento->id, 'status' => 1]);
        $programa = ComplementarioOfertado::factory()->conOferta()->create();

        $data = [
            'tipo_documento' => 1,
            'numero_documento' => '1234567890',
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 1,
            'celular' => '3001234567',
            'email' => 'juan@test.com',
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
            'numero_documento' => '1234567890',
        ]);
        $this->assertDatabaseHas('aspirantes_complementarios', [
            'complementario_id' => $programa->id,
        ]);
    }
}
