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

    private function crearTemaMarcas(): Tema
    {
        return Tema::create(['name' => 'MARCAS']);
    }

    private function crearParametro(string $nombre): Parametro
    {
        return Parametro::create(['name' => $nombre]);
    }

    private function crearMarca(string $nombre): \App\Models\Inventario\Marca
    {
        return \App\Models\Inventario\Marca::create(['name' => $nombre]);
    }

    private function crearMarcaConParametroTema(Tema $tema, string $nombreParametro): Parametro
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
    public function puede_obtener_tema_marcas()
    {
        $this->crearTemaMarcas();

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
        $tema = $this->crearTemaMarcas();
        $this->crearMarcaConParametroTema($tema, 'MARCA 1');
        $this->crearMarcaConParametroTema($tema, 'MARCA 2');

        $resultado = $this->repository->obtenerConFiltros();

        $this->assertEquals(2, $resultado->total());
    }

    #[Test]
    public function puede_filtrar_marcas_por_busqueda()
    {
        $tema = $this->crearTemaMarcas();
        $this->crearMarcaConParametroTema($tema, 'SAMSUNG');
        $this->crearMarcaConParametroTema($tema, 'LG');

        $resultado = $this->repository->obtenerConFiltros(['search' => 'SAMS']);

        $this->assertEquals(1, $resultado->total());
        $this->assertEquals('SAMSUNG', $resultado->items()[0]->name);
    }

    #[Test]
    public function puede_encontrar_marca_por_id()
    {
        $marca = $this->crearMarca('TEST MARCA ' . uniqid());

        $resultado = $this->repository->encontrar($marca->id);

        $this->assertNotNull($resultado);
        $this->assertEquals($marca->id, $resultado->id);
    }

    #[Test]
    public function puede_encontrar_multiples_marcas()
    {
        $marca1 = $this->crearMarca('MARCA1');
        $marca2 = $this->crearMarca('MARCA2');

        $resultado = $this->repository->encontrarMultiples([$marca1->id, $marca2->id]);

        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado->has($marca1->id));
        $this->assertTrue($resultado->has($marca2->id));
    }

    #[Test]
    public function puede_encontrar_marca_con_relaciones()
    {
        $parametro = $this->crearParametro('MARCA TEST');

        $resultado = $this->repository->encontrarConRelaciones($parametro->id);

        $this->assertNotNull($resultado);
        $this->assertEquals($parametro->id, $resultado->id);
    }

    #[Test]
    public function puede_actualizar_marca()
    {
        $parametro = $this->crearParametro('MARCA ORIGINAL');

        $resultado = $this->repository->actualizar($parametro->id, ['name' => 'MARCA ACTUALIZADA']);

        $this->assertTrue($resultado);
        $this->assertEquals('MARCA ACTUALIZADA', Parametro::find($parametro->id)->name);
    }

    #[Test]
    public function puede_verificar_si_marca_tiene_productos()
    {
        $marca = $this->crearMarca('MARCA');
        Producto::factory()->create(['marca_id' => $marca->id]);

        $resultado = $this->repository->tieneProductos($marca->id);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function retorna_false_si_marca_no_tiene_productos()
    {
        $marca = $this->crearMarca('MARCA');

        $resultado = $this->repository->tieneProductos($marca->id);

        $this->assertFalse($resultado);
    }
}

