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
        $orden = Orden::factory()->create();
        $orden->update(['descripcion_orden' => 'ORDEN TEST']);

        $resultado = $this->repository->obtenerConFiltros(['search' => 'TEST']);

        $this->assertGreaterThanOrEqual(1, $resultado->total());
    }

    #[Test]
    public function puede_filtrar_ordenes_por_tipo()
    {
        $tipoOrdenId = 44;
        Orden::factory()->create(['tipo_orden_id' => $tipoOrdenId]);
        Orden::factory()->create(['tipo_orden_id' => 45]);

        $resultado = $this->repository->obtenerConFiltros(['tipo_orden_id' => $tipoOrdenId]);

        $this->assertGreaterThanOrEqual(1, $resultado->total());
    }

    #[Test]
    public function puede_obtener_ordenes_pendientes()
    {
        $estadoEnEsperaId = 46; // Asumiendo que existe este estado
        $orden = Orden::factory()->create();
        DetalleOrden::factory()->create([
            'orden_id' => $orden->id,
            'estado_orden_id' => $estadoEnEsperaId
        ]);

        $resultado = $this->repository->obtenerPendientes($estadoEnEsperaId);

        $this->assertGreaterThanOrEqual(1, $resultado->total());
    }

    #[Test]
    public function puede_obtener_ordenes_completadas()
    {
        $estadoAprobadaId = 47; // Asumiendo que existe este estado
        $orden = Orden::factory()->create();
        DetalleOrden::factory()->create([
            'orden_id' => $orden->id,
            'estado_orden_id' => $estadoAprobadaId
        ]);

        $resultado = $this->repository->obtenerCompletadas($estadoAprobadaId);

        $this->assertGreaterThanOrEqual(1, $resultado->total());
    }

    #[Test]
    public function puede_obtener_ordenes_rechazadas()
    {
        $estadoRechazadaId = 48; // Asumiendo que existe este estado
        $orden = Orden::factory()->create();
        DetalleOrden::factory()->create([
            'orden_id' => $orden->id,
            'estado_orden_id' => $estadoRechazadaId
        ]);

        $resultado = $this->repository->obtenerRechazadas($estadoRechazadaId);

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
        $estadoEnEsperaId = 46;
        $orden = Orden::factory()->create();
        DetalleOrden::factory()->create([
            'orden_id' => $orden->id,
            'estado_orden_id' => $estadoEnEsperaId
        ]);

        $resultado = $this->repository->obtenerDetallesPendientes($estadoEnEsperaId);

        $this->assertGreaterThanOrEqual(1, $resultado->count());
    }

    #[Test]
    public function puede_crear_orden()
    {
        $datos = [
            'tipo_orden_id' => 44,
            'user_create_id' => 1,
        ];

        $resultado = $this->repository->crear($datos);

        $this->assertInstanceOf(Orden::class, $resultado);
        $this->assertEquals(44, $resultado->tipo_orden_id);
    }

    #[Test]
    public function puede_actualizar_orden()
    {
        $orden = Orden::factory()->create();

        $resultado = $this->repository->actualizar($orden, ['tipo_orden_id' => 45]);

        $this->assertTrue($resultado);
        $this->assertEquals(45, $orden->fresh()->tipo_orden_id);
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

