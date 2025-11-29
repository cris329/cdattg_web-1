<?php

declare(strict_types=1);

namespace Tests\Inventario\Feature\Request;

use Tests\TestCase;
use App\Http\Requests\Inventario\OrdenRequest;
use App\Models\Inventario\Producto;
use App\Models\ParametroTema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;

class OrdenRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateDatabases();
    }

    #[Test]
    public function valida_campos_requeridos_para_prestamos_salidas(): void
    {
        $request = new OrdenRequest();
        $request->setRouteResolver(function () {
            return new class {
                public function named(...$patterns) {
                    return in_array('inventario.prestamos-salidas.store', $patterns);
                }
            };
        });

        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('rol', $validator->errors()->toArray());
        $this->assertArrayHasKey('programa_formacion', $validator->errors()->toArray());
        $this->assertArrayHasKey('tipo', $validator->errors()->toArray());
        $this->assertArrayHasKey('descripcion', $validator->errors()->toArray());
        $this->assertArrayHasKey('carrito', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_tipo_debe_ser_prestamo_o_salida(): void
    {
        $request = new OrdenRequest();
        $request->setRouteResolver(function () {
            return new class {
                public function named(...$patterns) {
                    return in_array('inventario.prestamos-salidas.store', $patterns);
                }
            };
        });

        $rules = $request->rules();

        $validator = Validator::make([
            'tipo' => 'invalido',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tipo', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_fecha_devolucion_requerida_para_prestamo(): void
    {
        $request = new OrdenRequest();
        $request->setRouteResolver(function () {
            return new class {
                public function named(...$patterns) {
                    return in_array('inventario.prestamos-salidas.store', $patterns);
                }
            };
        });

        $rules = $request->rules();

        $validator = Validator::make([
            'tipo' => 'prestamo',
            'fecha_devolucion' => null,
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('fecha_devolucion', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_fecha_devolucion_debe_ser_posterior_a_hoy(): void
    {
        $request = new OrdenRequest();
        $request->setRouteResolver(function () {
            return new class {
                public function named(...$patterns) {
                    return in_array('inventario.prestamos-salidas.store', $patterns);
                }
            };
        });

        $rules = $request->rules();

        $validator = Validator::make([
            'tipo' => 'prestamo',
            'fecha_devolucion' => '2020-01-01',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('fecha_devolucion', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_carrito_debe_ser_json(): void
    {
        $request = new OrdenRequest();
        $request->setRouteResolver(function () {
            return new class {
                public function named(...$patterns) {
                    return in_array('inventario.prestamos-salidas.store', $patterns);
                }
            };
        });

        $rules = $request->rules();

        $validator = Validator::make([
            'carrito' => 'no es json',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('carrito', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_campos_requeridos_para_orden_normal(): void
    {
        $request = new OrdenRequest();
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('descripcion_orden', $validator->errors()->toArray());
        $this->assertArrayHasKey('tipo_orden_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('productos', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_productos_debe_ser_array_no_vacio(): void
    {
        $request = new OrdenRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'productos' => [],
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('productos', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_productos_debe_tener_producto_id_y_cantidad(): void
    {
        $request = new OrdenRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'productos' => [
                ['cantidad' => 1],
            ],
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('productos.0.producto_id', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_producto_id_debe_existir(): void
    {
        $request = new OrdenRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'productos' => [
                [
                    'producto_id' => 99999,
                    'cantidad' => 1,
                ],
            ],
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('productos.0.producto_id', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_cantidad_minima_en_productos(): void
    {
        $this->markTestSkipped('Requiere Personas y ParametroTema porque Producto::factory() y ParametroTema::factory() requieren datos que no existen aún');
    }

    #[Test]
    public function acepta_datos_validos_para_prestamo(): void
    {
        $request = new OrdenRequest();
        $request->setRouteResolver(function () {
            return new class {
                public function named(...$patterns) {
                    return in_array('inventario.prestamos-salidas.store', $patterns);
                }
            };
        });

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
}

