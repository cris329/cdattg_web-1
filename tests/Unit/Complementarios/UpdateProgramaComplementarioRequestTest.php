<?php

namespace Tests\Complementarios\Unit\Requests;

use App\Http\Requests\Complementarios\UpdateProgramaComplementarioRequest;
use App\Models\Complementarios\ComplementarioOfertado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateProgramaComplementarioRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        $datos = [
            'codigo' => 'COMP002',
            'nombre' => 'Programa Actualizado',
            'duracion' => 50,
            'cupos' => 25,
            'estado' => 1,
            'modalidad_id' => 1,
            'jornada_id' => 1,
            'ambiente_id' => 1,
        ];

        $request = new UpdateProgramaComplementarioRequest;
        $rules = $request->rules();

        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->passes());
    }
}

