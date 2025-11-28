<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateMunicipioRequest;
use App\Models\Municipio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateMunicipioRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $municipio = Municipio::factory()->create();

        $datos = [
            'municipio' => 'Municipio Actualizado',
            'status' => true,
        ];

        $request = new UpdateMunicipioRequest;
        $rules = $request->rules();

        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->passes());
    }
}

