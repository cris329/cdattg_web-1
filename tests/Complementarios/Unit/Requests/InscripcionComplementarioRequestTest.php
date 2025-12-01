<?php

namespace Tests\Complementarios\Unit\Requests;

use Tests\TestCase;
use App\Http\Requests\Complementarios\InscripcionComplementarioRequest;
use App\Models\Persona;
use App\Models\Pais;
use App\Models\Departamento;
use App\Models\Municipio;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;

class InscripcionComplementarioRequestTest extends TestCase
{
    use DatabaseTransactions;

    private const TEST_MUNICIPIO = 'Bogotá';
    private const TEST_NUMERO_DOCUMENTO = '1234567890';
    private const TEST_APELLIDO = 'Pérez';
    private const TEST_CELULAR = '3001234567';
    private const TEST_EMAIL = 'juan@test.com';
    private const TEST_DIRECCION = 'Calle 123';
    private const TEST_FECHA_NACIMIENTO = '1990-01-01';

    #[Test]
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

    #[Test]
    public function valida_edad_minima_14_anios()
    {
        $pais = Pais::create(['pais' => 'Colombia', 'status' => 1]);
        $departamento = Departamento::create(['departamento' => 'Cundinamarca', 'pais_id' => $pais->id, 'status' => 1]);
        $municipio = Municipio::create(['municipio' => self::TEST_MUNICIPIO, 'departamento_id' => $departamento->id, 'status' => 1]);

        $data = [
            'tipo_documento' => 1,
            'numero_documento' => self::TEST_NUMERO_DOCUMENTO,
            'primer_nombre' => 'Juan',
            'primer_apellido' => self::TEST_APELLIDO,
            'fecha_nacimiento' => now()->subYears(13)->format('Y-m-d'), // Menor de 14
            'genero' => 1,
            'celular' => self::TEST_CELULAR,
            'email' => self::TEST_EMAIL,
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => self::TEST_DIRECCION,
            'documento_identidad' => \Illuminate\Http\UploadedFile::fake()->create('documento.pdf', 1000),
            'acepto_privacidad' => '1',
            'acepto_terminos' => '1',
        ];

        $validator = Validator::make($data, (new InscripcionComplementarioRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('14 años', $validator->errors()->first('fecha_nacimiento'));
    }

    #[Test]
    public function valida_formato_documento_identidad()
    {
        $pais = Pais::create(['pais' => 'Colombia', 'status' => 1]);
        $departamento = Departamento::create(['departamento' => 'Cundinamarca', 'pais_id' => $pais->id, 'status' => 1]);
        $municipio = Municipio::create(['municipio' => self::TEST_MUNICIPIO, 'departamento_id' => $departamento->id, 'status' => 1]);

        $data = [
            'tipo_documento' => 1,
            'numero_documento' => self::TEST_NUMERO_DOCUMENTO,
            'primer_nombre' => 'Juan',
            'primer_apellido' => self::TEST_APELLIDO,
            'fecha_nacimiento' => self::TEST_FECHA_NACIMIENTO,
            'genero' => 1,
            'celular' => self::TEST_CELULAR,
            'email' => self::TEST_EMAIL,
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => self::TEST_DIRECCION,
            'documento_identidad' => \Illuminate\Http\UploadedFile::fake()->create('documento.jpg', 1000), // No es PDF
            'acepto_privacidad' => '1',
            'acepto_terminos' => '1',
        ];

        $request = new InscripcionComplementarioRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('PDF', $validator->errors()->first('documento_identidad'));
    }

    #[Test]
    public function valida_tamano_maximo_documento()
    {
        $pais = Pais::create(['pais' => 'Colombia', 'status' => 1]);
        $departamento = Departamento::create(['departamento' => 'Cundinamarca', 'pais_id' => $pais->id, 'status' => 1]);
        $municipio = Municipio::create(['municipio' => self::TEST_MUNICIPIO, 'departamento_id' => $departamento->id, 'status' => 1]);

        $data = [
            'tipo_documento' => 1,
            'numero_documento' => self::TEST_NUMERO_DOCUMENTO,
            'primer_nombre' => 'Juan',
            'primer_apellido' => self::TEST_APELLIDO,
            'fecha_nacimiento' => self::TEST_FECHA_NACIMIENTO,
            'genero' => 1,
            'celular' => self::TEST_CELULAR,
            'email' => self::TEST_EMAIL,
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => self::TEST_DIRECCION,
            'documento_identidad' => \Illuminate\Http\UploadedFile::fake()->create('documento.pdf', 6000), // Mayor a 5MB
            'acepto_privacidad' => '1',
            'acepto_terminos' => '1',
        ];

        $validator = Validator::make($data, (new InscripcionComplementarioRequest())->rules());

        $this->assertTrue($validator->fails());
    }

    #[Test]
    public function valida_aceptacion_terminos_y_privacidad()
    {
        $pais = Pais::create(['pais' => 'Colombia', 'status' => 1]);
        $departamento = Departamento::create(['departamento' => 'Cundinamarca', 'pais_id' => $pais->id, 'status' => 1]);
        $municipio = Municipio::create(['municipio' => self::TEST_MUNICIPIO, 'departamento_id' => $departamento->id, 'status' => 1]);

        $data = [
            'tipo_documento' => 1,
            'numero_documento' => self::TEST_NUMERO_DOCUMENTO,
            'primer_nombre' => 'Juan',
            'primer_apellido' => self::TEST_APELLIDO,
            'fecha_nacimiento' => self::TEST_FECHA_NACIMIENTO,
            'genero' => 1,
            'celular' => self::TEST_CELULAR,
            'email' => self::TEST_EMAIL,
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => self::TEST_DIRECCION,
            'documento_identidad' => \Illuminate\Http\UploadedFile::fake()->create('documento.pdf', 1000),
            // Sin aceptar términos
        ];

        $validator = Validator::make($data, (new InscripcionComplementarioRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('acepto_privacidad', $validator->errors()->toArray());
        $this->assertArrayHasKey('acepto_terminos', $validator->errors()->toArray());
    }
}
