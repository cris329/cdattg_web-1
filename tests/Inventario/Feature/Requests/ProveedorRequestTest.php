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
        Proveedor::factory()->create(['nit' => '123456789']);
        $proveedor2 = Proveedor::factory()->create();

        $rules = $this->obtenerRulesParaUpdate($proveedor2);

        $this->validarYVerificarError(
            [
                'proveedor' => 'OTRO PROVEEDOR',
                'nit' => '123456789',
            ],
            $rules,
            'nit'
        );
    }

    #[Test]
    public function valida_unicidad_de_email_en_update(): void
    {
        Proveedor::factory()->create(['email' => 'test1@example.com']);
        $proveedor2 = Proveedor::factory()->create();

        $rules = $this->obtenerRulesParaUpdate($proveedor2);

        $this->validarYVerificarError(
            [
                'proveedor' => 'OTRO PROVEEDOR',
                'email' => 'test1@example.com',
            ],
            $rules,
            'email'
        );
    }

    #[Test]
    public function valida_formato_de_email(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            [
                'proveedor' => self::PROVEEDOR_TEST,
                'email' => 'email-invalido',
            ],
            $rules,
            'email'
        );
    }

    #[Test]
    public function valida_longitud_maxima_de_telefono(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            [
                'proveedor' => self::PROVEEDOR_TEST,
                'telefono' => '12345678901',
            ],
            $rules,
            'telefono'
        );
    }

    #[Test]
    public function valida_existencia_de_departamento(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            [
                'proveedor' => self::PROVEEDOR_TEST,
                'departamento_id' => 99999,
            ],
            $rules,
            'departamento_id'
        );
    }

    #[Test]
    public function valida_existencia_de_municipio(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            [
                'proveedor' => self::PROVEEDOR_TEST,
                'municipio_id' => 99999,
            ],
            $rules,
            'municipio_id'
        );
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
            'nit' => '123456789',
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
}

