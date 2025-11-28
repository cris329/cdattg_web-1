<?php

namespace Tests\Unit;

use App\Http\Requests\VerificarDisponibilidadRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VerificarDisponibilidadRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $datos = [
            'fecha_inicio' => now()->format('Y-m-d'),
            'fecha_fin' => now()->addDays(30)->format('Y-m-d'),
            'horas_semanales' => 20,
        ];

        $request = new VerificarDisponibilidadRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_horas_semanales_mayor_a_48(): void
    {
        $datos = [
            'fecha_inicio' => now()->format('Y-m-d'),
            'fecha_fin' => now()->addDays(30)->format('Y-m-d'),
            'horas_semanales' => 50,
        ];

        $request = new VerificarDisponibilidadRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

