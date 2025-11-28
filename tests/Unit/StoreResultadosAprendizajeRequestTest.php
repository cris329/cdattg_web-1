<?php

namespace Tests\Unit;

use App\Http\Requests\StoreResultadosAprendizajeRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreResultadosAprendizajeRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $datos = [
            'codigo' => 'RAP001',
            'nombre' => 'Resultado Test',
            'duracion' => 100,
            'fecha_inicio' => Carbon::today()->format('Y-m-d'),
            'fecha_fin' => Carbon::today()->addMonths(6)->format('Y-m-d'),
            'status' => true,
        ];

        $request = new StoreResultadosAprendizajeRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_fecha_inicio_posterior_a_fecha_fin(): void
    {
        $datos = [
            'codigo' => 'RAP001',
            'nombre' => 'Resultado Test',
            'duracion' => 100,
            'fecha_inicio' => Carbon::today()->addMonths(6)->format('Y-m-d'),
            'fecha_fin' => Carbon::today()->format('Y-m-d'),
        ];

        $request = new StoreResultadosAprendizajeRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

