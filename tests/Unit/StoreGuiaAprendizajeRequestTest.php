<?php

namespace Tests\Unit;

use App\Http\Requests\StoreGuiaAprendizajeRequest;
use App\Models\ResultadosAprendizaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreGuiaAprendizajeRequestTest extends TestCase
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
            'codigo' => 'GUIA001',
            'nombre' => 'Guía Test',
            'resultados_aprendizaje' => [$resultado->id],
        ];

        $request = new StoreGuiaAprendizajeRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_codigo_faltante(): void
    {
        $datos = [
            'nombre' => 'Guía Test',
        ];

        $request = new StoreGuiaAprendizajeRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

