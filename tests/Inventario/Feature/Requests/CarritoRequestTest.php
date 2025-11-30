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
    public function valida_cantidad_requerida_para_actualizar(): void
    {
        $request = new CarritoRequest();
        $request->setRouteResolver(function () {
            return new class {
                public function named(...$patterns) {
                    return in_array('inventario.carrito.actualizar', $patterns);
                }
            };
        });

        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cantidad', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_cantidad_debe_ser_integer_para_actualizar(): void
    {
        $request = new CarritoRequest();
        $request->setRouteResolver(function () {
            return new class {
                public function named(...$patterns) {
                    return in_array('inventario.carrito.actualizar', $patterns);
                }
            };
        });

        $rules = $request->rules();

        $validator = Validator::make([
            'cantidad' => 'no es numero',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cantidad', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_cantidad_minima_para_actualizar(): void
    {
        $request = new CarritoRequest();
        $request->setRouteResolver(function () {
            return new class {
                public function named(...$patterns) {
                    return in_array('inventario.carrito.actualizar', $patterns);
                }
            };
        });

        $rules = $request->rules();

        $validator = Validator::make([
            'cantidad' => 0,
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cantidad', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_items_requerido_para_agregar(): void
    {
        $request = new CarritoRequest();
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('items', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_items_debe_ser_array(): void
    {
        $request = new CarritoRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'items' => 'no es array',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('items', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_items_debe_tener_producto_id(): void
    {
        $request = new CarritoRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'items' => [
                ['cantidad' => 1],
            ],
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('items.0.producto_id', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_producto_id_debe_existir(): void
    {
        $request = new CarritoRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'items' => [
                [
                    'producto_id' => 99999,
                    'cantidad' => 1,
                ],
            ],
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('items.0.producto_id', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_items_debe_tener_cantidad(): void
    {
        $request = new CarritoRequest();
        $rules = $request->rules();

        $producto = Producto::factory()->create();

        $validator = Validator::make([
            'items' => [
                [
                    'producto_id' => $producto->id,
                ],
            ],
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('items.0.cantidad', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_cantidad_minima_en_items(): void
    {
        $request = new CarritoRequest();
        $rules = $request->rules();

        $producto = Producto::factory()->create();

        $validator = Validator::make([
            'items' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 0,
                ],
            ],
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('items.0.cantidad', $validator->errors()->toArray());
    }

    #[Test]
    public function acepta_datos_validos_para_actualizar(): void
    {
        $request = new CarritoRequest();
        $request->setRouteResolver(function () {
            return new class {
                public function named(...$patterns) {
                    return in_array('inventario.carrito.actualizar', $patterns);
                }
            };
        });

        $rules = $request->rules();

        $validator = Validator::make([
            'cantidad' => 5,
        ], $rules);

        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function acepta_datos_validos_para_agregar(): void
    {
        $request = new CarritoRequest();
        $rules = $request->rules();

        $producto = Producto::factory()->create();

        $validator = Validator::make([
            'items' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 5,
                ],
            ],
        ], $rules);

        $this->assertFalse($validator->fails());
    }
}

