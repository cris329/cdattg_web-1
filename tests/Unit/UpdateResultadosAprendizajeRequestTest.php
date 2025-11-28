<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateResultadosAprendizajeRequest;
use App\Models\ResultadosAprendizaje;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateResultadosAprendizajeRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $resultado = ResultadosAprendizaje::factory()->create();

        $datos = [
            'codigo' => 'RAP002',
            'nombre' => 'Resultado Actualizado',
            'duracion' => 150,
            'fecha_inicio' => Carbon::today()->format('Y-m-d'),
            'fecha_fin' => Carbon::today()->addMonths(6)->format('Y-m-d'),
        ];

        $request = new UpdateResultadosAprendizajeRequest;
        $rules = $request->rules();

        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->passes());
    }
}

