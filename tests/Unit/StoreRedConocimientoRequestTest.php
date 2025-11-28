<?php

namespace Tests\Unit;

use App\Http\Requests\StoreRedConocimientoRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreRedConocimientoRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $datos = [
            'nombre' => 'Red Test',
            'regionals_id' => null,
        ];

        $request = new StoreRedConocimientoRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_nombre_faltante(): void
    {
        $datos = [
            'regionals_id' => 1,
        ];

        $request = new StoreRedConocimientoRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

