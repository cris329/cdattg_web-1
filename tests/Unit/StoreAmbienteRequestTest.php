<?php

namespace Tests\Unit;

use App\Http\Requests\StoreAmbienteRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreAmbienteRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $datos = [
            'title' => 'Ambiente Test',
            'piso_id' => 1,
        ];

        $request = new StoreAmbienteRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_title_faltante(): void
    {
        $datos = [
            'piso_id' => 1,
        ];

        $request = new StoreAmbienteRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }

    #[Test]
    public function rechaza_piso_id_faltante(): void
    {
        $datos = [
            'title' => 'Ambiente Test',
        ];

        $request = new StoreAmbienteRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

