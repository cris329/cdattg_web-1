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
        $this->ejecutarSeedersNecesarios();
    }

    private function ejecutarSeedersNecesarios(): void
    {
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
        $ordenConDetalle = $this->crearOrdenConDetalle();
        $resultado = $this->repository->obtenerPrestamosPendientes($ordenConDetalle['detalle']->estado_orden_id);

        $this->assertResultadoPaginadoMinimo($resultado, 1);
    }

    #[Test]
    public function puede_obtener_historial_devoluciones()
    {
        Devolucion::factory()->count(3)->create();
        $resultado = $this->repository->obtenerHistorial();

        $this->assertResultadoPaginadoMinimo($resultado, 3);
    }

    #[Test]
    public function puede_encontrar_devolucion_con_relaciones()
    {
        $devolucion = Devolucion::factory()->create();
        $resultado = $this->repository->encontrarConRelaciones($devolucion->id);

        $this->assertDevolucionEncontradaConRelaciones($resultado, 'detalleOrden');
    }

    private const USER_ID_TEST = 1;

    #[Test]
    public function puede_obtener_prestamos_activos_usuario()
    {
        $ordenConDetalle = $this->crearOrdenConDetalle([
            'user_create_id' => self::USER_ID_TEST,
        ]);
        $resultado = $this->repository->obtenerPrestamosActivosUsuario(
            self::USER_ID_TEST,
            $ordenConDetalle['detalle']->estado_orden_id
        );

        $this->assertResultadoPaginadoMinimo($resultado, 1);
    }

    #[Test]
    public function puede_obtener_historial_prestamos_usuario()
    {
        $this->crearOrdenConDetalle([
            'user_create_id' => self::USER_ID_TEST,
        ]);
        $resultado = $this->repository->obtenerHistorialPrestamosUsuario(self::USER_ID_TEST);

        $this->assertResultadoPaginadoMinimo($resultado, 1);
    }

    /**
     * Create orden with detalleOrden for testing.
     */
    private function crearOrdenConDetalle(array $datosOrden = []): array
    {
        $datosOrdenDefault = ['fecha_devolucion' => now()->addDays(30)];
        $orden = Orden::factory()->create(array_merge($datosOrdenDefault, $datosOrden));
        $detalleOrden = DetalleOrden::factory()->create(['orden_id' => $orden->id]);

        return [
            'orden' => $orden,
            'detalle' => $detalleOrden,
        ];
    }

    /**
     * Assert that paginated result has at least minimum count.
     */
    private function assertResultadoPaginadoMinimo($resultado, int $minimo): void
    {
        $this->assertGreaterThanOrEqual($minimo, $resultado->total());
    }

    /**
     * Assert that devolucion was found with relations loaded.
     */
    private function assertDevolucionEncontradaConRelaciones(?Devolucion $resultado, string $relacion): void
    {
        $this->assertNotNull($resultado);
        $this->assertTrue($resultado->relationLoaded($relacion));
    }
}

