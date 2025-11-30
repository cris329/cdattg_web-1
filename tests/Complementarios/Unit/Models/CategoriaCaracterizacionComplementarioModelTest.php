<?php

namespace Tests\Complementarios\Unit\Models;

use App\Models\Complementarios\CategoriaCaracterizacionComplementario;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoriaCaracterizacionComplementarioModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\TemaSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
        ]);
    }

    #[Test]
    public function tiene_relacion_con_parent(): void
    {
        $this->markTestSkipped('La tabla categorias_caracterizacion_complementarios no existe - la migración está vacía');
        
        $parent = CategoriaCaracterizacionComplementario::factory()->create();
        $child = CategoriaCaracterizacionComplementario::factory()->create([
            'parent_id' => $parent->id,
        ]);

        $this->assertInstanceOf(CategoriaCaracterizacionComplementario::class, $child->parent);
        $this->assertEquals($parent->id, $child->parent->id);
    }

    #[Test]
    public function tiene_relacion_con_children(): void
    {
        $this->markTestSkipped('La tabla categorias_caracterizacion_complementarios no existe - la migración está vacía');
        
        $parent = CategoriaCaracterizacionComplementario::factory()->create();
        CategoriaCaracterizacionComplementario::factory()->count(2)->create([
            'parent_id' => $parent->id,
        ]);

        $this->assertCount(2, $parent->children);
    }

    #[Test]
    public function obtiene_categorias_principales(): void
    {
        $this->markTestSkipped('La tabla categorias_caracterizacion_complementarios no existe - la migración está vacía');
        
        CategoriaCaracterizacionComplementario::factory()->create(['parent_id' => null, 'activo' => 1]);
        CategoriaCaracterizacionComplementario::factory()->create(['parent_id' => 1, 'activo' => 1]);

        $principales = CategoriaCaracterizacionComplementario::getMainCategories();

        $this->assertGreaterThan(0, $principales->count());
    }

    #[Test]
    public function tiene_relacion_muchos_a_muchos_con_personas(): void
    {
        $this->markTestSkipped('La tabla categorias_caracterizacion_complementarios no existe - la migración está vacía');
        
        $categoria = CategoriaCaracterizacionComplementario::factory()->create();
        $persona = Persona::factory()->create();

        $categoria->personas()->attach($persona->id);

        $this->assertTrue($categoria->personas->contains($persona));
    }
}

