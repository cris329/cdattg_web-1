<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateGuiasAprendizajeRequest;
use App\Models\GuiasAprendizaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateGuiasAprendizajeRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $guia = GuiasAprendizaje::factory()->create();

        $datos = [
            'codigo' => 'GUIA004',
            'nombre' => 'Guía Actualizada',
            'resultados_aprendizaje' => [1],
        ];

        $request = new UpdateGuiasAprendizajeRequest;
        $rules = $request->rules();

        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->passes());
    }
}

