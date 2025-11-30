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
        $producto = Producto::factory()->create(['producto' => 'PRODUCTO TEST']);

        $request = new ProductoRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'producto' => 'PRODUCTO TEST',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('producto', $validator->errors()->toArray());
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
        $producto = Producto::factory()->create();

        $request = new ProductoRequest();
        $request->setRouteResolver(function () {
            return new class {
                public function named(...$patterns) {
                    return in_array('inventario.productos.agregar-carrito', $patterns);
                }
            };
        });

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
        $tipoProducto = ParametroTema::query()->inRandomOrder()->first();
        $unidadMedida = ParametroTema::query()->inRandomOrder()->first();
        $estadoProducto = ParametroTema::query()->inRandomOrder()->first();
        
        // Crear categoría y marca (extienden de Parametro)
        $temaCategorias = \App\Models\Tema::where('name', 'CATEGORIAS')->first();
        $temaMarcas = \App\Models\Tema::where('name', 'MARCAS')->first();
        
        $categoriaParametro = \App\Models\Parametro::create([
            'name' => 'CATEGORIA TEST',
            'status' => 1,
            'user_create_id' => User::query()->inRandomOrder()->value('id'),
            'user_edit_id' => User::query()->inRandomOrder()->value('id'),
        ]);
        
        if ($temaCategorias) {
            \App\Models\ParametroTema::create([
                'parametro_id' => $categoriaParametro->id,
                'tema_id' => $temaCategorias->id,
                'status' => 1,
                'user_create_id' => User::query()->inRandomOrder()->value('id'),
                'user_edit_id' => User::query()->inRandomOrder()->value('id'),
            ]);
        }
        
        $marcaParametro = \App\Models\Parametro::create([
            'name' => 'MARCA TEST',
            'status' => 1,
            'user_create_id' => User::query()->inRandomOrder()->value('id'),
            'user_edit_id' => User::query()->inRandomOrder()->value('id'),
        ]);
        
        if ($temaMarcas) {
            \App\Models\ParametroTema::create([
                'parametro_id' => $marcaParametro->id,
                'tema_id' => $temaMarcas->id,
                'status' => 1,
                'user_create_id' => User::query()->inRandomOrder()->value('id'),
                'user_edit_id' => User::query()->inRandomOrder()->value('id'),
            ]);
        }
        
        $contratoConvenio = ContratoConvenio::factory()->create();
        $proveedor = Proveedor::factory()->create();
        $ambiente = Ambiente::factory()->create();

        $request = new ProductoRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'producto' => 'NUEVO PRODUCTO',
            'tipo_producto_id' => $tipoProducto->id,
            'descripcion' => 'Descripción del producto',
            'peso' => 10.5,
            'unidad_medida_id' => $unidadMedida->id,
            'cantidad' => 5,
            'estado_producto_id' => $estadoProducto->id,
            'categoria_id' => $categoriaParametro->id,
            'marca_id' => $marcaParametro->id,
            'contrato_convenio_id' => $contratoConvenio->id,
            'ambiente_id' => $ambiente->id,
            'proveedor_id' => $proveedor->id,
        ], $rules);

        $this->assertFalse($validator->fails());
    }
}

