<?php

namespace Tests\Inventario\Unit\Repositories;

use Tests\TestCase;
use App\Inventario\Repositories\Producto\ProductoRepository;
use App\Models\Inventario\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\Attributes\Test;

class ProductoRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private const CODIGO_BARRAS_TEST = '1234567890123';

    protected ProductoRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProductoRepository();
        
        // Ejecutar seeders necesarios
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
    public function puede_obtener_productos_con_filtros()
    {
        Producto::factory()->count(3)->create();

        $resultado = $this->repository->obtenerConFiltros();

        $this->assertGreaterThanOrEqual(3, $resultado->total());
    }

    #[Test]
    public function puede_filtrar_productos_por_busqueda()
    {
        Producto::factory()->create(['producto' => 'COMPUTADOR TEST']);
        Producto::factory()->create(['producto' => 'MOUSE TEST']);

        $resultado = $this->repository->obtenerConFiltros(['search' => 'COMPUTADOR']);

        $this->assertGreaterThanOrEqual(1, $resultado->total());
    }

    #[Test]
    public function puede_filtrar_productos_por_categoria()
    {
        $categoriaId = 51;
        Producto::factory()->create(['categoria_id' => $categoriaId]);
        Producto::factory()->create(['categoria_id' => 52]);

        $resultado = $this->repository->obtenerConFiltros(['categoria_id' => $categoriaId]);

        $this->assertGreaterThanOrEqual(1, $resultado->total());
    }

    #[Test]
    public function puede_filtrar_productos_con_stock()
    {
        Producto::factory()->create(['cantidad' => 5]);
        Producto::factory()->create(['cantidad' => 0]);

        $resultado = $this->repository->obtenerConFiltros(['solo_con_stock' => true]);

        $this->assertTodosTienenStock($resultado);
    }

    #[Test]
    public function puede_encontrar_producto_con_relaciones()
    {
        $producto = Producto::factory()->create();

        $resultado = $this->repository->encontrarConRelaciones($producto->id);

        $this->assertNotNull($resultado);
        $this->assertTrue($resultado->relationLoaded('tipoProducto'));
        $this->assertTrue($resultado->relationLoaded('categoria'));
    }

    #[Test]
    public function puede_buscar_producto_por_codigo_barras()
    {
        $producto = Producto::factory()->create(['codigo_barras' => self::CODIGO_BARRAS_TEST]);

        $resultado = $this->repository->buscarPorCodigoBarras(self::CODIGO_BARRAS_TEST);

        $this->assertProductoEncontrado($resultado, $producto->id);
    }

    #[Test]
    public function puede_obtener_productos_para_catalogo()
    {
        Producto::factory()->create(['cantidad' => 10]);
        Producto::factory()->create(['cantidad' => 0]);

        $resultado = $this->repository->obtenerParaCatalogo();

        $this->assertTodosTienenStock($resultado);
    }

    #[Test]
    public function puede_buscar_productos_para_ajax()
    {
        Producto::factory()->count(3)->create(['cantidad' => 5]);

        $resultado = $this->repository->buscarParaAjax();

        $this->assertGreaterThanOrEqual(3, $resultado->count());
    }

    #[Test]
    public function puede_encontrar_producto_por_id()
    {
        $producto = Producto::factory()->create();

        $resultado = $this->repository->encontrar($producto->id);

        $this->assertProductoEncontrado($resultado, $producto->id);
    }

    #[Test]
    public function puede_crear_producto()
    {
        $datos = [
            'producto' => 'PRODUCTO TEST',
            'tipo_producto_id' => 28,
            'unidad_medida_id' => 30,
            'cantidad' => 10,
            'estado_producto_id' => 42,
            'categoria_id' => 51,
            'marca_id' => 60,
            'descripcion' => 'Descripción del producto test',
            'peso' => 10.5,
            'codigo_barras' => self::CODIGO_BARRAS_TEST,
            'user_create_id' => 1,
            'user_update_id' => 1,
        ];

        $resultado = $this->repository->crear($datos);

        $this->assertInstanceOf(Producto::class, $resultado);
        $this->assertEquals('PRODUCTO TEST', $resultado->producto);
    }

    #[Test]
    public function puede_actualizar_producto()
    {
        $producto = Producto::factory()->create(['producto' => 'ORIGINAL']);

        $resultado = $this->repository->actualizar($producto, ['producto' => 'ACTUALIZADO']);

        $this->assertTrue($resultado);
        $this->assertEquals('ACTUALIZADO', $producto->fresh()->producto);
    }

    #[Test]
    public function puede_eliminar_producto()
    {
        $producto = Producto::factory()->create();

        $resultado = $this->repository->eliminar($producto);

        $this->assertTrue($resultado);
        $this->assertNull(Producto::find($producto->id));
    }

    #[Test]
    public function puede_actualizar_stock()
    {
        $producto = Producto::factory()->create(['cantidad' => 10]);

        $resultado = $this->repository->actualizarStock($producto, 25);

        $this->assertTrue($resultado);
        $this->assertEquals(25, $producto->fresh()->cantidad);
    }

    #[Test]
    public function puede_obtener_max_codigo_barras()
    {
        Producto::factory()->create(['codigo_barras' => '100']);
        Producto::factory()->create(['codigo_barras' => '200']);

        $resultado = $this->repository->obtenerMaxCodigoBarras();

        $this->assertNotNull($resultado);
    }

    #[Test]
    public function puede_verificar_si_existe_codigo_barras()
    {
        Producto::factory()->create(['codigo_barras' => self::CODIGO_BARRAS_TEST]);

        $resultado = $this->repository->existeCodigoBarras(self::CODIGO_BARRAS_TEST);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function retorna_false_si_no_existe_codigo_barras()
    {
        $resultado = $this->repository->existeCodigoBarras('9999999999999');

        $this->assertFalse($resultado);
    }

    /**
     * Assert that all products in a collection have stock (cantidad > 0).
     */
    private function assertTodosTienenStock(LengthAwarePaginator $productos): void
    {
        foreach ($productos as $producto) {
            $this->assertGreaterThan(0, $producto->cantidad);
        }
    }

    /**
     * Assert that a producto was found and matches the expected ID.
     */
    private function assertProductoEncontrado(?Producto $resultado, int $productoId): void
    {
        $this->assertNotNull($resultado);
        $this->assertEquals($productoId, $resultado->id);
    }
}

