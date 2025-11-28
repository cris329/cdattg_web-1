<?php

namespace Tests\Unit;

use App\Http\Requests\StoreProgramaFormacionRequest;
use App\Models\RedConocimiento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreProgramaFormacionRequestTest extends TestCase
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
        $redConocimiento = RedConocimiento::factory()->create();
        $parametro = \App\Models\Parametro::first();

        $datos = [
            'codigo' => 'PROG001',
            'nombre' => 'Programa Test',
            'red_conocimiento_id' => $redConocimiento->id,
            'nivel_formacion_id' => $parametro->id,
            'horas_totales' => 2000,
            'horas_etapa_lectiva' => 1000,
            'horas_etapa_productiva' => 1000,
        ];

        $request = new StoreProgramaFormacionRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_suma_horas_desigual(): void
    {
        $redConocimiento = RedConocimiento::factory()->create();
        $parametro = \App\Models\Parametro::first();

        $datos = [
            'codigo' => 'PROG001',
            'nombre' => 'Programa Test',
            'red_conocimiento_id' => $redConocimiento->id,
            'nivel_formacion_id' => $parametro->id,
            'horas_totales' => 2000,
            'horas_etapa_lectiva' => 1000,
            'horas_etapa_productiva' => 500,
        ];

        $request = new StoreProgramaFormacionRequest;
        $validator = Validator::make($datos, $request->rules());
        $request->withValidator($validator);

        $this->assertTrue($validator->fails());
    }

    #[Test]
    public function rechaza_codigo_faltante(): void
    {
        $datos = [
            'nombre' => 'Programa Test',
        ];

        $request = new StoreProgramaFormacionRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

