<?php

namespace Tests\Unit\Inventario;

use App\Models\Inventario\ContratoConvenio;
use App\Models\Inventario\Producto;
use App\Models\Inventario\Proveedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContratoConvenioModelTest extends TestCase
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
    public function convierte_name_a_mayusculas_al_crear(): void
    {
        $proveedor = Proveedor::factory()->create();
        $contrato = ContratoConvenio::factory()->create([
            'name' => 'contrato test',
            'proveedor_id' => $proveedor->id,
        ]);

        $this->assertEquals('CONTRATO TEST', $contrato->name);
    }

    #[Test]
    public function tiene_relacion_con_proveedor(): void
    {
        $proveedor = Proveedor::factory()->create();
        $contrato = ContratoConvenio::factory()->create(['proveedor_id' => $proveedor->id]);

        $this->assertInstanceOf(Proveedor::class, $contrato->proveedor);
        $this->assertEquals($proveedor->id, $contrato->proveedor->id);
    }

    #[Test]
    public function tiene_relacion_con_productos(): void
    {
        $contrato = ContratoConvenio::factory()->create();
        Producto::factory()->count(2)->create(['contrato_convenio_id' => $contrato->id]);

        $this->assertCount(2, $contrato->productos);
    }
}

