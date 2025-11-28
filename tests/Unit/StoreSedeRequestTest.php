<?php

namespace Tests\Unit;

use App\Http\Requests\StoreSedeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreSedeRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $datos = [
            'sede' => 'Sede Test',
            'direccion' => 'Dirección Test',
            'municipio_id' => 1,
            'regional_id' => 1,
        ];

        $request = new StoreSedeRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_sede_faltante(): void
    {
        $datos = [
            'direccion' => 'Dirección Test',
            'municipio_id' => 1,
            'regional_id' => 1,
        ];

        $request = new StoreSedeRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }

    #[Test]
    public function rechaza_municipio_id_faltante(): void
    {
        $datos = [
            'sede' => 'Sede Test',
            'direccion' => 'Dirección Test',
            'regional_id' => 1,
        ];

        $request = new StoreSedeRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

