<?php

namespace Tests\Unit;

use App\Http\Requests\StoreRegionalRequest;
use App\Models\Departamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreRegionalRequestTest extends TestCase
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
        $departamento = Departamento::first();

        $datos = [
            'nombre' => 'Regional Test',
            'departamento_id' => $departamento->id,
        ];

        $request = new StoreRegionalRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_nombre_faltante(): void
    {
        $departamento = Departamento::first();

        $datos = [
            'departamento_id' => $departamento->id,
        ];

        $request = new StoreRegionalRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

