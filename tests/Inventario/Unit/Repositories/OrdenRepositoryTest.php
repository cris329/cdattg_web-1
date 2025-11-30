<?php

namespace Tests\Inventario\Unit\Repositories;

use Tests\TestCase;
use App\Inventario\Repositories\Orden\OrdenRepository;
use App\Models\Inventario\Orden;
use App\Models\Inventario\DetalleOrden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class OrdenRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected OrdenRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new OrdenRepository();
        
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
    public function puede_obtener_ordenes_con_filtros()
    {
        Orden::factory()->count(3)->create();

        $resultado = $this->repository->obtenerConFiltros();

        $this->assertGreaterThanOrEqual(3, $resultado->total());
    }

    #[Test]
    public function puede_filtrar_ordenes_por_busqueda()
    {
        $orden = Orden::factory()->create(['descripcion_orden' => 'ORDEN TEST']);

        $resultado = $this->repository->obtenerConFiltros(['search' => 'TEST']);

        $this->assertGreaterThanOrEqual(1, $resultado->total());
    }

    #[Test]
    public function puede_filtrar_ordenes_por_tipo()
    {
        $orden1 = Orden::factory()->create();
        $orden2 = Orden::factory()->create();

        $resultado = $this->repository->obtenerConFiltros(['tipo_orden_id' => $orden1->tipo_orden_id]);

        $this->assertGreaterThanOrEqual(1, $resultado->total());
    }

    #[Test]
    public function puede_obtener_ordenes_pendientes()
    {
        $orden = Orden::factory()->create();
        $detalleOrden = DetalleOrden::factory()->create([
            'orden_id' => $orden->id,
        ]);

        $resultado = $this->repository->obtenerPendientes($detalleOrden->estado_orden_id);

        $this->assertGreaterThanOrEqual(1, $resultado->total());
    }

    #[Test]
    public function puede_obtener_ordenes_completadas()
    {
        $orden = Orden::factory()->create();
        $detalleOrden = DetalleOrden::factory()->create([
            'orden_id' => $orden->id,
        ]);

        $resultado = $this->repository->obtenerCompletadas($detalleOrden->estado_orden_id);

        $this->assertGreaterThanOrEqual(1, $resultado->total());
    }

    #[Test]
    public function puede_obtener_ordenes_rechazadas()
    {
        $orden = Orden::factory()->create();
        $detalleOrden = DetalleOrden::factory()->create([
            'orden_id' => $orden->id,
        ]);

        $resultado = $this->repository->obtenerRechazadas($detalleOrden->estado_orden_id);

        $this->assertGreaterThanOrEqual(1, $resultado->total());
    }

    #[Test]
    public function puede_encontrar_orden_con_relaciones()
    {
        $orden = Orden::factory()->create();

        $resultado = $this->repository->encontrarConRelaciones($orden->id);

        $this->assertNotNull($resultado);
        $this->assertTrue($resultado->relationLoaded('tipoOrden'));
        $this->assertTrue($resultado->relationLoaded('detalles'));
    }

    #[Test]
    public function puede_encontrar_orden_con_detalles_y_devoluciones()
    {
        $orden = Orden::factory()->create();

        $resultado = $this->repository->encontrarConDetallesYDevoluciones($orden->id);

        $this->assertNotNull($resultado);
        $this->assertTrue($resultado->relationLoaded('detalles'));
    }

    #[Test]
    public function puede_obtener_detalles_pendientes()
    {
        $orden = Orden::factory()->create();
        $detalleOrden = DetalleOrden::factory()->create([
            'orden_id' => $orden->id,
        ]);

        $resultado = $this->repository->obtenerDetallesPendientes($detalleOrden->estado_orden_id);

        $this->assertGreaterThanOrEqual(1, $resultado->count());
    }

    #[Test]
    public function puede_crear_orden()
    {
        $orden = Orden::factory()->create();
        $datos = [
            'descripcion_orden' => 'ORDEN TEST',
            'tipo_orden_id' => $orden->tipo_orden_id,
            'user_create_id' => 1,
            'user_update_id' => 1,
        ];

        $resultado = $this->repository->crear($datos);

        $this->assertInstanceOf(Orden::class, $resultado);
        $this->assertEquals($orden->tipo_orden_id, $resultado->tipo_orden_id);
    }

    #[Test]
    public function puede_actualizar_orden()
    {
        $orden = Orden::factory()->create();
        $nuevaOrden = Orden::factory()->create();

        $resultado = $this->repository->actualizar($orden, ['tipo_orden_id' => $nuevaOrden->tipo_orden_id]);

        $this->assertTrue($resultado);
        $this->assertEquals($nuevaOrden->tipo_orden_id, $orden->fresh()->tipo_orden_id);
    }

    #[Test]
    public function puede_eliminar_orden()
    {
        $orden = Orden::factory()->create();

        $resultado = $this->repository->eliminar($orden);

        $this->assertTrue($resultado);
        $this->assertNull(Orden::find($orden->id));
    }
}

