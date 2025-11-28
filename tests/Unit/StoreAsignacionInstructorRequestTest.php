<?php

namespace Tests\Unit;

use App\Http\Requests\StoreAsignacionInstructorRequest;
use App\Models\Competencia;
use App\Models\FichaCaracterizacion;
use App\Models\Instructor;
use App\Models\ResultadosAprendizaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreAsignacionInstructorRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
        ]);
    }

    #[Test]
    public function valida_datos_validos(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        $instructor = Instructor::factory()->create();
        $competencia = Competencia::factory()->create();
        $resultado = ResultadosAprendizaje::factory()->create();

        $datos = [
            'ficha_id' => $ficha->id,
            'instructor_id' => $instructor->id,
            'competencia_id' => $competencia->id,
            'resultados' => [$resultado->id],
        ];

        $request = new StoreAsignacionInstructorRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_resultados_vacio(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        $instructor = Instructor::factory()->create();
        $competencia = Competencia::factory()->create();

        $datos = [
            'ficha_id' => $ficha->id,
            'instructor_id' => $instructor->id,
            'competencia_id' => $competencia->id,
            'resultados' => [],
        ];

        $request = new StoreAsignacionInstructorRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

