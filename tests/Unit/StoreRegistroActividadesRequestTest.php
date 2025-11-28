<?php

namespace Tests\Unit;

use App\Http\Requests\StoreRegistroActividadesRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreRegistroActividadesRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $datos = [
            'nombre' => 'Actividad Test',
            'fecha_evidencia' => Carbon::today()->format('Y-m-d'),
        ];

        $request = new StoreRegistroActividadesRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_nombre_faltante(): void
    {
        $datos = [
            'fecha_evidencia' => Carbon::today()->format('Y-m-d'),
        ];

        $request = new StoreRegistroActividadesRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

