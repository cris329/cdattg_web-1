<?php

declare(strict_types=1);

namespace Tests\Inventario\Feature\Request;

use Tests\TestCase;
use App\Http\Requests\Inventario\ProveedorRequest;
use App\Models\Inventario\Proveedor;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\ParametroTema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;

class ProveedorRequestTest extends TestCase
{
    use RefreshDatabase;
    
    private const PROVEEDOR_TEST = 'PROVEEDOR TEST';
    private const NIT_TEST = '123456789';
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
        $request = new ProveedorRequest();
        return $request->rules();
    }

    private function obtenerRulesParaUpdate(Proveedor $proveedor): array
    {
        $request = new ProveedorRequest();
        $request->setMethod('PUT');

        $resolver = $this->crearRouteResolver($proveedor);
        $request->setRouteResolver(fn() => $resolver);

        return $request->rules();
    }

    private function crearRouteResolver(Proveedor $proveedor): object
    {
        return new class($proveedor) {
            private Proveedor $proveedor;
            
            public function __construct($proveedor) {
                $this->proveedor = $proveedor;
            }
            
            public function parameter(string $name): ?int
            {
                if ($name === 'proveedor') {
                    return $this->proveedor->id;
                }
                return null;
            }
        };
    }

    private function validarYVerificarError(array $data, array $rules, string $campoEsperado): void
    {
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey($campoEsperado, $validator->errors()->toArray());
    }

    #[Test]
    public function valida_proveedor_requerido_en_store(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError([], $rules, 'proveedor');
    }

    #[Test]
    public function valida_unicidad_de_proveedor_en_store(): void
    {
        Proveedor::factory()->create(['proveedor' => self::PROVEEDOR_TEST]);

        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            ['proveedor' => self::PROVEEDOR_TEST],
            $rules,
            'proveedor'
        );
    }

    #[Test]
    public function valida_unicidad_de_nit_en_update(): void
    {
        $this->validarUnicidadEnUpdate('nit', self::NIT_TEST, ['proveedor' => 'OTRO PROVEEDOR']);
    }

    #[Test]
    public function valida_unicidad_de_email_en_update(): void
    {
        $this->validarUnicidadEnUpdate('email', 'test1@example.com', ['proveedor' => 'OTRO PROVEEDOR']);
    }

    #[Test]
    public function valida_formato_de_email(): void
    {
        $this->validarCampoConDatosBase('email', 'email-invalido');
    }

    #[Test]
    public function valida_longitud_maxima_de_telefono(): void
    {
        $this->validarCampoConDatosBase('telefono', '12345678901');
    }

    #[Test]
    public function valida_existencia_de_departamento(): void
    {
        $this->validarExistenciaRelacion('departamento_id', 99999);
    }

    #[Test]
    public function valida_existencia_de_municipio(): void
    {
        $this->validarExistenciaRelacion('municipio_id', 99999);
    }

    #[Test]
    public function acepta_datos_validos_para_store(): void
    {
        $departamento = Departamento::query()->inRandomOrder()->first();
        $municipio = Municipio::query()->where('departamento_id', $departamento->id)->inRandomOrder()->firstOrFail();
        $estado = ParametroTema::query()->inRandomOrder()->first();

        $rules = $this->obtenerRules();

        $validator = Validator::make([
            'proveedor' => 'PROVEEDOR VALIDO',
            'nit' => self::NIT_TEST,
            'email' => 'proveedor@example.com',
            'telefono' => '1234567890',
            'direccion' => 'Dirección del proveedor',
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'contacto' => 'Contacto del proveedor',
            'estado_id' => $estado->id,
        ], $rules);

        $this->assertFalse($validator->fails());
    }

    /**
     * Validate uniqueness in update scenario.
     * Creates two providers, the first with the conflicting value, and validates
     * that the second cannot use the same value.
     */
    private function validarUnicidadEnUpdate(string $campo, string $valor, array $datosAdicionales = []): void
    {
        Proveedor::factory()->create([$campo => $valor]);
        $proveedor2 = Proveedor::factory()->create();

        $rules = $this->obtenerRulesParaUpdate($proveedor2);
        $datos = array_merge([$campo => $valor], $datosAdicionales);

        $this->validarYVerificarError($datos, $rules, $campo);
    }

    /**
     * Validate existence of a related entity.
     */
    private function validarExistenciaRelacion(string $campo, int $idInexistente): void
    {
        $rules = $this->obtenerRules();
        $datos = [
            'proveedor' => self::PROVEEDOR_TEST,
            $campo => $idInexistente,
        ];

        $this->validarYVerificarError($datos, $rules, $campo);
    }

    /**
     * Validate a field with base data (proveedor required).
     */
    private function validarCampoConDatosBase(string $campo, mixed $valorInvalido): void
    {
        $rules = $this->obtenerRules();
        $datos = [
            'proveedor' => self::PROVEEDOR_TEST,
            $campo => $valorInvalido,
        ];

        $this->validarYVerificarError($datos, $rules, $campo);
    }
}

