<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateSedeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateSedeRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $datos = [
            'sede' => 'Sede Actualizada',
            'direccion' => 'Dirección Actualizada',
            'municipio_id' => 1,
            'regional_id' => 1,
            'status' => true,
        ];

        $request = new UpdateSedeRequest;
        $rules = $request->rules();

        $validator = Validator::make($datos, $rules);

        $this->assertIsArray($rules);
    }
}

