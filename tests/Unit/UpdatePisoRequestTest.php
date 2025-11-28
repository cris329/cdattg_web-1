<?php

namespace Tests\Unit;

use App\Http\Requests\UpdatePisoRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdatePisoRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $datos = [
            'piso' => 'Piso 2',
            'bloque_id' => 1,
            'status' => true,
        ];

        $request = new UpdatePisoRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }
}

