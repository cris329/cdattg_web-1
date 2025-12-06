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

    private const PRODUCTO_TEST = 'Producto Test';
    private const PRODUCTO_ACTUALIZADO = 'Producto Actualizado';
    private const CODIGO_BARRAS_TEST = '12345678901';
    private const CODIGO_BARRAS_ACTUALIZADO = '98765432109';
    private const CODIGO_BARRAS_INVALIDO = '123';
    private const CODIGO_BARRAS_GENERADO = '00000000001';
    private const IMAGEN_TEST = 'img/default.png';
    private const IMAGEN_PRODUCTO = 'img/producto.jpg';
    private const CANTIDAD_INICIAL = 10;
    private const CANTIDAD_ACTUALIZADA = 20;
    private const USER_ID = 1;

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
            'name' => self::PRODUCTO_TEST,
            'descripcion' => 'Descripción del producto',
            'cantidad' => self::CANTIDAD_INICIAL,
        ];

        /** @var Producto $productoMock */
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->id = 1;
        $productoMock->name = self::PRODUCTO_TEST;

        $this->mockBarcodeService->shouldReceive('resolverCodigoBarras')
            ->once()
            ->with(null)
            ->andReturn(self::CODIGO_BARRAS_TEST);

        $this->mockImageService->shouldReceive('procesarImagen')
            ->once()
            ->with(null)
            ->andReturn(self::IMAGEN_TEST);

        $this->mockRepository->shouldReceive('crear')
            ->once()
            ->with(Mockery::on(function ($argument) {
                return $argument['name'] === self::PRODUCTO_TEST &&
                       $argument['codigo_barras'] === self::CODIGO_BARRAS_TEST &&
                       $argument['imagen'] === self::IMAGEN_TEST &&
                       isset($argument['user_create_id']) &&
                       isset($argument['user_update_id']);
            }))
            ->andReturn($productoMock);

        $resultado = $this->service->crear($datos, self::USER_ID);

        $this->assertEquals(1, $resultado->id);
        $this->assertEquals(self::PRODUCTO_TEST, $resultado->name);
    }

    #[Test]
    public function puede_crear_producto_con_codigo_barras(): void
    {
        $datos = [
            'name' => self::PRODUCTO_TEST,
            'codigo_barras' => self::CODIGO_BARRAS_ACTUALIZADO,
        ];

        /** @var Producto $productoMock */
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->id = 1;

        $this->mockBarcodeService->shouldReceive('resolverCodigoBarras')
            ->once()
            ->with(self::CODIGO_BARRAS_ACTUALIZADO)
            ->andReturn(self::CODIGO_BARRAS_ACTUALIZADO);

        $this->mockImageService->shouldReceive('procesarImagen')
            ->once()
            ->with(null)
            ->andReturn(self::IMAGEN_TEST);

        $this->mockRepository->shouldReceive('crear')
            ->once()
            ->andReturn($productoMock);

        $resultado = $this->service->crear($datos, self::USER_ID);

        $this->assertInstanceOf(Producto::class, $resultado);
    }

    private function crearProductoMock(): Producto
    {
        /** @var Producto $productoMock */
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->cantidad = self::CANTIDAD_INICIAL;
        $productoMock->shouldReceive('refresh')->once();
        return $productoMock;
    }

    #[Test]
    public function puede_actualizar_producto(): void
    {
        $productoMock = $this->crearProductoMock();

        $datos = [
            'name' => self::PRODUCTO_ACTUALIZADO,
            'cantidad' => self::CANTIDAD_ACTUALIZADA,
        ];

        $this->mockImageService->shouldReceive('procesarImagenParaActualizacion')
            ->never();

        $this->mockBarcodeService->shouldReceive('normalizarCodigoBarras')
            ->never();

        $this->mockRepository->shouldReceive('actualizar')
            ->once()
            ->with($productoMock, Mockery::on(function ($argument) {
                return $argument['name'] === self::PRODUCTO_ACTUALIZADO &&
                       $argument['cantidad'] === self::CANTIDAD_ACTUALIZADA &&
                       isset($argument['user_update_id']);
            }))
            ->andReturn(true);

        $this->mockStockValidator->shouldReceive('verificarYNotificarCambioStock')
            ->once()
            ->with($productoMock, self::CANTIDAD_INICIAL);

        $resultado = $this->service->actualizar($productoMock, $datos, self::USER_ID);

        $this->assertInstanceOf(Producto::class, $resultado);
    }

    #[Test]
    public function puede_actualizar_producto_con_nueva_imagen(): void
    {
        $productoMock = $this->crearProductoMock();

        $imagen = UploadedFile::fake()->image('producto.jpg');
        $datos = [
            'name' => self::PRODUCTO_ACTUALIZADO,
            'imagen' => $imagen,
        ];

        $this->mockImageService->shouldReceive('procesarImagenParaActualizacion')
            ->once()
            ->with($imagen, $productoMock)
            ->andReturn(self::IMAGEN_PRODUCTO);

        $this->mockRepository->shouldReceive('actualizar')
            ->once()
            ->andReturn(true);

        $this->mockStockValidator->shouldReceive('verificarYNotificarCambioStock')
            ->once();

        $resultado = $this->service->actualizar($productoMock, $datos, self::USER_ID);

        $this->assertInstanceOf(Producto::class, $resultado);
    }

    #[Test]
    public function puede_actualizar_codigo_barras_normalizado(): void
    {
        $productoMock = $this->crearProductoMock();

        $datos = [
            'codigo_barras' => self::CODIGO_BARRAS_TEST,
        ];

        $this->mockBarcodeService->shouldReceive('normalizarCodigoBarras')
            ->once()
            ->with(self::CODIGO_BARRAS_TEST)
            ->andReturn(self::CODIGO_BARRAS_TEST);

        $this->mockRepository->shouldReceive('actualizar')
            ->once()
            ->with($productoMock, Mockery::on(function ($argument) {
                return $argument['codigo_barras'] === self::CODIGO_BARRAS_TEST;
            }))
            ->andReturn(true);

        $this->mockStockValidator->shouldReceive('verificarYNotificarCambioStock')
            ->once();

        $resultado = $this->service->actualizar($productoMock, $datos, self::USER_ID);

        $this->assertInstanceOf(Producto::class, $resultado);
    }

    #[Test]
    public function genera_nuevo_codigo_barras_si_no_se_puede_normalizar(): void
    {
        $productoMock = $this->crearProductoMock();

        $datos = [
            'codigo_barras' => self::CODIGO_BARRAS_INVALIDO,
        ];

        $this->mockBarcodeService->shouldReceive('normalizarCodigoBarras')
            ->once()
            ->with(self::CODIGO_BARRAS_INVALIDO)
            ->andReturn(null);

        $this->mockBarcodeService->shouldReceive('generarSiguienteCodigoBarras')
            ->once()
            ->andReturn(self::CODIGO_BARRAS_GENERADO);

        $this->mockRepository->shouldReceive('actualizar')
            ->once()
            ->with($productoMock, Mockery::on(function ($argument) {
                return $argument['codigo_barras'] === self::CODIGO_BARRAS_GENERADO;
            }))
            ->andReturn(true);

        $this->mockStockValidator->shouldReceive('verificarYNotificarCambioStock')
            ->once();

        $resultado = $this->service->actualizar($productoMock, $datos, self::USER_ID);

        $this->assertInstanceOf(Producto::class, $resultado);
    }

    #[Test]
    public function puede_eliminar_producto(): void
    {
        /** @var Producto $productoMock */
        $productoMock = Mockery::mock(Producto::class)->makePartial();

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
