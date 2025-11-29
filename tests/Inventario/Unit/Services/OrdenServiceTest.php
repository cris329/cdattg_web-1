<?php

declare(strict_types=1);

namespace Tests\Inventario\Unit\Services;

use Tests\TestCase;
use App\Inventario\Services\Orden\OrdenService;
use App\Inventario\Interfaces\Repositories\Orden\OrdenRepositoryInterface;
use App\Inventario\Interfaces\Repositories\Orden\DetalleOrdenRepositoryInterface;
use App\Inventario\Interfaces\Repositories\Producto\ProductoRepositoryInterface;
use App\Inventario\Interfaces\Services\NotificationServiceInterface;
use App\Inventario\Interfaces\Services\TransactionServiceInterface;
use App\Inventario\Interfaces\Services\StockValidatorServiceInterface;
use App\Inventario\Interfaces\Services\FormOptionsServiceInterface;
use App\Models\Inventario\Orden;
use App\Models\Inventario\DetalleOrden;
use App\Models\Inventario\Producto;
use App\Models\Parametro;
use App\Exceptions\OrdenException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class OrdenServiceTest extends TestCase
{
    protected OrdenService $service;
    protected $mockOrdenRepository;
    protected $mockDetalleOrdenRepository;
    protected $mockProductoRepository;
    protected $mockNotificationService;
    protected $mockTransactionService;
    protected $mockStockValidator;
    protected $mockFormOptionsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockOrdenRepository = Mockery::mock(OrdenRepositoryInterface::class);
        $this->mockDetalleOrdenRepository = Mockery::mock(DetalleOrdenRepositoryInterface::class);
        $this->mockProductoRepository = Mockery::mock(ProductoRepositoryInterface::class);
        $this->mockNotificationService = Mockery::mock(NotificationServiceInterface::class);
        $this->mockTransactionService = Mockery::mock(TransactionServiceInterface::class);
        $this->mockStockValidator = Mockery::mock(StockValidatorServiceInterface::class);
        $this->mockFormOptionsService = Mockery::mock(FormOptionsServiceInterface::class);

        $this->service = new OrdenService(
            $this->mockOrdenRepository,
            $this->mockDetalleOrdenRepository,
            $this->mockProductoRepository,
            $this->mockNotificationService,
            $this->mockTransactionService,
            $this->mockStockValidator
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
        $this->assertInstanceOf(OrdenService::class, $this->service);
    }

    #[Test]
    public function puede_verificar_si_orden_tiene_devoluciones(): void
    {
        $ordenMock = Mockery::mock(Orden::class);
        $detallesMock = Mockery::mock();
        $detallesMock->shouldReceive('whereHas')
            ->once()
            ->with('devoluciones')
            ->andReturnSelf();
        $detallesMock->shouldReceive('exists')
            ->once()
            ->andReturn(false);

        $ordenMock->shouldReceive('detalles')
            ->once()
            ->andReturn($detallesMock);

        $resultado = $this->service->tieneDevoluciones($ordenMock);

        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }
}
