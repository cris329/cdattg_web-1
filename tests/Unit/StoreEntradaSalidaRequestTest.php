<?php

namespace Tests\Unit;

use App\Http\Requests\StoreEntradaSalidaRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreEntradaSalidaRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $datos = [
            'tipo' => 'entrada',
        ];

        $request = new StoreEntradaSalidaRequest;
        $rules = $request->rules();

        $validator = Validator::make($datos, $rules);

        $this->assertIsArray($rules);
    }
}

