<?php

declare(strict_types=1);

namespace Tests\Inventario\Feature\Request;

use Tests\TestCase;
use App\Http\Requests\Inventario\CarritoRequest;
use App\Models\Inventario\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;

class CarritoRequestTest extends TestCase
{
    use RefreshDatabase;

    private const ROUTE_ACTUALIZAR = 'inventario.carrito.actualizar';
    private const ID_INEXISTENTE = 99999;
    private const CANTIDAD_VALIDA = 5;
    private const CANTIDAD_INVALIDA = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateDatabases();

        // Producto necesita: Ambiente → Piso → Bloque → Sede → Regional
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
        $request = new CarritoRequest();
        return $request->rules();
    }

    private function obtenerRulesParaActualizar(): array
    {
        $request = new CarritoRequest();
        $rutaActualizar = self::ROUTE_ACTUALIZAR;
        $request->setRouteResolver(function () use ($rutaActualizar) {
            return new class($rutaActualizar) {
                private string $ruta;
                
                public function __construct(string $ruta) {
                    $this->ruta = $ruta;
                }
                
                public function named(...$patterns): bool {
                    return in_array($this->ruta, $patterns);
                }
            };
        });
        return $request->rules();
    }

    private function validarYVerificarError(array $data, array $rules, string $campoEsperado): void
    {
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey($campoEsperado, $validator->errors()->toArray());
    }

    #[Test]
    public function valida_cantidad_requerida_para_actualizar(): void
    {
        $rules = $this->obtenerRulesParaActualizar();

        $this->validarYVerificarError([], $rules, 'cantidad');
    }

    #[Test]
    public function valida_cantidad_debe_ser_integer_para_actualizar(): void
    {
        $rules = $this->obtenerRulesParaActualizar();

        $this->validarYVerificarError(
            ['cantidad' => 'no es numero'],
            $rules,
            'cantidad'
        );
    }

    #[Test]
    public function valida_cantidad_minima_para_actualizar(): void
    {
        $rules = $this->obtenerRulesParaActualizar();

        $this->validarYVerificarError(
            ['cantidad' => self::CANTIDAD_INVALIDA],
            $rules,
            'cantidad'
        );
    }

    #[Test]
    public function valida_items_requerido_para_agregar(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError([], $rules, 'items');
    }

    #[Test]
    public function valida_items_debe_ser_array(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            ['items' => 'no es array'],
            $rules,
            'items'
        );
    }

    #[Test]
    public function valida_items_debe_tener_producto_id(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            ['items' => [['cantidad' => 1]]],
            $rules,
            'items.0.producto_id'
        );
    }

    #[Test]
    public function valida_producto_id_debe_existir(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            [
                'items' => [
                    [
                        'producto_id' => self::ID_INEXISTENTE,
                        'cantidad' => 1,
                    ],
                ],
            ],
            $rules,
            'items.0.producto_id'
        );
    }

    #[Test]
    public function valida_items_debe_tener_cantidad(): void
    {
        $producto = Producto::factory()->create();
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            ['items' => [['producto_id' => $producto->id]]],
            $rules,
            'items.0.cantidad'
        );
    }

    #[Test]
    public function valida_cantidad_minima_en_items(): void
    {
        $producto = Producto::factory()->create();
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            [
                'items' => [
                    [
                        'producto_id' => $producto->id,
                        'cantidad' => self::CANTIDAD_INVALIDA,
                    ],
                ],
            ],
            $rules,
            'items.0.cantidad'
        );
    }

    #[Test]
    public function acepta_datos_validos_para_actualizar(): void
    {
        $rules = $this->obtenerRulesParaActualizar();

        $validator = Validator::make(['cantidad' => self::CANTIDAD_VALIDA], $rules);

        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function acepta_datos_validos_para_agregar(): void
    {
        $producto = Producto::factory()->create();
        $rules = $this->obtenerRules();

        $validator = Validator::make([
            'items' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => self::CANTIDAD_VALIDA,
                ],
            ],
        ], $rules);

        $this->assertFalse($validator->fails());
    }
}

