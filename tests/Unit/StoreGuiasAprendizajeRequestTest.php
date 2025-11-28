<?php

namespace Tests\Unit;

use App\Http\Requests\StoreGuiasAprendizajeRequest;
use App\Models\ResultadosAprendizaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreGuiasAprendizajeRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);
    }

    #[Test]
    public function valida_datos_validos(): void
    {
        $resultado = ResultadosAprendizaje::factory()->create();

        $datos = [
            'codigo' => 'GUIA003',
            'nombre' => 'Guía Test',
            'resultados_aprendizaje' => [$resultado->id],
        ];

        $request = new StoreGuiasAprendizajeRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_resultados_aprendizaje_vacio(): void
    {
        $datos = [
            'codigo' => 'GUIA003',
            'nombre' => 'Guía Test',
            'resultados_aprendizaje' => [],
        ];

        $request = new StoreGuiasAprendizajeRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

