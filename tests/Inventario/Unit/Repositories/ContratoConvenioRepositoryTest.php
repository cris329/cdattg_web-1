<?php

namespace Tests\Inventario\Unit\Repositories;

use Tests\TestCase;
use App\Inventario\Repositories\ContratoConvenio\ContratoConvenioRepository;
use App\Models\Inventario\ContratoConvenio;
use App\Models\Inventario\Proveedor;
use App\Models\Inventario\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ContratoConvenioRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected ContratoConvenioRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ContratoConvenioRepository();
        
        // Ejecutar seeders necesarios
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\TemaSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\PersonaSeeder::class,
            \Database\Seeders\UsersSeeder::class,
        ]);
    }

    #[Test]
    public function puede_obtener_todos_los_contratos()
    {
        ContratoConvenio::factory()->count(3)->create();

        $resultado = $this->repository->obtenerTodos();

        $this->assertCount(3, $resultado);
    }

    #[Test]
    public function puede_obtener_contratos_con_filtros()
    {
        ContratoConvenio::factory()->create(['name' => 'CONTRATO TEST']);
        ContratoConvenio::factory()->create(['name' => 'OTRO CONTRATO']);

        $resultado = $this->repository->obtenerConFiltros();

        $this->assertGreaterThanOrEqual(2, $resultado->total());
    }

    #[Test]
    public function puede_filtrar_contratos_por_busqueda()
    {
        ContratoConvenio::factory()->create(['name' => 'CONTRATO SUMINISTRO']);
        ContratoConvenio::factory()->create(['name' => 'CONVENIO SERVICIOS']);

        $resultado = $this->repository->obtenerConFiltros(['search' => 'SUMINISTRO']);

        $this->assertGreaterThanOrEqual(1, $resultado->count());
    }

    #[Test]
    public function puede_filtrar_contratos_por_codigo()
    {
        $codigo = 'AB-12CD-3456';
        ContratoConvenio::factory()->create(['codigo' => $codigo]);
        ContratoConvenio::factory()->create(['codigo' => 'XY-99ZZ-9999']);

        $resultado = $this->repository->obtenerConFiltros(['search' => 'AB-12']);

        $this->assertGreaterThanOrEqual(1, $resultado->count());
    }

    #[Test]
    public function puede_encontrar_contrato_con_relaciones()
    {
        $contrato = ContratoConvenio::factory()->create();

        $resultado = $this->repository->encontrarConRelaciones($contrato->id);

        $this->assertNotNull($resultado);
        $this->assertTrue($resultado->relationLoaded('proveedor'));
        $this->assertTrue($resultado->relationLoaded('productos'));
    }

    #[Test]
    public function puede_crear_contrato()
    {
        $proveedor = Proveedor::factory()->create();
        $estado = \App\Models\ParametroTema::query()->inRandomOrder()->first();
        
        $datos = [
            'name' => 'CONTRATO TEST',
            'codigo' => 'TEST-001',
            'proveedor_id' => $proveedor->id,
            'fecha_inicio' => now()->format('Y-m-d'),
            'fecha_fin' => now()->addYear()->format('Y-m-d'),
            'estado_id' => $estado->id,
            'user_create_id' => 1,
            'user_update_id' => 1,
        ];

        $resultado = $this->repository->crear($datos);

        $this->assertInstanceOf(ContratoConvenio::class, $resultado);
        $this->assertEquals('CONTRATO TEST', $resultado->name);
    }

    #[Test]
    public function puede_actualizar_contrato()
    {
        $contrato = ContratoConvenio::factory()->create(['name' => 'ORIGINAL']);

        $resultado = $this->repository->actualizar($contrato->id, ['name' => 'ACTUALIZADO']);

        $this->assertTrue($resultado);
        $this->assertEquals('ACTUALIZADO', ContratoConvenio::find($contrato->id)->name);
    }

    #[Test]
    public function puede_eliminar_contrato()
    {
        $contrato = ContratoConvenio::factory()->create();

        $resultado = $this->repository->eliminar($contrato->id);

        $this->assertTrue($resultado);
        $this->assertNull(ContratoConvenio::find($contrato->id));
    }

    #[Test]
    public function puede_verificar_si_contrato_tiene_productos()
    {
        $contrato = ContratoConvenio::factory()->create();
        Producto::factory()->create(['contrato_convenio_id' => $contrato->id]);

        $resultado = $this->repository->tieneProductos($contrato->id);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function retorna_false_si_contrato_no_tiene_productos()
    {
        $contrato = ContratoConvenio::factory()->create();

        $resultado = $this->repository->tieneProductos($contrato->id);

        $this->assertFalse($resultado);
    }
}

