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

    private const ID_INEXISTENTE = 99999;
    private const LONGITUD_MAX_NAME = 256;
    private const LONGITUD_MAX_CODIGO = 101;
    private const FECHA_INICIO = '2025-01-01';
    private const FECHA_FIN = '2025-12-31';
    private const FECHA_FIN_INVALIDA = '2025-01-01';
    private const FECHA_INICIO_INVALIDA = '2025-12-31';
    private const CONTRATO_TEST = 'CONTRATO TEST';

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

    private function obtenerRules(): array
    {
        $request = new ContratoConvenioRequest();
        return $request->rules();
    }

    private function validarYVerificarError(array $data, array $rules, string $campoEsperado): void
    {
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey($campoEsperado, $validator->errors()->toArray());
    }

    #[Test]
    public function valida_name_requerido_en_store(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError([], $rules, 'name');
    }

    #[Test]
    public function valida_unicidad_de_name_en_store(): void
    {
        ContratoConvenio::factory()->create(['name' => self::CONTRATO_TEST]);

        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            ['name' => self::CONTRATO_TEST],
            $rules,
            'name'
        );
    }

    #[Test]
    public function valida_unicidad_de_codigo_en_store(): void
    {
        ContratoConvenio::factory()->create(['codigo' => 'COD-001']);

        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            [
                'name' => 'NUEVO CONTRATO',
                'codigo' => 'COD-001',
            ],
            $rules,
            'codigo'
        );
    }

    #[Test]
    public function valida_longitud_maxima_de_name(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            ['name' => str_repeat('a', self::LONGITUD_MAX_NAME)],
            $rules,
            'name'
        );
    }

    #[Test]
    public function valida_longitud_maxima_de_codigo(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            [
                'name' => self::CONTRATO_TEST,
                'codigo' => str_repeat('a', self::LONGITUD_MAX_CODIGO),
            ],
            $rules,
            'codigo'
        );
    }

    #[Test]
    public function valida_existencia_de_proveedor(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            [
                'name' => self::CONTRATO_TEST,
                'proveedor_id' => self::ID_INEXISTENTE,
            ],
            $rules,
            'proveedor_id'
        );
    }

    #[Test]
    public function valida_fecha_fin_debe_ser_posterior_o_igual_a_fecha_inicio(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            [
                'name' => self::CONTRATO_TEST,
                'fecha_inicio' => self::FECHA_INICIO_INVALIDA,
                'fecha_fin' => self::FECHA_FIN_INVALIDA,
            ],
            $rules,
            'fecha_fin'
        );
    }

    #[Test]
    public function valida_estado_id_requerido(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            ['name' => self::CONTRATO_TEST],
            $rules,
            'estado_id'
        );
    }

    #[Test]
    public function valida_estado_id_debe_existir(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            [
                'name' => self::CONTRATO_TEST,
                'estado_id' => self::ID_INEXISTENTE,
            ],
            $rules,
            'estado_id'
        );
    }

    #[Test]
    public function acepta_datos_validos_para_store(): void
    {
        $proveedor = Proveedor::factory()->create();
        $estado = ParametroTema::query()->inRandomOrder()->first();

        $rules = $this->obtenerRules();

        $validator = Validator::make([
            'name' => 'CONTRATO VALIDO',
            'codigo' => 'COD-001',
            'proveedor_id' => $proveedor->id,
            'fecha_inicio' => self::FECHA_INICIO,
            'fecha_fin' => self::FECHA_FIN,
            'estado_id' => $estado->id,
        ], $rules);

        $this->assertFalse($validator->fails());
    }
}

