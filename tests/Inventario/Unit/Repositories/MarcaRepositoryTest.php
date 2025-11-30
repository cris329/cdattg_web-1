<?php

namespace Tests\Inventario\Unit\Repositories;

use Tests\TestCase;
use App\Inventario\Repositories\Marca\MarcaRepository;
use App\Models\Tema;
use App\Models\Parametro;
use App\Models\ParametroTema;
use App\Models\Inventario\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class MarcaRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected MarcaRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new MarcaRepository();
        
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
    public function puede_obtener_tema_marcas()
    {
        $tema = Tema::create(['name' => 'MARCAS']);

        $resultado = $this->repository->obtenerTemaMarcas();

        $this->assertNotNull($resultado);
        $this->assertEquals('MARCAS', $resultado->name);
    }

    #[Test]
    public function retorna_null_si_no_existe_tema_marcas()
    {
        $resultado = $this->repository->obtenerTemaMarcas();

        $this->assertNull($resultado);
    }

    #[Test]
    public function puede_obtener_marcas_con_filtros()
    {
        $tema = Tema::create(['name' => 'MARCAS']);
        $parametro1 = Parametro::create(['name' => 'MARCA 1']);
        $parametro2 = Parametro::create(['name' => 'MARCA 2']);
        
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
    public function puede_filtrar_marcas_por_busqueda()
    {
        $tema = Tema::create(['name' => 'MARCAS']);
        $parametro1 = Parametro::create(['name' => 'SAMSUNG']);
        $parametro2 = Parametro::create(['name' => 'LG']);
        
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

        $resultado = $this->repository->obtenerConFiltros(['search' => 'SAMS']);

        $this->assertEquals(1, $resultado->total());
        $this->assertEquals('SAMSUNG', $resultado->items()[0]->name);
    }

    #[Test]
    public function puede_encontrar_marca_por_id()
    {
        $marca = \App\Models\Inventario\Marca::create(['name' => 'TEST MARCA ' . uniqid()]);

        $resultado = $this->repository->encontrar($marca->id);

        $this->assertNotNull($resultado);
        $this->assertEquals($marca->id, $resultado->id);
    }

    #[Test]
    public function puede_encontrar_multiples_marcas()
    {
        $marca1 = \App\Models\Inventario\Marca::create(['name' => 'MARCA1']);
        $marca2 = \App\Models\Inventario\Marca::create(['name' => 'MARCA2']);

        $resultado = $this->repository->encontrarMultiples([$marca1->id, $marca2->id]);

        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado->has($marca1->id));
        $this->assertTrue($resultado->has($marca2->id));
    }

    #[Test]
    public function puede_encontrar_marca_con_relaciones()
    {
        $parametro = Parametro::create(['name' => 'MARCA TEST']);

        $resultado = $this->repository->encontrarConRelaciones($parametro->id);

        $this->assertNotNull($resultado);
        $this->assertEquals($parametro->id, $resultado->id);
    }

    #[Test]
    public function puede_actualizar_marca()
    {
        $parametro = Parametro::create(['name' => 'MARCA ORIGINAL']);

        $resultado = $this->repository->actualizar($parametro->id, ['name' => 'MARCA ACTUALIZADA']);

        $this->assertTrue($resultado);
        $this->assertEquals('MARCA ACTUALIZADA', Parametro::find($parametro->id)->name);
    }

    #[Test]
    public function puede_verificar_si_marca_tiene_productos()
    {
        $marca = \App\Models\Inventario\Marca::create(['name' => 'MARCA']);
        $producto = Producto::factory()->create(['marca_id' => $marca->id]);

        $resultado = $this->repository->tieneProductos($marca->id);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function retorna_false_si_marca_no_tiene_productos()
    {
        $marca = \App\Models\Inventario\Marca::create(['name' => 'MARCA']);

        $resultado = $this->repository->tieneProductos($marca->id);

        $this->assertFalse($resultado);
    }
}

