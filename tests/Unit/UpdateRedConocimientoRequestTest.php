<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateRedConocimientoRequest;
use App\Models\RedConocimiento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateRedConocimientoRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $redConocimiento = RedConocimiento::factory()->create();

        $datos = [
            'nombre' => 'Red Actualizada',
            'regionals_id' => null,
        ];

        $request = new UpdateRedConocimientoRequest;
        $request->setRouteResolver(function () use ($redConocimiento) {
            return new class($redConocimiento) {
                public function parameter($name) {
                    return $name === 'red_conocimiento' ? $this->redConocimiento : null;
                }
                public function __construct($redConocimiento) {
                    $this->redConocimiento = $redConocimiento;
                }
            };
        });

        $rules = $request->rules();
        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->passes());
    }
}

