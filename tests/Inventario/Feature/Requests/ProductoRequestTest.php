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
        $rules = $this->obtenerRules();

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
        Producto::factory()->create(['producto' => 'PRODUCTO TEST']);

        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            ['producto' => 'PRODUCTO TEST'],
            $rules,
            'producto'
        );
    }

    private const ROUTE_AGREGAR_CARRITO = 'inventario.productos.agregar-carrito';
    private const ID_INEXISTENTE = 99999;
    private const CANTIDAD_INVALIDA = 0;
    private const CANTIDAD_VALIDA = 1;
    private const PESO_INVALIDO = -1;
    private const PESO_VALIDO = 10.5;
    private const PRODUCTO_NUEVO = 'PRODUCTO NUEVO';

    private function obtenerRules(): array
    {
        $request = new ProductoRequest();
        return $request->rules();
    }

    private function obtenerRulesParaAgregarCarrito(): array
    {
        $request = new ProductoRequest();
        $rutaAgregarCarrito = self::ROUTE_AGREGAR_CARRITO;
        $request->setRouteResolver(function () use ($rutaAgregarCarrito) {
            return new class($rutaAgregarCarrito) {
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
    public function valida_que_producto_exista_en_agregar_carrito(): void
    {
        $rules = $this->obtenerRulesParaAgregarCarrito();

        $this->validarYVerificarError(
            [
                'producto_id' => self::ID_INEXISTENTE,
                'cantidad' => self::CANTIDAD_VALIDA,
            ],
            $rules,
            'producto_id'
        );
    }

    #[Test]
    public function valida_cantidad_minima_en_agregar_carrito(): void
    {
        $producto = Producto::factory()->create();
        $rules = $this->obtenerRulesParaAgregarCarrito();

        $this->validarYVerificarError(
            [
                'producto_id' => $producto->id,
                'cantidad' => self::CANTIDAD_INVALIDA,
            ],
            $rules,
            'cantidad'
        );
    }

    #[Test]
    public function valida_existencia_de_tipo_producto(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            [
                'producto' => self::PRODUCTO_NUEVO,
                'tipo_producto_id' => self::ID_INEXISTENTE,
            ],
            $rules,
            'tipo_producto_id'
        );
    }

    #[Test]
    public function valida_peso_minimo(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            [
                'producto' => self::PRODUCTO_NUEVO,
                'peso' => self::PESO_INVALIDO,
            ],
            $rules,
            'peso'
        );
    }

    #[Test]
    public function valida_cantidad_minima_en_store(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            [
                'producto' => self::PRODUCTO_NUEVO,
                'cantidad' => self::CANTIDAD_INVALIDA,
            ],
            $rules,
            'cantidad'
        );
    }

    #[Test]
    public function valida_imagen_formato_y_tamaño(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            [
                'producto' => self::PRODUCTO_NUEVO,
                'imagen' => 'archivo.pdf',
            ],
            $rules,
            'imagen'
        );
    }

    #[Test]
    public function acepta_datos_validos_para_store(): void
    {
        $tipoProducto = ParametroTema::query()->inRandomOrder()->first();
        $unidadMedida = ParametroTema::query()->inRandomOrder()->first();
        $estadoProducto = ParametroTema::query()->inRandomOrder()->first();
        
        $categoriaParametro = $this->crearParametroConTema('CATEGORIA TEST', 'CATEGORIAS');
        $marcaParametro = $this->crearParametroConTema('MARCA TEST', 'MARCAS');

        $rules = $this->obtenerRules();

        $validator = Validator::make([
            'producto' => self::PRODUCTO_NUEVO,
            'tipo_producto_id' => $tipoProducto->id,
            'descripcion' => 'Descripción del producto',
            'peso' => self::PESO_VALIDO,
            'unidad_medida_id' => $unidadMedida->id,
            'cantidad' => 5,
            'estado_producto_id' => $estadoProducto->id,
            'categoria_id' => $categoriaParametro->id,
            'marca_id' => $marcaParametro->id,
            'contrato_convenio_id' => ContratoConvenio::factory()->create()->id,
            'ambiente_id' => Ambiente::factory()->create()->id,
            'proveedor_id' => Proveedor::factory()->create()->id,
        ], $rules);

        $this->assertFalse($validator->fails());
    }

    private function crearParametroConTema(string $nombreParametro, string $nombreTema): \App\Models\Parametro
    {
        $tema = \App\Models\Tema::where('name', $nombreTema)->first();
        $userId = User::query()->inRandomOrder()->value('id');
        
        $parametro = \App\Models\Parametro::create([
            'name' => $nombreParametro,
            'status' => 1,
            'user_create_id' => $userId,
            'user_edit_id' => $userId,
        ]);
        
        if ($tema) {
            \App\Models\ParametroTema::create([
                'parametro_id' => $parametro->id,
                'tema_id' => $tema->id,
                'status' => 1,
                'user_create_id' => $userId,
                'user_edit_id' => $userId,
            ]);
        }
        
        return $parametro;
    }
}

