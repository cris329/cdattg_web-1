<?php

namespace Tests\Complementarios\Unit\Requests;

use Tests\TestCase;
use App\Http\Requests\Complementarios\InscripcionComplementarioRequest;
use App\Models\Persona;
use App\Models\Pais;
use App\Models\Departamento;
use App\Models\Municipio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

class InscripcionComplementarioRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function valida_campos_requeridos()
    {
        $request = new InscripcionComplementarioRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('tipo_documento', $rules);
        $this->assertArrayHasKey('numero_documento', $rules);
        $this->assertArrayHasKey('primer_nombre', $rules);
        $this->assertArrayHasKey('primer_apellido', $rules);
        $this->assertArrayHasKey('fecha_nacimiento', $rules);
        $this->assertArrayHasKey('genero', $rules);
        $this->assertArrayHasKey('celular', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('documento_identidad', $rules);
    }

    /** @test */
    public function valida_edad_minima_14_anios()
    {
        $pais = Pais::create(['pais' => 'Colombia', 'status' => 1]);
        $departamento = Departamento::create(['departamento' => 'Cundinamarca', 'pais_id' => $pais->id, 'status' => 1]);
        $municipio = Municipio::create(['municipio' => 'Bogotá', 'departamento_id' => $departamento->id, 'status' => 1]);

        $data = [
            'tipo_documento' => 1,
            'numero_documento' => '1234567890',
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'fecha_nacimiento' => now()->subYears(13)->format('Y-m-d'), // Menor de 14
            'genero' => 1,
            'celular' => '3001234567',
            'email' => 'juan@test.com',
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => 'Calle 123',
            'documento_identidad' => \Illuminate\Http\UploadedFile::fake()->create('documento.pdf', 1000),
            'acepto_privacidad' => '1',
            'acepto_terminos' => '1',
        ];

        $validator = Validator::make($data, (new InscripcionComplementarioRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('14 años', $validator->errors()->first('fecha_nacimiento'));
    }

    /** @test */
    public function valida_formato_documento_identidad()
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
            'documento_identidad' => \Illuminate\Http\UploadedFile::fake()->create('documento.jpg', 1000), // No es PDF
            'acepto_privacidad' => '1',
            'acepto_terminos' => '1',
        ];

        $validator = Validator::make($data, (new InscripcionComplementarioRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('PDF', $validator->errors()->first('documento_identidad'));
    }

    /** @test */
    public function valida_tamano_maximo_documento()
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
            'documento_identidad' => \Illuminate\Http\UploadedFile::fake()->create('documento.pdf', 6000), // Mayor a 5MB
            'acepto_privacidad' => '1',
            'acepto_terminos' => '1',
        ];

        $validator = Validator::make($data, (new InscripcionComplementarioRequest())->rules());

        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function valida_aceptacion_terminos_y_privacidad()
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
            'documento_identidad' => \Illuminate\Http\UploadedFile::fake()->create('documento.pdf', 1000),
            // Sin aceptar términos
        ];

        $validator = Validator::make($data, (new InscripcionComplementarioRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('acepto_privacidad', $validator->errors()->toArray());
        $this->assertArrayHasKey('acepto_terminos', $validator->errors()->toArray());
    }
}
