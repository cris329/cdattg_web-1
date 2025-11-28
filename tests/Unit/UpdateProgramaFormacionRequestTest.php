<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateProgramaFormacionRequest;
use App\Models\ProgramaFormacion;
use App\Models\RedConocimiento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateProgramaFormacionRequestTest extends TestCase
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
        $programa = ProgramaFormacion::factory()->create();
        $redConocimiento = RedConocimiento::factory()->create();
        $parametro = \App\Models\Parametro::first();

        $datos = [
            'codigo' => 'PROG002',
            'nombre' => 'Programa Actualizado',
            'red_conocimiento_id' => $redConocimiento->id,
            'nivel_formacion_id' => $parametro->id,
            'horas_totales' => 3000,
            'horas_etapa_lectiva' => 1500,
            'horas_etapa_productiva' => 1500,
        ];

        $request = new UpdateProgramaFormacionRequest;
        $rules = $request->rules();
        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->passes());
    }
}

