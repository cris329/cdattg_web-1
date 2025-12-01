<?php

declare(strict_types=1);

namespace Tests\Inventario\Feature\Request;

use Tests\TestCase;
use App\Http\Requests\Inventario\OrdenRequest;
use App\Models\Inventario\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;
use PHPUnit\Framework\Attributes\Test;

class OrdenRequestTest extends TestCase
{
    use RefreshDatabase;

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

    #[Test]
    public function valida_campos_requeridos_para_prestamos_salidas(): void
    {
        $request = $this->crearRequestParaPrestamosSalidas();
        $rules = $request->rules();
        $validator = Validator::make([], $rules);

        $this->assertValidacionFalla($validator);
        $this->assertValidacionTieneError($validator, 'rol');
        $this->assertValidacionTieneError($validator, 'programa_formacion');
        $this->assertValidacionTieneError($validator, 'tipo');
        $this->assertValidacionTieneError($validator, 'descripcion');
        $this->assertValidacionTieneError($validator, 'carrito');
    }

    #[Test]
    public function valida_tipo_debe_ser_prestamo_o_salida(): void
    {
        $request = $this->crearRequestParaPrestamosSalidas();
        $rules = $request->rules();
        $validator = Validator::make(['tipo' => 'invalido'], $rules);

        $this->assertValidacionFalla($validator);
        $this->assertValidacionTieneError($validator, 'tipo');
    }

    #[Test]
    public function valida_fecha_devolucion_requerida_para_prestamo(): void
    {
        $request = $this->crearRequestParaPrestamosSalidas();
        $rules = $request->rules();
        $validator = Validator::make([
            'tipo' => 'prestamo',
            'fecha_devolucion' => null,
        ], $rules);

        $this->assertValidacionFalla($validator);
        $this->assertValidacionTieneError($validator, 'fecha_devolucion');
    }

    #[Test]
    public function valida_fecha_devolucion_debe_ser_posterior_a_hoy(): void
    {
        $request = $this->crearRequestParaPrestamosSalidas();
        $rules = $request->rules();
        $validator = Validator::make([
            'tipo' => 'prestamo',
            'fecha_devolucion' => '2020-01-01',
        ], $rules);

        $this->assertValidacionFalla($validator);
        $this->assertValidacionTieneError($validator, 'fecha_devolucion');
    }

    #[Test]
    public function valida_carrito_debe_ser_json(): void
    {
        $request = $this->crearRequestParaPrestamosSalidas();
        $rules = $request->rules();
        $validator = Validator::make(['carrito' => 'no es json'], $rules);

        $this->assertValidacionFalla($validator);
        $this->assertValidacionTieneError($validator, 'carrito');
    }

    #[Test]
    public function valida_campos_requeridos_para_orden_normal(): void
    {
        $request = $this->crearRequestParaOrdenNormal();
        $rules = $request->rules();
        $validator = Validator::make([], $rules);

        $this->assertValidacionFalla($validator);
        $this->assertValidacionTieneError($validator, 'descripcion_orden');
        $this->assertValidacionTieneError($validator, 'tipo_orden_id');
        $this->assertValidacionTieneError($validator, 'productos');
    }

    #[Test]
    public function valida_productos_debe_ser_array_no_vacio(): void
    {
        $request = $this->crearRequestParaOrdenNormal();
        $rules = $request->rules();
        $validator = Validator::make(['productos' => []], $rules);

        $this->assertValidacionFalla($validator);
        $this->assertValidacionTieneError($validator, 'productos');
    }

    #[Test]
    public function valida_productos_debe_tener_producto_id_y_cantidad(): void
    {
        $request = $this->crearRequestParaOrdenNormal();
        $rules = $request->rules();
        $validator = Validator::make([
            'productos' => [['cantidad' => 1]],
        ], $rules);

        $this->assertValidacionFalla($validator);
        $this->assertValidacionTieneError($validator, 'productos.0.producto_id');
    }

    #[Test]
    public function valida_producto_id_debe_existir(): void
    {
        $request = $this->crearRequestParaOrdenNormal();
        $rules = $request->rules();
        $validator = Validator::make([
            'productos' => [
                ['producto_id' => 99999, 'cantidad' => 1],
            ],
        ], $rules);

        $this->assertValidacionFalla($validator);
        $this->assertValidacionTieneError($validator, 'productos.0.producto_id');
    }

    #[Test]
    public function valida_cantidad_minima_en_productos(): void
    {
        $producto = Producto::factory()->create();
        $request = $this->crearRequestParaOrdenNormal();
        $rules = $request->rules();
        $validator = Validator::make([
            'productos' => [
                ['producto_id' => $producto->id, 'cantidad' => 0],
            ],
        ], $rules);

        $this->assertValidacionFalla($validator);
        $this->assertValidacionTieneError($validator, 'productos.0.cantidad');
    }

    #[Test]
    public function acepta_datos_validos_para_prestamo(): void
    {
        $request = $this->crearRequestParaPrestamosSalidas();
        $rules = $request->rules();
        $validator = Validator::make([
            'rol' => 'Instructor',
            'programa_formacion' => 'Programa de Prueba',
            'tipo' => 'prestamo',
            'fecha_devolucion' => now()->addDays(7)->format('Y-m-d'),
            'descripcion' => 'Descripción del préstamo',
            'carrito' => json_encode(['items' => []]),
        ], $rules);

        $this->assertFalse($validator->fails());
    }

    /**
     * Create OrdenRequest configured for préstamos/salidas route.
     */
    private function crearRequestParaPrestamosSalidas(): OrdenRequest
    {
        $request = new OrdenRequest();
        $request->setRouteResolver(function () {
            return new class {
                public function named(...$patterns): bool {
                    return in_array('inventario.prestamos-salidas.store', $patterns);
                }
            };
        });
        return $request;
    }

    /**
     * Create OrdenRequest for normal order route.
     */
    private function crearRequestParaOrdenNormal(): OrdenRequest
    {
        return new OrdenRequest();
    }

    /**
     * Assert that validation fails.
     */
    private function assertValidacionFalla(ValidationValidator $validator): void
    {
        $this->assertTrue($validator->fails());
    }

    /**
     * Assert that validation has a specific error field.
     */
    private function assertValidacionTieneError(ValidationValidator $validator, string $campo): void
    {
        $this->assertArrayHasKey($campo, $validator->errors()->toArray());
    }
}

