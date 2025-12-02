<?php

declare(strict_types=1);

namespace Tests\Complementarios\Unit\Requests;

use Tests\TestCase;
use App\Http\Requests\Complementarios\BuscarPersonaRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;

class BuscarPersonaRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_numero_documento_requerido(): void
    {
        $request = new BuscarPersonaRequest();
        $rules = $request->rules();

        $validator = Validator::make([], $rules, $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('numero_documento', $validator->errors()->toArray());
        $this->assertStringContainsString(
            'El campo numero documento es obligatorio.',
            $validator->errors()->first('numero_documento')
        );
    }

    #[Test]
    public function valida_numero_documento_debe_ser_string(): void
    {
        $request = new BuscarPersonaRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'numero_documento' => 1234567890, // Integer instead of string
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('numero_documento', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_numero_documento_maximo_20_caracteres(): void
    {
        $request = new BuscarPersonaRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'numero_documento' => str_repeat('1', 21), // 21 characters
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('numero_documento', $validator->errors()->toArray());
    }

    #[Test]
    public function acepta_numero_documento_valido(): void
    {
        $request = new BuscarPersonaRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'numero_documento' => '1234567890',
        ], $rules);

        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function acepta_numero_documento_con_20_caracteres(): void
    {
        $request = new BuscarPersonaRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'numero_documento' => str_repeat('1', 20), // Exactly 20 characters
        ], $rules);

        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function authorize_retorna_true(): void
    {
        $request = new BuscarPersonaRequest();

        $this->assertTrue($request->authorize());
    }

    // Note: The failedValidation() method behavior is tested indirectly
    // through the integration test in AspiranteComplementarioControllerTest
    // (buscar_persona_valida_numero_documento_requerido test).
    // Testing it directly would require reflection which violates encapsulation
    // and SonarQube best practices (php:S3011).

    #[Test]
    public function mensajes_personalizados_estan_definidos(): void
    {
        $request = new BuscarPersonaRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('numero_documento.required', $messages);
        $this->assertEquals(
            'El campo numero documento es obligatorio.',
            $messages['numero_documento.required']
        );
    }
}

