<?php

declare(strict_types=1);

namespace Tests\Inventario\Feature\Request;

use Tests\TestCase;
use App\Http\Requests\Inventario\ContratoConvenioRequest;
use App\Models\Inventario\ContratoConvenio;
use App\Models\Inventario\Proveedor;
use App\Models\ParametroTema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;

class ContratoConvenioRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateDatabases();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\TemaSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\PersonaSeeder::class,
            \Database\Seeders\UsersSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
            \Database\Seeders\SedeSeeder::class,
            \Database\Seeders\BloqueSeeder::class,
            \Database\Seeders\PisoSeeder::class,
            \Database\Seeders\AmbienteSeeder::class,
        ]);
    }

    #[Test]
    public function valida_name_requerido_en_store(): void
    {
        $request = new ContratoConvenioRequest();
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_unicidad_de_name_en_store(): void
    {
        $contrato = ContratoConvenio::factory()->create(['name' => 'CONTRATO TEST']);

        $request = new ContratoConvenioRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'CONTRATO TEST',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_unicidad_de_codigo_en_store(): void
    {
        $contrato = ContratoConvenio::factory()->create(['codigo' => 'COD-001']);

        $request = new ContratoConvenioRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'NUEVO CONTRATO',
            'codigo' => 'COD-001',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('codigo', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_longitud_maxima_de_name(): void
    {
        $request = new ContratoConvenioRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => str_repeat('a', 256),
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_longitud_maxima_de_codigo(): void
    {
        $request = new ContratoConvenioRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'CONTRATO TEST',
            'codigo' => str_repeat('a', 101),
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('codigo', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_existencia_de_proveedor(): void
    {
        $request = new ContratoConvenioRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'CONTRATO TEST',
            'proveedor_id' => 99999,
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('proveedor_id', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_fecha_fin_debe_ser_posterior_o_igual_a_fecha_inicio(): void
    {
        $request = new ContratoConvenioRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'CONTRATO TEST',
            'fecha_inicio' => '2025-12-31',
            'fecha_fin' => '2025-01-01',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('fecha_fin', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_estado_id_requerido(): void
    {
        $request = new ContratoConvenioRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'CONTRATO TEST',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('estado_id', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_estado_id_debe_existir(): void
    {
        $request = new ContratoConvenioRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'CONTRATO TEST',
            'estado_id' => 99999,
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('estado_id', $validator->errors()->toArray());
    }

    #[Test]
    public function acepta_datos_validos_para_store(): void
    {
        $proveedor = Proveedor::factory()->create();
        $estado = ParametroTema::query()->inRandomOrder()->first();

        $request = new ContratoConvenioRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'CONTRATO VALIDO',
            'codigo' => 'COD-001',
            'proveedor_id' => $proveedor->id,
            'fecha_inicio' => '2025-01-01',
            'fecha_fin' => '2025-12-31',
            'estado_id' => $estado->id,
        ], $rules);

        $this->assertFalse($validator->fails());
    }
}

