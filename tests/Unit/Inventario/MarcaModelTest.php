<?php

namespace Tests\Unit\Inventario;

use App\Models\Inventario\Marca;
use App\Models\Inventario\Producto;
use App\Models\Tema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MarcaModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);
    }

    #[Test]
    public function convierte_nombre_a_mayusculas_al_crear(): void
    {
        $marca = Marca::create(['name' => 'marca test']);

        $this->assertEquals('MARCA TEST', $marca->name);
    }

    #[Test]
    public function tiene_relacion_con_productos(): void
    {
        $marca = Marca::create(['name' => 'MARCA']);
        Producto::factory()->count(2)->create(['marca_id' => $marca->id]);

        $this->assertCount(2, $marca->productos);
    }

    #[Test]
    public function puede_asociar_a_tema_marcas(): void
    {
        $tema = Tema::firstOrCreate(['name' => 'MARCAS']);
        $marca = Marca::create(['name' => 'MARCA TEST']);

        $marca->asociarATemaMarcas();

        $this->assertDatabaseHas('parametros_temas', [
            'parametro_id' => $marca->id,
            'tema_id' => $tema->id,
        ]);
    }
}

