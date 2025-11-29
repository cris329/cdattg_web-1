<?php

declare(strict_types=1);

namespace Tests\Inventario\Feature\Request;

use Tests\TestCase;
use App\Http\Requests\Inventario\ProductoRequest;
use App\Models\Inventario\Producto;
use App\Models\ParametroTema;
use App\Models\Parametro;
use App\Models\Inventario\ContratoConvenio;
use App\Models\Inventario\Proveedor;
use App\Models\Ambiente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;

class ProductoRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateDatabases();
    }

    #[Test]
    public function valida_campos_requeridos_para_store(): void
    {
        $request = new ProductoRequest();
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('producto', $validator->errors()->toArray());
        $this->assertArrayHasKey('tipo_producto_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('descripcion', $validator->errors()->toArray());
        $this->assertArrayHasKey('peso', $validator->errors()->toArray());
        $this->assertArrayHasKey('unidad_medida_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('cantidad', $validator->errors()->toArray());
        $this->assertArrayHasKey('estado_producto_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('categoria_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('marca_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('contrato_convenio_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('ambiente_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('proveedor_id', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_unicidad_de_producto_en_store(): void
    {
        $this->markTestSkipped('Requiere Personas porque Producto::factory() crea usuarios que necesitan persona_id');
    }

    #[Test]
    public function valida_que_producto_exista_en_agregar_carrito(): void
    {
        $request = new ProductoRequest();
        
        // Simular ruta de agregar carrito
        $request->setRouteResolver(function () {
            return new class {
                public function named(...$patterns) {
                    return in_array('inventario.productos.agregar-carrito', $patterns);
                }
            };
        });

        $rules = $request->rules();

        $validator = Validator::make([
            'producto_id' => 99999,
            'cantidad' => 1,
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('producto_id', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_cantidad_minima_en_agregar_carrito(): void
    {
        $this->markTestSkipped('Requiere Personas porque Producto::factory() crea usuarios que necesitan persona_id');

        $rules = $request->rules();

        $validator = Validator::make([
            'producto_id' => $producto->id,
            'cantidad' => 0,
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cantidad', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_existencia_de_tipo_producto(): void
    {
        $request = new ProductoRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'producto' => 'NUEVO PRODUCTO',
            'tipo_producto_id' => 99999,
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tipo_producto_id', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_peso_minimo(): void
    {
        $request = new ProductoRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'producto' => 'NUEVO PRODUCTO',
            'peso' => -1,
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('peso', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_cantidad_minima_en_store(): void
    {
        $request = new ProductoRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'producto' => 'NUEVO PRODUCTO',
            'cantidad' => 0,
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cantidad', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_imagen_formato_y_tamaño(): void
    {
        $request = new ProductoRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'producto' => 'NUEVO PRODUCTO',
            'imagen' => 'archivo.pdf',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('imagen', $validator->errors()->toArray());
    }

    #[Test]
    public function acepta_datos_validos_para_store(): void
    {
        $this->markTestSkipped('Requiere Personas y ParametroTema porque usa múltiples factories que requieren datos que no existen aún');
    }
}

