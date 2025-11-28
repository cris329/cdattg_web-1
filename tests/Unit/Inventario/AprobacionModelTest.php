<?php

namespace Tests\Unit\Inventario;

use App\Models\Inventario\Aprobacion;
use App\Models\Inventario\DetalleOrden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AprobacionModelTest extends TestCase
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
        $aprobacion = Aprobacion::factory()->create(['detalle_orden_id' => $detalle->id]);

        $this->assertInstanceOf(DetalleOrden::class, $aprobacion->detalleOrden);
        $this->assertEquals($detalle->id, $aprobacion->detalleOrden->id);
    }

    #[Test]
    public function puede_crear_aprobacion(): void
    {
        $detalle = DetalleOrden::factory()->create();

        $aprobacion = Aprobacion::factory()->create([
            'detalle_orden_id' => $detalle->id,
        ]);

        $this->assertDatabaseHas('aprobaciones', [
            'id' => $aprobacion->id,
            'detalle_orden_id' => $detalle->id,
        ]);
    }
}

