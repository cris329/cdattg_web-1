<?php

namespace Tests\Unit;

use App\Http\Requests\InstructorRequest;
use App\Models\Persona;
use App\Models\Regional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstructorRequestTest extends TestCase
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
        ]);
    }

    #[Test]
    public function valida_datos_validos(): void
    {
        $persona = Persona::factory()->create();
        $regional = Regional::factory()->create();

        $datos = [
            'persona_id' => $persona->id,
            'regional_id' => $regional->id,
            'status' => true,
            'especialidades' => [
                'principal' => 'Tecnología',
                'secundarias' => ['Programación', 'Base de datos'],
            ],
            'anos_experiencia' => 5,
        ];

        $request = new InstructorRequest;
        $rules = $request->rules();

        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_persona_id_faltante(): void
    {
        $datos = [
            'regional_id' => 1,
        ];

        $request = new InstructorRequest;
        $rules = $request->rules();

        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->fails());
    }
}
