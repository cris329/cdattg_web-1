<?php

namespace Tests\Unit\Inventario;

use App\Models\Departamento;
use App\Models\Inventario\ContratoConvenio;
use App\Models\Inventario\Producto;
use App\Models\Inventario\Proveedor;
use App\Models\Municipio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProveedorModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
        ]);
    }

    #[Test]
    public function convierte_proveedor_a_mayusculas_al_crear(): void
    {
        $proveedor = Proveedor::factory()->create(['proveedor' => 'proveedor test']);

        $this->assertEquals('PROVEEDOR TEST', $proveedor->proveedor);
    }

    #[Test]
    public function tiene_relacion_con_departamento(): void
    {
        $departamento = Departamento::first();
        $proveedor = Proveedor::factory()->create(['departamento_id' => $departamento->id]);

        $this->assertInstanceOf(Departamento::class, $proveedor->departamento);
    }

    #[Test]
    public function tiene_relacion_con_municipio(): void
    {
        $municipio = Municipio::first();
        $proveedor = Proveedor::factory()->create(['municipio_id' => $municipio->id]);

        $this->assertInstanceOf(Municipio::class, $proveedor->municipio);
    }

    #[Test]
    public function tiene_relacion_con_contratos_convenios(): void
    {
        $proveedor = Proveedor::factory()->create();
        ContratoConvenio::factory()->count(2)->create(['proveedor_id' => $proveedor->id]);

        $this->assertCount(2, $proveedor->contratosConvenios);
    }

    #[Test]
    public function tiene_relacion_con_productos(): void
    {
        $proveedor = Proveedor::factory()->create();
        Producto::factory()->count(2)->create(['proveedor_id' => $proveedor->id]);

        $this->assertCount(2, $proveedor->productos);
    }
}

