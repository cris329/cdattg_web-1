<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateGuiaAprendizajeRequest;
use App\Models\GuiasAprendizaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateGuiaAprendizajeRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $guia = GuiasAprendizaje::factory()->create();

        $datos = [
            'codigo' => 'GUIA002',
            'nombre' => 'Guía Actualizada',
        ];

        $request = new UpdateGuiaAprendizajeRequest;
        $rules = $request->rules();

        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->passes());
    }
}

