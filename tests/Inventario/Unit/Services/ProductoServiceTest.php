<?php

declare(strict_types=1);

namespace Tests\Inventario\Unit\Services;

use Tests\TestCase;
use App\Inventario\Services\Producto\ProductoService;
use App\Inventario\Interfaces\Repositories\Producto\ProductoRepositoryInterface;
use App\Inventario\Interfaces\Services\ImageServiceInterface;
use App\Inventario\Interfaces\Services\BarcodeServiceInterface;
use App\Inventario\Interfaces\Services\StockValidatorServiceInterface;
use App\Models\Inventario\Producto;
use Illuminate\Http\UploadedFile;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class ProductoServiceTest extends TestCase
{
    protected ProductoService $service;
    protected $mockRepository;
    protected $mockImageService;
    protected $mockBarcodeService;
    protected $mockStockValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(ProductoRepositoryInterface::class);
        $this->mockImageService = Mockery::mock(ImageServiceInterface::class);
        $this->mockBarcodeService = Mockery::mock(BarcodeServiceInterface::class);
        $this->mockStockValidator = Mockery::mock(StockValidatorServiceInterface::class);

        $this->service = new ProductoService(
            $this->mockRepository,
            $this->mockImageService,
            $this->mockBarcodeService,
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
        $this->assertInstanceOf(ProductoService::class, $this->service);
    }

    #[Test]
    public function puede_crear_producto(): void
    {
        $datos = [
            'producto' => 'Producto Test',
            'descripcion' => 'Descripción del producto',
            'cantidad' => 10,
        ];

        $productoMock = Mockery::mock(Producto::class);
        $productoMock->id = 1;
        $productoMock->producto = 'Producto Test';

        $this->mockBarcodeService->shouldReceive('resolverCodigoBarras')
            ->once()
            ->with(null)
            ->andReturn('12345678901');

        $this->mockImageService->shouldReceive('procesarImagen')
            ->once()
            ->with(null)
            ->andReturn('img/default.png');

        $this->mockRepository->shouldReceive('crear')
            ->once()
            ->with(Mockery::on(function ($argument) {
                return $argument['producto'] === 'Producto Test' &&
                       $argument['codigo_barras'] === '12345678901' &&
                       $argument['imagen'] === 'img/default.png' &&
                       isset($argument['user_create_id']) &&
                       isset($argument['user_update_id']);
            }))
            ->andReturn($productoMock);

        $resultado = $this->service->crear($datos, 1);

        $this->assertEquals(1, $resultado->id);
        $this->assertEquals('Producto Test', $resultado->producto);
    }

    #[Test]
    public function puede_crear_producto_con_codigo_barras(): void
    {
        $datos = [
            'producto' => 'Producto Test',
            'codigo_barras' => '98765432109',
        ];

        $productoMock = Mockery::mock(Producto::class);
        $productoMock->id = 1;

        $this->mockBarcodeService->shouldReceive('resolverCodigoBarras')
            ->once()
            ->with('98765432109')
            ->andReturn('98765432109');

        $this->mockImageService->shouldReceive('procesarImagen')
            ->once()
            ->with(null)
            ->andReturn('img/default.png');

        $this->mockRepository->shouldReceive('crear')
            ->once()
            ->andReturn($productoMock);

        $resultado = $this->service->crear($datos, 1);

        $this->assertInstanceOf(Producto::class, $resultado);
    }

    #[Test]
    public function puede_actualizar_producto(): void
    {
        $productoMock = Mockery::mock(Producto::class);
        $productoMock->cantidad = 10;
        $productoMock->shouldReceive('refresh')->once();

        $datos = [
            'producto' => 'Producto Actualizado',
            'cantidad' => 20,
        ];

        $this->mockImageService->shouldReceive('procesarImagenParaActualizacion')
            ->never();

        $this->mockBarcodeService->shouldReceive('normalizarCodigoBarras')
            ->never();

        $this->mockRepository->shouldReceive('actualizar')
            ->once()
            ->with($productoMock, Mockery::on(function ($argument) {
                return $argument['producto'] === 'Producto Actualizado' &&
                       $argument['cantidad'] === 20 &&
                       isset($argument['user_update_id']);
            }))
            ->andReturn(true);

        $this->mockStockValidator->shouldReceive('verificarYNotificarCambioStock')
            ->once()
            ->with($productoMock, 10);

        $resultado = $this->service->actualizar($productoMock, $datos, 1);

        $this->assertInstanceOf(Producto::class, $resultado);
    }

    #[Test]
    public function puede_actualizar_producto_con_nueva_imagen(): void
    {
        $productoMock = Mockery::mock(Producto::class);
        $productoMock->cantidad = 10;
        $productoMock->shouldReceive('refresh')->once();

        $imagen = UploadedFile::fake()->image('producto.jpg');
        $datos = [
            'producto' => 'Producto Actualizado',
            'imagen' => $imagen,
        ];

        $this->mockImageService->shouldReceive('procesarImagenParaActualizacion')
            ->once()
            ->with($imagen, $productoMock)
            ->andReturn('img/producto.jpg');

        $this->mockRepository->shouldReceive('actualizar')
            ->once()
            ->andReturn(true);

        $this->mockStockValidator->shouldReceive('verificarYNotificarCambioStock')
            ->once();

        $resultado = $this->service->actualizar($productoMock, $datos, 1);

        $this->assertInstanceOf(Producto::class, $resultado);
    }

    #[Test]
    public function puede_actualizar_codigo_barras_normalizado(): void
    {
        $productoMock = Mockery::mock(Producto::class);
        $productoMock->cantidad = 10;
        $productoMock->shouldReceive('refresh')->once();

        $datos = [
            'codigo_barras' => '12345678901',
        ];

        $this->mockBarcodeService->shouldReceive('normalizarCodigoBarras')
            ->once()
            ->with('12345678901')
            ->andReturn('12345678901');

        $this->mockRepository->shouldReceive('actualizar')
            ->once()
            ->with($productoMock, Mockery::on(function ($argument) {
                return $argument['codigo_barras'] === '12345678901';
            }))
            ->andReturn(true);

        $this->mockStockValidator->shouldReceive('verificarYNotificarCambioStock')
            ->once();

        $resultado = $this->service->actualizar($productoMock, $datos, 1);

        $this->assertInstanceOf(Producto::class, $resultado);
    }

    #[Test]
    public function genera_nuevo_codigo_barras_si_no_se_puede_normalizar(): void
    {
        $productoMock = Mockery::mock(Producto::class);
        $productoMock->cantidad = 10;
        $productoMock->shouldReceive('refresh')->once();

        $datos = [
            'codigo_barras' => '123',
        ];

        $this->mockBarcodeService->shouldReceive('normalizarCodigoBarras')
            ->once()
            ->with('123')
            ->andReturn(null);

        $this->mockBarcodeService->shouldReceive('generarSiguienteCodigoBarras')
            ->once()
            ->andReturn('00000000001');

        $this->mockRepository->shouldReceive('actualizar')
            ->once()
            ->with($productoMock, Mockery::on(function ($argument) {
                return $argument['codigo_barras'] === '00000000001';
            }))
            ->andReturn(true);

        $this->mockStockValidator->shouldReceive('verificarYNotificarCambioStock')
            ->once();

        $resultado = $this->service->actualizar($productoMock, $datos, 1);

        $this->assertInstanceOf(Producto::class, $resultado);
    }

    #[Test]
    public function puede_eliminar_producto(): void
    {
        $productoMock = Mockery::mock(Producto::class);

        $this->mockImageService->shouldReceive('eliminarImagenSiExiste')
            ->once()
            ->with($productoMock);

        $this->mockRepository->shouldReceive('eliminar')
            ->once()
            ->with($productoMock)
            ->andReturn(true);

        $resultado = $this->service->eliminar($productoMock);

        $this->assertTrue($resultado);
    }
}
