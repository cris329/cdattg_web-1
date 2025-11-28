<?php

namespace Tests\Unit;

use App\Http\Requests\CreateInstructorRequest;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateInstructorRequestTest extends TestCase
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
            \Database\Seeders\RegionalSeeder::class,
        ]);
    }

    #[Test]
    public function valida_datos_requeridos(): void
    {
        $rules = (new CreateInstructorRequest)->rules();

        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('persona_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('regional_id', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_anos_experiencia_rango(): void
    {
        $persona = Persona::factory()->create();
        $user = User::factory()->create(['persona_id' => $persona->id]);
        $regional = \App\Models\Regional::first();

        $rules = (new CreateInstructorRequest)->rules();

        $validator = Validator::make([
            'persona_id' => $persona->id,
            'regional_id' => $regional->id,
            'anos_experiencia' => 60, // Mayor al máximo
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('anos_experiencia', $validator->errors()->toArray());
    }
}
