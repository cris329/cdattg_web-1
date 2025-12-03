<?php

namespace Tests\Inventario\Unit\Repositories;

use Tests\TestCase;
use App\Inventario\Repositories\Dashboard\DashboardRepository;
use App\Models\Inventario\Producto;
use App\Models\Tema;
use App\Models\Parametro;
use App\Models\ParametroTema;
use App\Models\Inventario\DetalleOrden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Carbon\Carbon;

class DashboardRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected DashboardRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DashboardRepository();
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

    #[Test]
    public function puede_obtener_total_productos()
    {
        Producto::factory()->count(5)->create();

        $resultado = $this->repository->obtenerTotalProductos();

        $this->assertEquals(5, $resultado);
    }

    #[Test]
    public function puede_obtener_productos_consumibles()
    {
        $parametroTema = $this->crearTipoProducto('CONSUMIBLE');
        Producto::factory()->create(['tipo_producto_id' => $parametroTema->id]);

        $resultado = $this->repository->obtenerProductosConsumibles();

        $this->assertGreaterThanOrEqual(1, $resultado);
    }

    #[Test]
    public function puede_obtener_productos_no_consumibles()
    {
        $parametroTema = $this->crearTipoProducto('NO CONSUMIBLE');
        Producto::factory()->create(['tipo_producto_id' => $parametroTema->id]);

        $resultado = $this->repository->obtenerProductosNoConsumibles();

        $this->assertGreaterThanOrEqual(1, $resultado);
    }

    #[Test]
    public function puede_obtener_productos_por_vencer()
    {
        $fechaVencimiento = Carbon::now()->addDays(15);
        Producto::factory()->create(['fecha_vencimiento' => $fechaVencimiento]);

        $resultado = $this->repository->obtenerProductosPorVencer();

        $this->assertGreaterThanOrEqual(1, $resultado);
    }

    #[Test]
    public function puede_obtener_productos_stock_bajo()
    {
        Producto::factory()->create(['cantidad' => 5]);
        Producto::factory()->create(['cantidad' => 15]);

        $resultado = $this->repository->obtenerProductosStockBajo();

        $this->assertGreaterThanOrEqual(1, $resultado);
    }

    #[Test]
    public function puede_obtener_total_categorias()
    {
        $this->crearCategoria('CATEGORIA 1');
        $this->crearCategoria('CATEGORIA 2');

        $resultado = $this->repository->obtenerTotalCategorias();

        $this->assertEquals(2, $resultado);
    }

    #[Test]
    public function puede_obtener_productos_mas_solicitados()
    {
        $producto = Producto::factory()->create();
        DetalleOrden::factory()->count(3)->create([
            'producto_id' => $producto->id,
            'cantidad' => 5
        ]);

        $resultado = $this->repository->obtenerProductosMasSolicitados(5);

        $this->assertIsArray($resultado);
        $this->assertGreaterThanOrEqual(1, count($resultado));
    }

    #[Test]
    public function puede_obtener_productos_por_categoria()
    {
        $categoria = $this->crearCategoria('CATEGORIA TEST');
        Producto::factory()->count(3)->create(['categoria_id' => $categoria->id]);

        $resultado = $this->repository->obtenerProductosPorCategoria();

        $this->assertIsArray($resultado);
    }

    #[Test]
    public function puede_obtener_productos_recientes()
    {
        Producto::factory()->count(3)->create();

        $resultado = $this->repository->obtenerProductosRecientes(5);

        $this->assertIsArray($resultado);
        $this->assertLessThanOrEqual(5, count($resultado));
    }

    /**
     * Create a tipo de producto (Tema + Parametro + ParametroTema).
     */
    private function crearTipoProducto(string $nombreParametro): ParametroTema
    {
        $tema = Tema::firstOrCreate(['name' => 'TIPOS DE PRODUCTO'], ['status' => 1]);
        $parametro = Parametro::firstOrCreate(
            ['name' => $nombreParametro],
            ['status' => 1, 'user_create_id' => null, 'user_edit_id' => null]
        );
        
        return ParametroTema::firstOrCreate(
            [
                'parametro_id' => $parametro->id,
                'tema_id' => $tema->id,
            ],
            ['status' => 1, 'user_create_id' => null, 'user_edit_id' => null]
        );
    }

    /**
     * Create a categoria (Tema + Parametro + ParametroTema).
     * Returns the Parametro instance as that's what Producto uses.
     */
    private function crearCategoria(string $nombreCategoria): Parametro
    {
        $tema = Tema::firstOrCreate(['name' => 'CATEGORIAS'], ['status' => 1]);
        $parametro = Parametro::firstOrCreate(
            ['name' => $nombreCategoria],
            ['status' => 1]
        );
        
        ParametroTema::firstOrCreate(
            [
                'parametro_id' => $parametro->id,
                'tema_id' => $tema->id,
            ],
            ['status' => 1]
        );
        
        return $parametro;
    }
}

