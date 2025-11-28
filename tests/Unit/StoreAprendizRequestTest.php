<?php

namespace Tests\Unit;

use App\Http\Requests\StoreAprendizRequest;
use App\Models\FichaCaracterizacion;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreAprendizRequestTest extends TestCase
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
        $persona = Persona::factory()->create();
        $ficha = FichaCaracterizacion::factory()->create();

        $datos = [
            'persona_id' => $persona->id,
            'ficha_caracterizacion_id' => $ficha->id,
            'estado' => true,
        ];

        $request = new StoreAprendizRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_persona_id_faltante(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();

        $datos = [
            'ficha_caracterizacion_id' => $ficha->id,
            'estado' => true,
        ];

        $request = new StoreAprendizRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('persona_id', $validator->errors()->toArray());
    }

    #[Test]
    public function rechaza_ficha_caracterizacion_id_faltante(): void
    {
        $persona = Persona::factory()->create();

        $datos = [
            'persona_id' => $persona->id,
            'estado' => true,
        ];

        $request = new StoreAprendizRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

