<?php

declare(strict_types=1);

namespace Tests\Inventario\Unit\Services;

use Tests\TestCase;
use App\Inventario\Services\Carrito\CarritoService;
use App\Inventario\Interfaces\Repositories\Producto\ProductoRepositoryInterface;
use App\Models\Inventario\Producto;
use App\Exceptions\CarritoException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class CarritoServiceTest extends TestCase
{
    private const NOMBRE_PRODUCTO_TEST = 'Producto Test';
    private const ID_PRODUCTO_TEST = 1;
    private const ID_PRODUCTO_NO_EXISTE = 99999;

    protected CarritoService $service;
    protected $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(ProductoRepositoryInterface::class);

        $this->service = new CarritoService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function servicio_puede_instanciarse(): void
    {
        $this->assertInstanceOf(CarritoService::class, $this->service);
    }

    #[Test]
    public function puede_verificar_disponibilidad_de_productos(): void
    {
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->id = self::ID_PRODUCTO_TEST;
        $productoMock->name = self::NOMBRE_PRODUCTO_TEST;
        $productoMock->cantidad = 10;

        $items = [
            [
                'producto_id' => self::ID_PRODUCTO_TEST,
                'cantidad' => 5,
            ],
        ];

        $this->mockRepository->shouldReceive('encontrar')
            ->once()
            ->with(self::ID_PRODUCTO_TEST)
            ->andReturn($productoMock);

        $errores = $this->service->verificarDisponibilidad($items);

        $this->assertIsArray($errores);
        $this->assertEmpty($errores);
    }

    #[Test]
    public function detecta_errores_de_stock_insuficiente(): void
    {
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->id = self::ID_PRODUCTO_TEST;
        $productoMock->name = self::NOMBRE_PRODUCTO_TEST;
        $productoMock->cantidad = 5;

        $items = [
            [
                'producto_id' => self::ID_PRODUCTO_TEST,
                'cantidad' => 10,
            ],
        ];

        $this->mockRepository->shouldReceive('encontrar')
            ->once()
            ->with(self::ID_PRODUCTO_TEST)
            ->andReturn($productoMock);

        $errores = $this->service->verificarDisponibilidad($items);

        $this->assertNotEmpty($errores);
        $this->assertEquals(self::NOMBRE_PRODUCTO_TEST, $errores[0]['producto']);
        $this->assertEquals(10, $errores[0]['solicitado']);
        $this->assertEquals(5, $errores[0]['disponible']);
    }

    #[Test]
    public function lanza_excepcion_si_producto_no_existe(): void
    {
        $this->expectException(CarritoException::class);
        $this->expectExceptionMessage('Producto con ID ' . self::ID_PRODUCTO_NO_EXISTE . ' no encontrado.');

        $items = [
            [
                'producto_id' => self::ID_PRODUCTO_NO_EXISTE,
                'cantidad' => 5,
            ],
        ];

        $this->mockRepository->shouldReceive('encontrar')
            ->once()
            ->with(self::ID_PRODUCTO_NO_EXISTE)
            ->andReturn(null);

        $this->service->verificarDisponibilidad($items);
    }

    #[Test]
    public function puede_validar_item_individual(): void
    {
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->id = self::ID_PRODUCTO_TEST;
        $productoMock->name = self::NOMBRE_PRODUCTO_TEST;
        $productoMock->cantidad = 10;

        $this->mockRepository->shouldReceive('encontrar')
            ->once()
            ->with(self::ID_PRODUCTO_TEST)
            ->andReturn($productoMock);

        $resultado = $this->service->validarItem(self::ID_PRODUCTO_TEST, 5);

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['success']);
        $this->assertEquals('Cantidad válida', $resultado['message']);
        $this->assertEquals(self::ID_PRODUCTO_TEST, $resultado['producto']['id']);
    }

    #[Test]
    public function detecta_stock_insuficiente_en_item_individual(): void
    {
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->id = self::ID_PRODUCTO_TEST;
        $productoMock->name = self::NOMBRE_PRODUCTO_TEST;
        $productoMock->cantidad = 5;

        $this->mockRepository->shouldReceive('encontrar')
            ->once()
            ->with(self::ID_PRODUCTO_TEST)
            ->andReturn($productoMock);

        $resultado = $this->service->validarItem(self::ID_PRODUCTO_TEST, 10);

        $this->assertFalse($resultado['success']);
        $this->assertEquals('Stock insuficiente', $resultado['message']);
        $this->assertEquals(5, $resultado['stock_disponible']);
    }

    #[Test]
    public function lanza_excepcion_si_producto_no_existe_en_validar_item(): void
    {
        $this->expectException(CarritoException::class);
        $this->expectExceptionMessage('Producto no encontrado');

        $this->mockRepository->shouldReceive('encontrar')
            ->once()
            ->with(self::ID_PRODUCTO_NO_EXISTE)
            ->andReturn(null);

        $this->service->validarItem(self::ID_PRODUCTO_NO_EXISTE, 5);
    }

    #[Test]
    public function puede_obtener_productos_para_carrito(): void
    {
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->id = self::ID_PRODUCTO_TEST;
        $productoMock->name = self::NOMBRE_PRODUCTO_TEST;
        $productoMock->codigo_barras = '12345678901';
        $productoMock->imagen = 'img/producto.jpg';
        $productoMock->cantidad = 10;
        $productoMock->descripcion = 'Descripción';
        
        $categoriaMock = Mockery::mock()->makePartial();
        $categoriaMock->name = 'Categoria Test';
        $productoMock->categoria = $categoriaMock;
        
        $marcaMock = Mockery::mock()->makePartial();
        $marcaMock->name = 'Marca Test';
        $productoMock->marca = $marcaMock;

        $items = [
            ['id' => self::ID_PRODUCTO_TEST],
        ];

        $this->mockRepository->shouldReceive('encontrarConRelaciones')
            ->once()
            ->with(self::ID_PRODUCTO_TEST)
            ->andReturn($productoMock);

        $productos = $this->service->obtenerProductosParaCarrito($items);

        $this->assertNotEmpty($productos);
        $this->assertEquals(self::ID_PRODUCTO_TEST, $productos->first()['id']);
        $this->assertEquals(self::NOMBRE_PRODUCTO_TEST, $productos->first()['nombre']);
    }

    #[Test]
    public function ignora_items_sin_id_valido(): void
    {
        $items = [
            ['id' => null],
            ['producto_id' => null],
        ];

        $productos = $this->service->obtenerProductosParaCarrito($items);

        $this->assertEmpty($productos);
    }
}
