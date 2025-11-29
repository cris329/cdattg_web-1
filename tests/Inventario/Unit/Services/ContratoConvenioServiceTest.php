<?php

declare(strict_types=1);

namespace Tests\Inventario\Unit\Services;

use Tests\TestCase;
use App\Inventario\Services\ContratoConvenio\ContratoConvenioService;
use App\Inventario\Interfaces\Repositories\ContratoConvenio\ContratoConvenioRepositoryInterface;
use App\Models\Inventario\ContratoConvenio;
use App\Exceptions\ContratoConvenioException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class ContratoConvenioServiceTest extends TestCase
{
    protected ContratoConvenioService $service;
    protected $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(ContratoConvenioRepositoryInterface::class);

        $this->service = new ContratoConvenioService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function servicio_puede_instanciarse(): void
    {
        $this->assertInstanceOf(ContratoConvenioService::class, $this->service);
    }

    #[Test]
    public function puede_crear_contrato_convenio(): void
    {
        $datos = [
            'numero_contrato' => 'CONT-001',
            'proveedor_id' => 1,
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addYear(),
        ];

        $contratoMock = Mockery::mock(ContratoConvenio::class);
        $contratoMock->id = 1;
        $contratoMock->numero_contrato = 'CONT-001';

        $this->mockRepository->shouldReceive('crear')
            ->once()
            ->with(Mockery::on(function ($argument) {
                return $argument['numero_contrato'] === 'CONT-001' &&
                       isset($argument['user_create_id']) &&
                       isset($argument['user_edit_id']);
            }))
            ->andReturn($contratoMock);

        $resultado = $this->service->crear($datos, 1);

        $this->assertInstanceOf(ContratoConvenio::class, $resultado);
        $this->assertEquals(1, $resultado->id);
        $this->assertEquals('CONT-001', $resultado->numero_contrato);
    }

    #[Test]
    public function puede_actualizar_contrato_convenio(): void
    {
        $contratoMock = Mockery::mock(ContratoConvenio::class);
        $contratoMock->id = 1;

        $datos = [
            'numero_contrato' => 'CONT-002',
        ];

        $this->mockRepository->shouldReceive('actualizar')
            ->once()
            ->with(1, Mockery::on(function ($argument) {
                return $argument['numero_contrato'] === 'CONT-002' &&
                       isset($argument['user_edit_id']);
            }))
            ->andReturn(true);

        $resultado = $this->service->actualizar($contratoMock, $datos, 1);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function puede_eliminar_contrato_convenio_sin_productos(): void
    {
        $contratoMock = Mockery::mock(ContratoConvenio::class);
        $contratoMock->id = 1;

        $this->mockRepository->shouldReceive('tieneProductos')
            ->once()
            ->with(1)
            ->andReturn(false);

        $this->mockRepository->shouldReceive('eliminar')
            ->once()
            ->with(1)
            ->andReturn(true);

        $resultado = $this->service->eliminar($contratoMock);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function lanza_excepcion_al_eliminar_contrato_convenio_con_productos(): void
    {
        $this->expectException(ContratoConvenioException::class);
        $this->expectExceptionMessage('No se puede eliminar el Contrato/Convenio porque está en uso.');

        $contratoMock = Mockery::mock(ContratoConvenio::class);
        $contratoMock->id = 1;

        $this->mockRepository->shouldReceive('tieneProductos')
            ->once()
            ->with(1)
            ->andReturn(true);

        $this->service->eliminar($contratoMock);
    }
}
