<?php

declare(strict_types=1);

namespace Tests\Inventario\Unit\Services;

use Tests\TestCase;
use App\Inventario\Services\Aprobacion\AprobacionService;
use App\Inventario\Interfaces\Repositories\Aprobacion\AprobacionRepositoryInterface;
use App\Inventario\Interfaces\Repositories\Orden\DetalleOrdenRepositoryInterface;
use App\Inventario\Interfaces\Repositories\Orden\OrdenRepositoryInterface;
use App\Inventario\Interfaces\Repositories\Producto\ProductoRepositoryInterface;
use App\Inventario\Interfaces\Services\TransactionServiceInterface;
use App\Inventario\Interfaces\Services\StockValidatorServiceInterface;
use App\Inventario\Interfaces\Services\FormOptionsServiceInterface;
use App\Models\Inventario\DetalleOrden;
use App\Models\Inventario\Orden;
use App\Models\Inventario\Producto;
use App\Models\Parametro;
use App\Exceptions\AprobacionException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class AprobacionServiceTest extends TestCase
{
    private const TEMA_ESTADOS_ORDEN = 'ESTADOS DE ORDEN';
    private const ESTADO_EN_ESPERA = 'EN ESPERA';
    private const ESTADO_APROBADA = 'APROBADA';
    private const ESTADO_RECHAZADA = 'RECHAZADA';
    private const ID_ESTADO_EN_ESPERA = 1;
    private const ID_ESTADO_APROBADA = 2;
    private const ID_ESTADO_RECHAZADA = 3;
    private const ID_DETALLE_ORDEN = 1;
    private const ID_ORDEN = 1;

    protected AprobacionService $service;
    protected $mockAprobacionRepository;
    protected $mockDetalleOrdenRepository;
    protected $mockOrdenRepository;
    protected $mockProductoRepository;
    protected $mockTransactionService;
    protected $mockStockValidator;
    protected $mockFormOptionsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockAprobacionRepository = Mockery::mock(AprobacionRepositoryInterface::class);
        $this->mockDetalleOrdenRepository = Mockery::mock(DetalleOrdenRepositoryInterface::class);
        $this->mockOrdenRepository = Mockery::mock(OrdenRepositoryInterface::class);
        $this->mockProductoRepository = Mockery::mock(ProductoRepositoryInterface::class);
        $this->mockTransactionService = Mockery::mock(TransactionServiceInterface::class);
        $this->mockStockValidator = Mockery::mock(StockValidatorServiceInterface::class);
        $this->mockFormOptionsService = Mockery::mock(FormOptionsServiceInterface::class);

        $this->service = new AprobacionService(
            $this->mockAprobacionRepository,
            $this->mockDetalleOrdenRepository,
            $this->mockOrdenRepository,
            $this->mockProductoRepository,
            $this->mockTransactionService,
            $this->mockStockValidator,
            $this->mockFormOptionsService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function servicio_puede_instanciarse(): void
    {
        $this->assertInstanceOf(AprobacionService::class, $this->service);
    }

    #[Test]
    public function puede_obtener_estado_en_espera(): void
    {
        $estadoMock = Mockery::mock(Parametro::class);
        $estadoMock->id = self::ID_ESTADO_EN_ESPERA;

        $this->mockFormOptionsService->shouldReceive('obtenerEstadoOrdenPorNombre')
            ->once()
            ->with(self::ESTADO_EN_ESPERA, self::TEMA_ESTADOS_ORDEN)
            ->andReturn($estadoMock);

        $resultado = $this->service->obtenerEstadoEnEspera();

        $this->assertInstanceOf(Parametro::class, $resultado);
        $this->assertEquals(self::ID_ESTADO_EN_ESPERA, $resultado->id);
    }

    #[Test]
    public function puede_obtener_estado_aprobada(): void
    {
        $estadoMock = Mockery::mock(Parametro::class);
        $estadoMock->id = self::ID_ESTADO_APROBADA;

        $this->mockFormOptionsService->shouldReceive('obtenerEstadoOrdenPorNombre')
            ->once()
            ->with(self::ESTADO_APROBADA, self::TEMA_ESTADOS_ORDEN)
            ->andReturn($estadoMock);

        $resultado = $this->service->obtenerEstadoAprobada();

        $this->assertInstanceOf(Parametro::class, $resultado);
    }

    #[Test]
    public function lanza_excepcion_si_estado_aprobada_no_existe(): void
    {
        $this->expectException(AprobacionException::class);

        $this->mockFormOptionsService->shouldReceive('obtenerEstadoOrdenPorNombre')
            ->once()
            ->with(self::ESTADO_APROBADA, self::TEMA_ESTADOS_ORDEN)
            ->andReturn(null);

        $this->service->obtenerEstadoAprobada();
    }

    #[Test]
    public function puede_obtener_estado_rechazada(): void
    {
        $estadoMock = Mockery::mock(Parametro::class);
        $estadoMock->id = self::ID_ESTADO_RECHAZADA;

        $this->mockFormOptionsService->shouldReceive('obtenerEstadoOrdenPorNombre')
            ->once()
            ->with(self::ESTADO_RECHAZADA, self::TEMA_ESTADOS_ORDEN)
            ->andReturn($estadoMock);

        $resultado = $this->service->obtenerEstadoRechazada();

        $this->assertInstanceOf(Parametro::class, $resultado);
    }

    #[Test]
    public function puede_obtener_detalles_pendientes(): void
    {
        $estadoMock = Mockery::mock(Parametro::class);
        $estadoMock->id = self::ID_ESTADO_EN_ESPERA;

        $detallesMock = collect([]);

        $this->mockFormOptionsService->shouldReceive('obtenerEstadoOrdenPorNombre')
            ->once()
            ->with(self::ESTADO_EN_ESPERA, self::TEMA_ESTADOS_ORDEN)
            ->andReturn($estadoMock);

        $this->mockOrdenRepository->shouldReceive('obtenerDetallesPendientes')
            ->once()
            ->with(self::ID_ESTADO_EN_ESPERA)
            ->andReturn($detallesMock);

        $resultado = $this->service->obtenerDetallesPendientes();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $resultado);
    }

    #[Test]
    public function retorna_collection_vacia_si_no_hay_estado_en_espera(): void
    {
        $this->mockFormOptionsService->shouldReceive('obtenerEstadoOrdenPorNombre')
            ->once()
            ->with(self::ESTADO_EN_ESPERA, self::TEMA_ESTADOS_ORDEN)
            ->andReturn(null);

        $resultado = $this->service->obtenerDetallesPendientes();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $resultado);
        $this->assertTrue($resultado->isEmpty());
    }

    #[Test]
    public function puede_encontrar_detalle_con_relaciones(): void
    {
        $detalleMock = Mockery::mock(DetalleOrden::class);
        $detalleMock->id = self::ID_DETALLE_ORDEN;

        $this->mockDetalleOrdenRepository->shouldReceive('encontrarConRelaciones')
            ->once()
            ->with(self::ID_DETALLE_ORDEN)
            ->andReturn($detalleMock);

        $resultado = $this->service->encontrarDetalleConRelaciones(self::ID_DETALLE_ORDEN);

        $this->assertInstanceOf(DetalleOrden::class, $resultado);
        $this->assertEquals(self::ID_DETALLE_ORDEN, $resultado->id);
    }

    #[Test]
    public function puede_encontrar_orden_con_detalles_y_devoluciones(): void
    {
        $ordenMock = Mockery::mock(Orden::class);
        $ordenMock->id = self::ID_ORDEN;

        $this->mockOrdenRepository->shouldReceive('encontrarConDetallesYDevoluciones')
            ->once()
            ->with(self::ID_ORDEN)
            ->andReturn($ordenMock);

        $resultado = $this->service->encontrarOrdenConDetallesYDevoluciones(self::ID_ORDEN);

        $this->assertInstanceOf(Orden::class, $resultado);
        $this->assertEquals(self::ID_ORDEN, $resultado->id);
    }
}
