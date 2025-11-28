<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateRegistroActividadesRequest;
use App\Models\Evidencias;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateRegistroActividadesRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $evidencia = Evidencias::factory()->create();

        $datos = [
            'nombre' => 'Actividad Actualizada',
            'fecha_evidencia' => Carbon::today()->format('Y-m-d'),
        ];

        $request = new UpdateRegistroActividadesRequest;
        $rules = $request->rules();

        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->passes());
    }
}

