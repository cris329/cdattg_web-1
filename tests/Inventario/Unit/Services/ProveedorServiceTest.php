<?php

declare(strict_types=1);

namespace Tests\Inventario\Unit\Services;

use Tests\TestCase;
use App\Inventario\Services\Proveedor\ProveedorService;
use App\Inventario\Interfaces\Repositories\Proveedor\ProveedorRepositoryInterface;
use App\Models\Inventario\Proveedor;
use App\Exceptions\ProveedorException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class ProveedorServiceTest extends TestCase
{
    private const PROVEEDOR_TEST = 'Proveedor Test';
    private const PROVEEDOR_ACTUALIZADO = 'Proveedor Actualizado';
    protected ProveedorService $service;
    protected $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(ProveedorRepositoryInterface::class);

        $this->service = new ProveedorService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function servicio_puede_instanciarse(): void
    {
        $this->assertInstanceOf(ProveedorService::class, $this->service);
    }

    #[Test]
    public function puede_crear_proveedor(): void
    {
        $datos = [
            'nombre' => self::PROVEEDOR_TEST,
            'nit' => '123456789',
            'telefono' => '1234567890',
        ];

        $proveedorMock = Mockery::mock(Proveedor::class)->makePartial();
        $proveedorMock->id = 1;
        $proveedorMock->nombre = self::PROVEEDOR_TEST;

        $this->mockRepository->shouldReceive('crear')
            ->once()
            ->with(Mockery::on(function ($argument) {
                return $argument['nombre'] === self::PROVEEDOR_TEST &&
                       isset($argument['user_create_id']) &&
                       isset($argument['user_update_id']);
            }))
            ->andReturn($proveedorMock);

        $resultado = $this->service->crear($datos, 1);

        $this->assertInstanceOf(Proveedor::class, $resultado);
        $this->assertEquals(1, $resultado->id);
        $this->assertEquals(self::PROVEEDOR_TEST, $resultado->nombre);
    }

    #[Test]
    public function puede_actualizar_proveedor(): void
    {
        /** @var Proveedor $proveedorMock */
        $proveedorMock = Mockery::mock(Proveedor::class)->makePartial();
        $proveedorMock->id = 1;

        $datos = [
            'nombre' => self::PROVEEDOR_ACTUALIZADO,
        ];

        $this->mockRepository->shouldReceive('actualizar')
            ->once()
            ->with(1, Mockery::on(function ($argument) {
                return $argument['nombre'] === self::PROVEEDOR_ACTUALIZADO &&
                       isset($argument['user_update_id']);
            }))
            ->andReturn(true);

        $resultado = $this->service->actualizar($proveedorMock, $datos, 1);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function puede_eliminar_proveedor_sin_relaciones(): void
    {
        /** @var Proveedor $proveedorMock */
        $proveedorMock = Mockery::mock(Proveedor::class)->makePartial();
        $proveedorMock->id = 1;

        $this->mockRepository->shouldReceive('tieneContratos')
            ->once()
            ->with(1)
            ->andReturn(false);

        $this->mockRepository->shouldReceive('tieneProductos')
            ->once()
            ->with(1)
            ->andReturn(false);

        $this->mockRepository->shouldReceive('eliminar')
            ->once()
            ->with(1)
            ->andReturn(true);

        $resultado = $this->service->eliminar($proveedorMock);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function lanza_excepcion_al_eliminar_proveedor_con_contratos(): void
    {
        $this->expectException(ProveedorException::class);
        $this->expectExceptionMessage('No se puede eliminar el proveedor porque tiene contratos asociados.');

        /** @var Proveedor $proveedorMock */
        $proveedorMock = Mockery::mock(Proveedor::class)->makePartial();
        $proveedorMock->id = 1;

        $this->mockRepository->shouldReceive('tieneContratos')
            ->once()
            ->with(1)
            ->andReturn(true);

        $this->service->eliminar($proveedorMock);
    }

    #[Test]
    public function lanza_excepcion_al_eliminar_proveedor_con_productos(): void
    {
        $this->expectException(ProveedorException::class);
        $this->expectExceptionMessage('No se puede eliminar el proveedor porque tiene productos asociados.');

        /** @var Proveedor $proveedorMock */
        $proveedorMock = Mockery::mock(Proveedor::class)->makePartial();
        $proveedorMock->id = 1;

        $this->mockRepository->shouldReceive('tieneContratos')
            ->once()
            ->with(1)
            ->andReturn(false);

        $this->mockRepository->shouldReceive('tieneProductos')
            ->once()
            ->with(1)
            ->andReturn(true);

        $this->service->eliminar($proveedorMock);
    }
}
