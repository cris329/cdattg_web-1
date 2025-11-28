<?php

namespace Tests\Unit;

use App\Http\Requests\StoreCompetenciaRequest;
use App\Models\ProgramaFormacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreCompetenciaRequestTest extends TestCase
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

        $datos = [
            'descripcion' => 'Descripción de la competencia',
            'codigo' => 'COMP001',
            'nombre' => 'Competencia Test',
            'duracion' => 100,
            'programas' => [$programa->id],
        ];

        $request = new StoreCompetenciaRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_codigo_faltante(): void
    {
        $datos = [
            'descripcion' => 'Descripción',
            'nombre' => 'Competencia Test',
            'duracion' => 100,
            'programas' => [1],
        ];

        $request = new StoreCompetenciaRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('codigo', $validator->errors()->toArray());
    }

    #[Test]
    public function rechaza_programas_vacio(): void
    {
        $datos = [
            'descripcion' => 'Descripción',
            'codigo' => 'COMP001',
            'nombre' => 'Competencia Test',
            'duracion' => 100,
            'programas' => [],
        ];

        $request = new StoreCompetenciaRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

