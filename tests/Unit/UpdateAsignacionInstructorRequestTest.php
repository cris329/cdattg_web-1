<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateAsignacionInstructorRequest;
use App\Models\AsignacionInstructor;
use App\Models\Competencia;
use App\Models\FichaCaracterizacion;
use App\Models\Instructor;
use App\Models\ResultadosAprendizaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateAsignacionInstructorRequestTest extends TestCase
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
        $asignacion = AsignacionInstructor::factory()->create();
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

        $request = new UpdateAsignacionInstructorRequest;
        $rules = $request->rules();

        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->passes());
    }
}

