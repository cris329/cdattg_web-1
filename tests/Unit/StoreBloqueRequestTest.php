<?php

namespace Tests\Unit;

use App\Http\Requests\StoreBloqueRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreBloqueRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $datos = [
            'bloque' => 'Bloque A',
            'sede_id' => 1,
        ];

        $request = new StoreBloqueRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_bloque_faltante(): void
    {
        $datos = [
            'sede_id' => 1,
        ];

        $request = new StoreBloqueRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

