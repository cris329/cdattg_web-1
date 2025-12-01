<?php

namespace Tests\Inventario\Unit\Repositories;

use Tests\TestCase;
use App\Inventario\Repositories\Aprobacion\AprobacionRepository;
use App\Models\Inventario\Aprobacion;
use App\Models\Inventario\DetalleOrden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class AprobacionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected AprobacionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AprobacionRepository();
        
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
            \Database\Seeders\RegionalSeeder::class,
            \Database\Seeders\SedeSeeder::class,
            \Database\Seeders\BloqueSeeder::class,
            \Database\Seeders\PisoSeeder::class,
            \Database\Seeders\AmbienteSeeder::class,
        ]);
    }

    #[Test]
    public function puede_crear_aprobacion()
    {
        $detalleOrden = DetalleOrden::factory()->create();
        $datos = $this->crearDatosAprobacion($detalleOrden->id);

        $resultado = $this->repository->crear($datos);

        $this->assertAprobacionCreada($resultado, $detalleOrden->id, 49);
    }

    /**
     * Create test data for aprobacion.
     */
    private function crearDatosAprobacion(int $detalleOrdenId, int $estadoAprobacionId = 49): array
    {
        return [
            'detalle_orden_id' => $detalleOrdenId,
            'estado_aprobacion_id' => $estadoAprobacionId,
            'user_create_id' => 1,
            'user_update_id' => 1,
        ];
    }

    /**
     * Assert that aprobacion was created correctly.
     */
    private function assertAprobacionCreada(
        Aprobacion $resultado,
        int $detalleOrdenIdEsperado,
        int $estadoAprobacionIdEsperado
    ): void {
        $this->assertInstanceOf(Aprobacion::class, $resultado);
        $this->assertEquals($detalleOrdenIdEsperado, $resultado->detalle_orden_id);
        $this->assertEquals($estadoAprobacionIdEsperado, $resultado->estado_aprobacion_id);
    }
}

