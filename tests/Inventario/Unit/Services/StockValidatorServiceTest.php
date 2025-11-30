<?php

declare(strict_types=1);

namespace Tests\Inventario\Unit\Services;

use Tests\TestCase;
use App\Inventario\Services\StockValidator\StockValidatorService;
use App\Inventario\Interfaces\Services\NotificationServiceInterface;
use App\Models\Inventario\Producto;
use App\Exceptions\OrdenException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class StockValidatorServiceTest extends TestCase
{
    protected StockValidatorService $service;
    protected $mockNotificationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockNotificationService = Mockery::mock(NotificationServiceInterface::class);

        $this->service = new StockValidatorService($this->mockNotificationService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function servicio_puede_instanciarse(): void
    {
        $this->assertInstanceOf(StockValidatorService::class, $this->service);
    }

    #[Test]
    public function detecta_producto_bajo_umbral_minimo(): void
    {
        $umbral = config('inventario.stock.umbral_minimo', 10);
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->cantidad = $umbral - 1;

        $resultado = $this->service->estaBajoUmbralMinimo($productoMock);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function detecta_producto_no_bajo_umbral_minimo(): void
    {
        $umbral = config('inventario.stock.umbral_minimo', 10);
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->cantidad = $umbral + 5;

        $resultado = $this->service->estaBajoUmbralMinimo($productoMock);

        $this->assertFalse($resultado);
    }

    #[Test]
    public function detecta_producto_en_nivel_critico(): void
    {
        $umbral = config('inventario.stock.umbral_critico', 5);
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->cantidad = $umbral - 1;

        $resultado = $this->service->estaNivelCritico($productoMock);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function valida_hay_stock_suficiente(): void
    {
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->cantidad = 10;

        $resultado = $this->service->hayStockSuficiente($productoMock, 5);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function detecta_stock_insuficiente(): void
    {
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->cantidad = 5;

        $resultado = $this->service->hayStockSuficiente($productoMock, 10);

        $this->assertFalse($resultado);
    }

    #[Test]
    public function lanza_excepcion_si_stock_insuficiente(): void
    {
        $this->expectException(OrdenException::class);

        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->cantidad = 5;
        $productoMock->producto = 'Producto Test';

        $this->service->validarStockSuficiente($productoMock, 10);
    }

    #[Test]
    public function no_lanza_excepcion_si_stock_suficiente(): void
    {
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->cantidad = 10;

        $this->service->validarStockSuficiente($productoMock, 5);

        $this->assertTrue(true);
    }

    #[Test]
    public function puede_calcular_porcentaje_stock(): void
    {
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->cantidad = 50;
        $stockMaximo = 100;

        $porcentaje = $this->service->calcularPorcentajeStock($productoMock, $stockMaximo);

        $this->assertEquals(50.0, $porcentaje);
    }

    #[Test]
    public function retorna_cero_si_stock_maximo_es_cero(): void
    {
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->cantidad = 50;
        $stockMaximo = 0;

        $porcentaje = $this->service->calcularPorcentajeStock($productoMock, $stockMaximo);

        $this->assertEquals(0.0, $porcentaje);
    }

    #[Test]
    public function puede_obtener_nivel_stock_critico(): void
    {
        $umbral = config('inventario.stock.umbral_critico', 5);
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->cantidad = $umbral - 1;

        $nivel = $this->service->obtenerNivelStock($productoMock);

        $this->assertEquals('critico', $nivel);
    }

    #[Test]
    public function puede_obtener_nivel_stock_bajo(): void
    {
        $umbralMinimo = config('inventario.stock.umbral_minimo', 10);
        $umbralCritico = config('inventario.stock.umbral_critico', 5);
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->cantidad = ($umbralMinimo + $umbralCritico) / 2;

        $nivel = $this->service->obtenerNivelStock($productoMock);

        $this->assertEquals('bajo', $nivel);
    }

    #[Test]
    public function puede_obtener_nivel_stock_alto(): void
    {
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->cantidad = 100;

        $nivel = $this->service->obtenerNivelStock($productoMock);

        $this->assertEquals('alto', $nivel);
    }

    #[Test]
    public function puede_obtener_umbral_minimo(): void
    {
        $umbral = $this->service->getUmbralMinimo();

        $this->assertIsInt($umbral);
        $this->assertGreaterThan(0, $umbral);
    }

    #[Test]
    public function puede_obtener_umbral_critico(): void
    {
        $umbral = $this->service->getUmbralCritico();

        $this->assertIsInt($umbral);
        $this->assertGreaterThan(0, $umbral);
    }

    #[Test]
    public function notifica_cuando_stock_cae_bajo_umbral(): void
    {
        $umbralMinimo = config('inventario.stock.umbral_minimo', 10);
        
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->cantidad = $umbralMinimo - 1;

        $this->mockNotificationService->shouldReceive('notificarStockBajo')
            ->once()
            ->with($productoMock, $umbralMinimo - 1, $umbralMinimo);

        $this->service->verificarYNotificarCambioStock($productoMock, $umbralMinimo + 5);

        $this->assertTrue(true);
    }
}
