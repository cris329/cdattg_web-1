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
        $this->ejecutarSeedersNecesarios();
    }

    private function ejecutarSeedersNecesarios(): void
    {
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
        $this->validarCampoRequeridoEnActualizar('cantidad');
    }

    #[Test]
    public function valida_cantidad_debe_ser_integer_para_actualizar(): void
    {
        $this->validarTipoCampoEnActualizar('cantidad', 'no es numero');
    }

    #[Test]
    public function valida_cantidad_minima_para_actualizar(): void
    {
        $this->validarCampoEnActualizar('cantidad', self::CANTIDAD_INVALIDA);
    }

    #[Test]
    public function valida_items_requerido_para_agregar(): void
    {
        $this->validarCampoRequeridoEnAgregar('items');
    }

    #[Test]
    public function valida_items_debe_ser_array(): void
    {
        $this->validarTipoCampoEnAgregar('items', 'no es array');
    }

    #[Test]
    public function valida_items_debe_tener_producto_id(): void
    {
        $this->validarItemSinCampo('producto_id', ['cantidad' => 1]);
    }

    #[Test]
    public function valida_producto_id_debe_existir(): void
    {
        $this->validarItemConDatos('items.0.producto_id', [
            'producto_id' => self::ID_INEXISTENTE,
            'cantidad' => 1,
        ]);
    }

    #[Test]
    public function valida_items_debe_tener_cantidad(): void
    {
        $producto = Producto::factory()->create();
        $this->validarItemSinCampo('cantidad', ['producto_id' => $producto->id]);
    }

    #[Test]
    public function valida_cantidad_minima_en_items(): void
    {
        $producto = Producto::factory()->create();
        $this->validarItemConDatos('items.0.cantidad', [
            'producto_id' => $producto->id,
            'cantidad' => self::CANTIDAD_INVALIDA,
        ]);
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
        $datos = [
            'items' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => self::CANTIDAD_VALIDA,
                ],
            ],
        ];
        $validator = Validator::make($datos, $rules);

        $this->assertFalse($validator->fails());
    }

    /**
     * Validate required field in specified context.
     */
    private function validarCampoRequerido(string $campo, bool $esActualizar = false): void
    {
        $rules = $esActualizar ? $this->obtenerRulesParaActualizar() : $this->obtenerRules();
        $this->validarYVerificarError([], $rules, $campo);
    }

    /**
     * Validate field type in specified context.
     */
    private function validarTipoCampo(string $campo, mixed $valorInvalido, bool $esActualizar = false): void
    {
        $rules = $esActualizar ? $this->obtenerRulesParaActualizar() : $this->obtenerRules();
        $this->validarYVerificarError([$campo => $valorInvalido], $rules, $campo);
    }

    /**
     * Validate field validation rules (min, max, etc.) in update context.
     */
    private function validarCampoEnActualizar(string $campo, mixed $valorInvalido): void
    {
        $rules = $this->obtenerRulesParaActualizar();
        $data = [$campo => $valorInvalido];
        $validator = Validator::make($data, $rules);
        
        $this->assertTrue($validator->fails(), "Validation should fail for field {$campo}");
        
        $errorArray = $validator->errors()->toArray();
        $this->assertArrayHasKey($campo, $errorArray, "Field {$campo} must have validation errors");
    }

    private function validarCampoRequeridoEnActualizar(string $campo): void
    {
        $this->validarCampoRequerido($campo, true);
    }

    private function validarTipoCampoEnActualizar(string $campo, mixed $valorInvalido): void
    {
        $this->validarTipoCampo($campo, $valorInvalido, true);
    }

    private function validarCampoRequeridoEnAgregar(string $campo): void
    {
        $this->validarCampoRequerido($campo, false);
    }

    private function validarTipoCampoEnAgregar(string $campo, mixed $valorInvalido): void
    {
        $this->validarTipoCampo($campo, $valorInvalido, false);
    }

    /**
     * Validate item without a specific field.
     */
    private function validarItemSinCampo(string $campoFaltante, array $datosItem): void
    {
        $rules = $this->obtenerRules();
        $datos = ['items' => [$datosItem]];
        $this->validarYVerificarError($datos, $rules, "items.0.{$campoFaltante}");
    }

    /**
     * Validate item with specific data.
     */
    private function validarItemConDatos(string $campoEsperado, array $datosItem): void
    {
        $rules = $this->obtenerRules();
        $datos = ['items' => [$datosItem]];
        $this->validarYVerificarError($datos, $rules, $campoEsperado);
    }
}

