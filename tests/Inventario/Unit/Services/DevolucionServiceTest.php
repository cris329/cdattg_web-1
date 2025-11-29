<?php

declare(strict_types=1);

namespace Tests\Inventario\Unit\Services;

use Tests\TestCase;
use App\Inventario\Services\Devolucion\DevolucionService;
use App\Inventario\Interfaces\Services\TransactionServiceInterface;
use App\Models\Inventario\Devolucion;
use App\Exceptions\DevolucionException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class DevolucionServiceTest extends TestCase
{
    private const METODO_CONSTRUIR_MENSAJE = 'construirMensajeDevolucion';
    private const MENSAJE_EXITO = 'Devolución registrada exitosamente';
    private const MENSAJE_SIN_STOCK = 'sin restaurar stock';
    private const MENSAJE_RETRASO = 'días de retraso';
    private const MENSAJE_ERROR_REGISTRO = 'Error al registrar la devolución';
    private const DIAS_RETRASO_CERO = 0;
    private const DIAS_RETRASO_TRES = 3;
    private const DIAS_RETRASO_CINCO = 5;

    protected DevolucionService $service;
    protected $mockTransactionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockTransactionService = Mockery::mock(TransactionServiceInterface::class);

        $this->service = new DevolucionService($this->mockTransactionService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function servicio_puede_instanciarse(): void
    {
        $this->assertInstanceOf(DevolucionService::class, $this->service);
    }

    #[Test]
    public function hace_rollback_si_registro_falla(): void
    {
        $this->expectException(DevolucionException::class);
        $this->expectExceptionMessage(self::MENSAJE_ERROR_REGISTRO);

        $this->mockTransactionService->shouldReceive('beginTransaction')
            ->once();

        $this->mockTransactionService->shouldReceive('rollBack')
            ->once();

        $this->markTestSkipped('Este test requiere base de datos porque Devolucion::registrarDevolucion es un método estático que hace consultas. Debería moverse a Feature test.');
    }

    #[Test]
    public function test_construye_mensaje_devolucion_normal(): void
    {
        $devolucionMock = Mockery::mock(Devolucion::class);
        $devolucionMock->cierra_sin_stock = false;
        $devolucionMock->shouldReceive('getDiasRetrasoDevolucion')
            ->once()
            ->andReturn(self::DIAS_RETRASO_CERO);

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod(self::METODO_CONSTRUIR_MENSAJE);
        $method->setAccessible(true);

        $mensaje = $method->invoke($this->service, $devolucionMock);

        $this->assertStringContainsString(self::MENSAJE_EXITO, $mensaje);
        $this->assertStringNotContainsString(self::MENSAJE_SIN_STOCK, $mensaje);
        $this->assertStringNotContainsString(self::MENSAJE_RETRASO, $mensaje);
    }

    #[Test]
    public function test_construye_mensaje_con_cierre_sin_stock(): void
    {
        $devolucionMock = Mockery::mock(Devolucion::class);
        $devolucionMock->cierra_sin_stock = true;
        $devolucionMock->shouldReceive('getDiasRetrasoDevolucion')
            ->once()
            ->andReturn(self::DIAS_RETRASO_CERO);

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod(self::METODO_CONSTRUIR_MENSAJE);
        $method->setAccessible(true);

        $mensaje = $method->invoke($this->service, $devolucionMock);

        $this->assertStringContainsString(self::MENSAJE_SIN_STOCK, $mensaje);
    }

    #[Test]
    public function test_construye_mensaje_con_retraso(): void
    {
        $devolucionMock = Mockery::mock(Devolucion::class);
        $devolucionMock->cierra_sin_stock = false;
        $devolucionMock->shouldReceive('getDiasRetrasoDevolucion')
            ->once()
            ->andReturn(self::DIAS_RETRASO_TRES);

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod(self::METODO_CONSTRUIR_MENSAJE);
        $method->setAccessible(true);

        $mensaje = $method->invoke($this->service, $devolucionMock);

        $this->assertStringContainsString(self::MENSAJE_RETRASO, $mensaje);
        $this->assertStringContainsString((string) self::DIAS_RETRASO_TRES, $mensaje);
    }

    #[Test]
    public function test_construye_mensaje_completo_con_stock_y_retraso(): void
    {
        $devolucionMock = Mockery::mock(Devolucion::class);
        $devolucionMock->cierra_sin_stock = true;
        $devolucionMock->shouldReceive('getDiasRetrasoDevolucion')
            ->once()
            ->andReturn(self::DIAS_RETRASO_CINCO);

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod(self::METODO_CONSTRUIR_MENSAJE);
        $method->setAccessible(true);

        $mensaje = $method->invoke($this->service, $devolucionMock);

        $this->assertStringContainsString(self::MENSAJE_SIN_STOCK, $mensaje);
        $this->assertStringContainsString(self::MENSAJE_RETRASO, $mensaje);
        $this->assertStringContainsString((string) self::DIAS_RETRASO_CINCO, $mensaje);
    }
}
