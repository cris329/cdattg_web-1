<?php

namespace Tests\Unit;

use App\Http\Requests\StoreevidenciasRequest;
use App\Models\ResultadosAprendizaje;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreevidenciasRequestTest extends TestCase
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
            'name' => 'Evidencia Test',
            'resultado_aprendizaje_id' => $resultado->id,
            'fecha_actividad' => Carbon::today()->format('Y-m-d'),
        ];

        $request = new StoreevidenciasRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_name_faltante(): void
    {
        $datos = [
            'resultado_aprendizaje_id' => 1,
            'fecha_actividad' => Carbon::today()->format('Y-m-d'),
        ];

        $request = new StoreevidenciasRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

