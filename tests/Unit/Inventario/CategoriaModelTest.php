<?php

namespace Tests\Unit\Inventario;

use App\Models\Inventario\Categoria;
use App\Models\Inventario\Producto;
use App\Models\Tema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoriaModelTest extends TestCase
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
        $categoria = Categoria::create(['name' => 'categoria test']);

        $this->assertEquals('CATEGORIA TEST', $categoria->name);
    }

    #[Test]
    public function tiene_relacion_con_productos(): void
    {
        $categoria = Categoria::create(['name' => 'CATEGORIA']);
        Producto::factory()->count(2)->create(['categoria_id' => $categoria->id]);

        $this->assertCount(2, $categoria->productos);
    }

    #[Test]
    public function puede_asociar_a_tema_categorias(): void
    {
        $tema = Tema::firstOrCreate(['name' => 'CATEGORIAS']);
        $categoria = Categoria::create(['name' => 'CATEGORIA TEST']);

        $categoria->asociarATemaCategorias();

        $this->assertDatabaseHas('parametros_temas', [
            'parametro_id' => $categoria->id,
            'tema_id' => $tema->id,
        ]);
    }
}

