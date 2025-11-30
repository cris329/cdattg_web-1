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

    #[Test]
    public function puede_obtener_tema_categorias()
    {
        // Crear tema de categorías
        $tema = Tema::create(['name' => 'CATEGORIAS']);

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
        // Crear tema y categorías
        $tema = Tema::create(['name' => 'CATEGORIAS']);
        $parametro1 = Parametro::create(['name' => 'CATEGORIA 1']);
        $parametro2 = Parametro::create(['name' => 'CATEGORIA 2']);
        
        ParametroTema::create([
            'parametro_id' => $parametro1->id,
            'tema_id' => $tema->id,
            'status' => 1
        ]);
        ParametroTema::create([
            'parametro_id' => $parametro2->id,
            'tema_id' => $tema->id,
            'status' => 1
        ]);

        $resultado = $this->repository->obtenerConFiltros();

        $this->assertEquals(2, $resultado->total());
    }

    #[Test]
    public function puede_filtrar_categorias_por_busqueda()
    {
        $tema = Tema::create(['name' => 'CATEGORIAS']);
        $parametro1 = Parametro::create(['name' => 'ELECTRONICA']);
        $parametro2 = Parametro::create(['name' => 'MOBILIARIO']);
        
        ParametroTema::create([
            'parametro_id' => $parametro1->id,
            'tema_id' => $tema->id,
            'status' => 1
        ]);
        ParametroTema::create([
            'parametro_id' => $parametro2->id,
            'tema_id' => $tema->id,
            'status' => 1
        ]);

        $resultado = $this->repository->obtenerConFiltros(['search' => 'ELECTRO']);

        $this->assertEquals(1, $resultado->total());
        $this->assertEquals('ELECTRONICA', $resultado->items()[0]->name);
    }

    #[Test]
    public function puede_encontrar_categoria_por_id()
    {
        $categoria = \App\Models\Inventario\Categoria::create(['name' => 'TEST CATEGORIA ' . uniqid()]);

        $resultado = $this->repository->encontrar($categoria->id);

        $this->assertNotNull($resultado);
        $this->assertEquals($categoria->id, $resultado->id);
    }

    #[Test]
    public function puede_encontrar_multiples_categorias()
    {
        $categoria1 = \App\Models\Inventario\Categoria::create(['name' => 'CAT1']);
        $categoria2 = \App\Models\Inventario\Categoria::create(['name' => 'CAT2']);

        $resultado = $this->repository->encontrarMultiples([$categoria1->id, $categoria2->id]);

        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado->has($categoria1->id));
        $this->assertTrue($resultado->has($categoria2->id));
    }

    #[Test]
    public function puede_encontrar_categoria_con_relaciones()
    {
        $parametro = Parametro::create(['name' => 'CATEGORIA TEST']);

        $resultado = $this->repository->encontrarConRelaciones($parametro->id);

        $this->assertNotNull($resultado);
        $this->assertEquals($parametro->id, $resultado->id);
    }

    #[Test]
    public function puede_actualizar_categoria()
    {
        $parametro = Parametro::create(['name' => 'CATEGORIA ORIGINAL']);

        $resultado = $this->repository->actualizar($parametro->id, ['name' => 'CATEGORIA ACTUALIZADA']);

        $this->assertTrue($resultado);
        $this->assertEquals('CATEGORIA ACTUALIZADA', Parametro::find($parametro->id)->name);
    }

    #[Test]
    public function puede_verificar_si_categoria_tiene_productos()
    {
        $categoria = \App\Models\Inventario\Categoria::create(['name' => 'CATEGORIA']);
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $resultado = $this->repository->tieneProductos($categoria->id);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function retorna_false_si_categoria_no_tiene_productos()
    {
        $categoria = \App\Models\Inventario\Categoria::create(['name' => 'CATEGORIA']);

        $resultado = $this->repository->tieneProductos($categoria->id);

        $this->assertFalse($resultado);
    }
}

