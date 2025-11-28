<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateevidenciasRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateevidenciasRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $datos = [
            'name' => 'Evidencia Actualizada',
        ];

        $request = new UpdateevidenciasRequest;
        $rules = $request->rules();

        $validator = Validator::make($datos, $rules);

        $this->assertIsArray($rules);
    }
}

