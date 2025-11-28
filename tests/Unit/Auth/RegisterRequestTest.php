<?php

namespace Tests\Unit\Auth;

use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Departamento;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegisterRequestTest extends TestCase
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
        $departamento = Departamento::first();
        $municipio = $departamento->municipios->first();

        $datos = [
            'tipo_documento' => 1,
            'numero_documento' => '1234567890',
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'fecha_nacimiento' => Carbon::now()->subYears(15)->format('Y-m-d'),
            'genero' => 1,
            'email' => 'test@example.com',
            'pais_id' => 1,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'caracterizacion_ids' => [1],
        ];

        $request = new RegisterRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_menor_de_14_anos(): void
    {
        $datos = [
            'fecha_nacimiento' => Carbon::now()->subYears(10)->format('Y-m-d'),
        ];

        $request = new RegisterRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

