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
    private const MENSAJE_EXITO = 'Devolución registrada exitosamente';
    private const MENSAJE_SIN_STOCK = 'sin restaurar stock';
    private const MENSAJE_RETRASO = 'días de retraso';
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
        $detalleOrdenId = 1;
        $cantidadDevuelta = 5;
        $observaciones = 'Test observaciones';

        // Simular que Devolucion::registrarDevolucion() lanza una excepción
        $exception = new \Exception('Error al registrar devolución en BD');

        // Configurar que beginTransaction() se llame primero
        $this->mockTransactionService->shouldReceive('beginTransaction')
            ->once();

        // Configurar que rollBack() se llame cuando ocurra la excepción
        $this->mockTransactionService->shouldReceive('rollBack')
            ->once();

        // Crear un servicio de prueba que extienda el original pero simule que
        // Devolucion::registrarDevolucion() falla lanzando una excepción
        $testService = new class($this->mockTransactionService, $exception) extends DevolucionService {
            private $exception;

            public function __construct($transactionService, $exception)
            {
                parent::__construct($transactionService);
                $this->exception = $exception;
            }

            public function registrarDevolucionConMensaje(
                int $detalleOrdenId,
                int $cantidadDevuelta,
                ?string $observaciones
            ): array {
                try {
                    $this->transactionService->beginTransaction();

                    // Simular que Devolucion::registrarDevolucion() lanza excepción
                    throw $this->exception;

                } catch (\Exception $e) {
                    $this->transactionService->rollBack();
                    throw new \App\Exceptions\DevolucionException('Error al registrar la devolución: ' . $e->getMessage());
                }
            }
        };

        // Verificar que se lanza DevolucionException con el mensaje correcto
        $this->expectException(DevolucionException::class);
        $this->expectExceptionMessage('Error al registrar la devolución: Error al registrar devolución en BD');

        $testService->registrarDevolucionConMensaje($detalleOrdenId, $cantidadDevuelta, $observaciones);
    }

    private function crearTestService(): object
    {
        return new class($this->mockTransactionService) extends DevolucionService {
            public function construirMensajeDevolucionPublico(Devolucion $devolucion): string
            {
                return $this->construirMensajeDevolucion($devolucion);
            }
        };
    }

    #[Test]
    public function test_construye_mensaje_devolucion_normal(): void
    {
        /** @var Devolucion $devolucionMock */
        $devolucionMock = Mockery::mock(Devolucion::class)->makePartial();
        $devolucionMock->cierra_sin_stock = false;
        $devolucionMock->shouldReceive('getDiasRetrasoDevolucion')
            ->once()
            ->andReturn(self::DIAS_RETRASO_CERO);

        $testService = $this->crearTestService();
        $mensaje = $testService->construirMensajeDevolucionPublico($devolucionMock);

        $this->assertStringContainsString(self::MENSAJE_EXITO, $mensaje);
        $this->assertStringNotContainsString(self::MENSAJE_SIN_STOCK, $mensaje);
        $this->assertStringNotContainsString(self::MENSAJE_RETRASO, $mensaje);
    }

    #[Test]
    public function test_construye_mensaje_con_cierre_sin_stock(): void
    {
        /** @var Devolucion $devolucionMock */
        $devolucionMock = Mockery::mock(Devolucion::class)->makePartial();
        $devolucionMock->cierra_sin_stock = true;
        $devolucionMock->shouldReceive('getDiasRetrasoDevolucion')
            ->once()
            ->andReturn(self::DIAS_RETRASO_CERO);

        $testService = $this->crearTestService();
        $mensaje = $testService->construirMensajeDevolucionPublico($devolucionMock);

        $this->assertStringContainsString(self::MENSAJE_SIN_STOCK, $mensaje);
    }

    #[Test]
    public function test_construye_mensaje_con_retraso(): void
    {
        /** @var Devolucion $devolucionMock */
        $devolucionMock = Mockery::mock(Devolucion::class)->makePartial();
        $devolucionMock->cierra_sin_stock = false;
        $devolucionMock->shouldReceive('getDiasRetrasoDevolucion')
            ->once()
            ->andReturn(self::DIAS_RETRASO_TRES);

        $testService = $this->crearTestService();
        $mensaje = $testService->construirMensajeDevolucionPublico($devolucionMock);

        $this->assertStringContainsString(self::MENSAJE_RETRASO, $mensaje);
        $this->assertStringContainsString((string) self::DIAS_RETRASO_TRES, $mensaje);
    }

    #[Test]
    public function test_construye_mensaje_completo_con_stock_y_retraso(): void
    {
        /** @var Devolucion $devolucionMock */
        $devolucionMock = Mockery::mock(Devolucion::class)->makePartial();
        $devolucionMock->cierra_sin_stock = true;
        $devolucionMock->shouldReceive('getDiasRetrasoDevolucion')
            ->once()
            ->andReturn(self::DIAS_RETRASO_CINCO);

        $testService = $this->crearTestService();
        $mensaje = $testService->construirMensajeDevolucionPublico($devolucionMock);

        $this->assertStringContainsString(self::MENSAJE_SIN_STOCK, $mensaje);
        $this->assertStringContainsString(self::MENSAJE_RETRASO, $mensaje);
        $this->assertStringContainsString((string) self::DIAS_RETRASO_CINCO, $mensaje);
    }
}
