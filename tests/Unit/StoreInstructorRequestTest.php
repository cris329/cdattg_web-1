<?php

namespace Tests\Unit;

use App\Http\Requests\StoreInstructorRequest;
use App\Models\Regional;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreInstructorRequestTest extends TestCase
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
        $regional = Regional::factory()->create();
        $parametro = \App\Models\Parametro::first();

        $datos = [
            'tipo_documento' => $parametro->id,
            'numero_documento' => '1234567890',
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'fecha_de_nacimiento' => Carbon::now()->subYears(25)->format('Y-m-d'),
            'genero' => $parametro->id,
            'email' => 'instructor@example.com',
            'regional_id' => $regional->id,
        ];

        $request = new StoreInstructorRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_numero_documento_duplicado(): void
    {
        $datos = [
            'numero_documento' => '1234567890',
        ];

        $request = new StoreInstructorRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

