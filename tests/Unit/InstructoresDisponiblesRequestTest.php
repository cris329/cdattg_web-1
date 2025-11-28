<?php

namespace Tests\Unit;

use App\Http\Requests\InstructoresDisponiblesRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstructoresDisponiblesRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_datos_validos(): void
    {
        $datos = [
            'fecha_inicio' => now()->format('Y-m-d'),
            'fecha_fin' => now()->addDays(30)->format('Y-m-d'),
        ];

        $request = new InstructoresDisponiblesRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_fecha_fin_anterior_a_fecha_inicio(): void
    {
        $datos = [
            'fecha_inicio' => now()->format('Y-m-d'),
            'fecha_fin' => now()->subDays(1)->format('Y-m-d'),
        ];

        $request = new InstructoresDisponiblesRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

