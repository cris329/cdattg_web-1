<?php

namespace Tests\Unit\Complementarios;

use App\Http\Requests\Complementarios\StoreProgramaComplementarioRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreProgramaComplementarioRequestTest extends TestCase
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
        $datos = [
            'codigo' => 'COMP001',
            'nombre' => 'Programa Complementario Test',
            'duracion' => 40,
            'cupos' => 20,
            'estado' => 1,
            'modalidad_id' => 1,
            'jornada_id' => 1,
            'ambiente_id' => 1,
        ];

        $request = new StoreProgramaComplementarioRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_codigo_faltante(): void
    {
        $datos = [
            'nombre' => 'Programa Test',
        ];

        $request = new StoreProgramaComplementarioRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

