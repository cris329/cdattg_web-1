<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateAmbienteRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateAmbienteRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $datos = [
            'piso_id' => 1,
            'title' => 'Ambiente Actualizado',
            'status' => true,
        ];

        $request = new UpdateAmbienteRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_status_faltante(): void
    {
        $datos = [
            'piso_id' => 1,
            'title' => 'Ambiente Test',
        ];

        $request = new UpdateAmbienteRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

