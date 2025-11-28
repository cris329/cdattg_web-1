<?php

namespace Tests\Unit\Inventario;

use App\Models\Inventario\Devolucion;
use App\Models\Inventario\DetalleOrden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DevolucionModelTest extends TestCase
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
    public function tiene_relacion_con_detalle_orden(): void
    {
        $detalle = DetalleOrden::factory()->create();
        $devolucion = Devolucion::factory()->create(['detalle_orden_id' => $detalle->id]);

        $this->assertInstanceOf(DetalleOrden::class, $devolucion->detalleOrden);
        $this->assertEquals($detalle->id, $devolucion->detalleOrden->id);
    }

    #[Test]
    public function calcula_dias_retraso(): void
    {
        $orden = \App\Models\Inventario\Orden::factory()->create([
            'fecha_devolucion' => now()->subDays(5),
        ]);
        $detalle = DetalleOrden::factory()->create(['orden_id' => $orden->id]);
        $devolucion = Devolucion::factory()->create([
            'detalle_orden_id' => $detalle->id,
            'fecha_devolucion' => now(),
        ]);

        $dias = $devolucion->getDiasRetraso();

        $this->assertIsInt($dias);
        $this->assertGreaterThanOrEqual(0, $dias);
    }
}

