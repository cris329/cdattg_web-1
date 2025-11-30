<?php

namespace Tests\Inventario\Unit\Repositories;

use Tests\TestCase;
use App\Inventario\Repositories\Categoria\CategoriaRepository;
use App\Models\Tema;
use App\Models\Parametro;
use App\Models\ParametroTema;
use App\Models\Inventario\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class CategoriaRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected CategoriaRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CategoriaRepository();
        
        // Ejecutar seeders necesarios
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\PersonaSeeder::class,
            \Database\Seeders\UsersSeeder::class,
        ]);
    }

    private function crearTemaCategorias(): Tema
    {
        return Tema::create(['name' => 'CATEGORIAS']);
    }

    private function crearParametro(string $nombre): Parametro
    {
        return Parametro::create(['name' => $nombre]);
    }

    private function crearCategoria(string $nombre): \App\Models\Inventario\Categoria
    {
        return \App\Models\Inventario\Categoria::create(['name' => $nombre]);
    }

    private function crearCategoriaConParametroTema(Tema $tema, string $nombreParametro): Parametro
    {
        $parametro = $this->crearParametro($nombreParametro);
        
        ParametroTema::create([
            'parametro_id' => $parametro->id,
            'tema_id' => $tema->id,
            'status' => 1
        ]);

        return $parametro;
    }

    #[Test]
    public function puede_obtener_tema_categorias()
    {
        $this->crearTemaCategorias();

        $resultado = $this->repository->obtenerTemaCategorias();

        $this->assertNotNull($resultado);
        $this->assertEquals('CATEGORIAS', $resultado->name);
    }

    #[Test]
    public function retorna_null_si_no_existe_tema_categorias()
    {
        $resultado = $this->repository->obtenerTemaCategorias();

        $this->assertNull($resultado);
    }

    #[Test]
    public function puede_obtener_categorias_con_filtros()
    {
        $tema = $this->crearTemaCategorias();
        $this->crearCategoriaConParametroTema($tema, 'CATEGORIA 1');
        $this->crearCategoriaConParametroTema($tema, 'CATEGORIA 2');

        $resultado = $this->repository->obtenerConFiltros();

        $this->assertEquals(2, $resultado->total());
    }

    #[Test]
    public function puede_filtrar_categorias_por_busqueda()
    {
        $tema = $this->crearTemaCategorias();
        $this->crearCategoriaConParametroTema($tema, 'ELECTRONICA');
        $this->crearCategoriaConParametroTema($tema, 'MOBILIARIO');

        $resultado = $this->repository->obtenerConFiltros(['search' => 'ELECTRO']);

        $this->assertEquals(1, $resultado->total());
        $this->assertEquals('ELECTRONICA', $resultado->items()[0]->name);
    }

    #[Test]
    public function puede_encontrar_categoria_por_id()
    {
        $categoria = $this->crearCategoria('TEST CATEGORIA ' . uniqid());

        $resultado = $this->repository->encontrar($categoria->id);

        $this->assertNotNull($resultado);
        $this->assertEquals($categoria->id, $resultado->id);
    }

    #[Test]
    public function puede_encontrar_multiples_categorias()
    {
        $categoria1 = $this->crearCategoria('CAT1');
        $categoria2 = $this->crearCategoria('CAT2');

        $resultado = $this->repository->encontrarMultiples([$categoria1->id, $categoria2->id]);

        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado->has($categoria1->id));
        $this->assertTrue($resultado->has($categoria2->id));
    }

    #[Test]
    public function puede_encontrar_categoria_con_relaciones()
    {
        $parametro = $this->crearParametro('CATEGORIA TEST');

        $resultado = $this->repository->encontrarConRelaciones($parametro->id);

        $this->assertNotNull($resultado);
        $this->assertEquals($parametro->id, $resultado->id);
    }

    #[Test]
    public function puede_actualizar_categoria()
    {
        $parametro = $this->crearParametro('CATEGORIA ORIGINAL');

        $resultado = $this->repository->actualizar($parametro->id, ['name' => 'CATEGORIA ACTUALIZADA']);

        $this->assertTrue($resultado);
        $this->assertEquals('CATEGORIA ACTUALIZADA', Parametro::find($parametro->id)->name);
    }

    #[Test]
    public function puede_verificar_si_categoria_tiene_productos()
    {
        $categoria = $this->crearCategoria('CATEGORIA');
        Producto::factory()->create(['categoria_id' => $categoria->id]);

        $resultado = $this->repository->tieneProductos($categoria->id);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function retorna_false_si_categoria_no_tiene_productos()
    {
        $categoria = $this->crearCategoria('CATEGORIA');

        $resultado = $this->repository->tieneProductos($categoria->id);

        $this->assertFalse($resultado);
    }
}

