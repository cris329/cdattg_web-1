<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateparametroRequest;
use App\Models\Parametro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateparametroRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $parametro = Parametro::factory()->create();

        $datos = [
            'name' => 'Parametro Actualizado',
            'status' => true,
        ];

        $request = new UpdateparametroRequest;
        $rules = $request->rules();

        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->passes());
    }
}

