<?php

namespace Tests\Unit;

use App\Http\Requests\AsignarInstructoresRequest;
use App\Models\FichaCaracterizacion;
use App\Models\Instructor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AsignarInstructoresRequestTest extends TestCase
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
        $parametro = \App\Models\Parametro::first();

        $datos = [
            'instructores' => [
                [
                    'instructor_id' => $instructor->id,
                    'fecha_inicio' => now()->format('Y-m-d'),
                    'fecha_fin' => now()->addMonths(6)->format('Y-m-d'),
                    'dias_semana' => [$parametro->id],
                    'total_horas_instructor' => 100,
                ],
            ],
        ];

        $request = new AsignarInstructoresRequest;
        $rules = $request->rules();

        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_instructores_vacio(): void
    {
        $datos = [
            'instructores' => [],
        ];

        $request = new AsignarInstructoresRequest;
        $rules = $request->rules();

        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->fails());
    }
}

