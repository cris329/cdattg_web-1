<?php

namespace Tests\Unit;

use App\Http\Requests\StorePisoRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StorePisoRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $datos = [
            'piso' => 'Piso 1',
            'bloque_id' => 1,
        ];

        $request = new StorePisoRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_piso_faltante(): void
    {
        $datos = [
            'bloque_id' => 1,
        ];

        $request = new StorePisoRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

