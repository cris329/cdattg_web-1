<?php

namespace Tests\Inventario\Unit\Repositories;

use Tests\TestCase;
use App\Inventario\Repositories\Devolucion\DevolucionRepository;
use App\Models\Inventario\Orden;
use App\Models\Inventario\DetalleOrden;
use App\Models\Inventario\Devolucion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class DevolucionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected DevolucionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DevolucionRepository();
        
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
    public function puede_obtener_prestamos_pendientes()
    {
        $orden = Orden::factory()->create(['fecha_devolucion' => now()->addDays(30)]);
        $detalleOrden = DetalleOrden::factory()->create([
            'orden_id' => $orden->id,
        ]);

        $resultado = $this->repository->obtenerPrestamosPendientes($detalleOrden->estado_orden_id);

        $this->assertGreaterThanOrEqual(1, $resultado->total());
    }

    #[Test]
    public function puede_obtener_historial_devoluciones()
    {
        Devolucion::factory()->count(3)->create();

        $resultado = $this->repository->obtenerHistorial();

        $this->assertGreaterThanOrEqual(3, $resultado->total());
    }

    #[Test]
    public function puede_encontrar_devolucion_con_relaciones()
    {
        $devolucion = Devolucion::factory()->create();

        $resultado = $this->repository->encontrarConRelaciones($devolucion->id);

        $this->assertNotNull($resultado);
        $this->assertTrue($resultado->relationLoaded('detalleOrden'));
    }

    #[Test]
    public function puede_obtener_prestamos_activos_usuario()
    {
        $userId = 1;
        $orden = Orden::factory()->create([
            'user_create_id' => $userId,
            'fecha_devolucion' => now()->addDays(30)
        ]);
        $detalleOrden = DetalleOrden::factory()->create([
            'orden_id' => $orden->id,
        ]);

        $resultado = $this->repository->obtenerPrestamosActivosUsuario($userId, $detalleOrden->estado_orden_id);

        $this->assertGreaterThanOrEqual(1, $resultado->total());
    }

    #[Test]
    public function puede_obtener_historial_prestamos_usuario()
    {
        $userId = 1;
        $orden = Orden::factory()->create([
            'user_create_id' => $userId,
            'fecha_devolucion' => now()->addDays(30)
        ]);
        DetalleOrden::factory()->create(['orden_id' => $orden->id]);

        $resultado = $this->repository->obtenerHistorialPrestamosUsuario($userId);

        $this->assertGreaterThanOrEqual(1, $resultado->total());
    }
}

