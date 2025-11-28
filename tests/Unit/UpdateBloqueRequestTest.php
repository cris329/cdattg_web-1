<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateBloqueRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateBloqueRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $datos = [
            'bloque' => 'Bloque B',
            'sede_id' => 1,
            'status' => true,
        ];

        $request = new UpdateBloqueRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_status_faltante(): void
    {
        $datos = [
            'bloque' => 'Bloque B',
            'sede_id' => 1,
        ];

        $request = new UpdateBloqueRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

