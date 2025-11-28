<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateTemaRequest;
use App\Models\Tema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateTemaRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $tema = Tema::factory()->create();

        $datos = [
            'name' => 'Tema Actualizado',
            'status' => true,
        ];

        $request = new UpdateTemaRequest;
        $rules = $request->rules();

        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->passes());
    }
}

