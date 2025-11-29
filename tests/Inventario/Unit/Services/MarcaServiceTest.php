<?php

declare(strict_types=1);

namespace Tests\Inventario\Unit\Services;

use Tests\TestCase;
use App\Inventario\Services\Marca\MarcaService;
use App\Inventario\Interfaces\Repositories\Marca\MarcaRepositoryInterface;
use App\Models\Inventario\Marca;
use App\Models\Tema;
use App\Exceptions\MarcaException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class MarcaServiceTest extends TestCase
{
    protected MarcaService $service;
    protected $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(MarcaRepositoryInterface::class);

        $this->service = new MarcaService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function servicio_puede_instanciarse(): void
    {
        $this->assertInstanceOf(MarcaService::class, $this->service);
    }

    #[Test]
    public function puede_actualizar_marca(): void
    {
        $marcaMock = Mockery::mock(Marca::class)->makePartial();
        $marcaMock->id = 1;

        $datos = [
            'name' => 'Marca Actualizada',
        ];

        $this->mockRepository->shouldReceive('actualizar')
            ->once()
            ->with(1, Mockery::on(function ($argument) {
                return $argument['name'] === 'MARCA ACTUALIZADA' &&
                       isset($argument['user_edit_id']);
            }))
            ->andReturn(true);

        $resultado = $this->service->actualizar($marcaMock, $datos, 1);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function puede_eliminar_marca_sin_productos(): void
    {
        $temaMock = Mockery::mock(Tema::class)->makePartial();
        $temaMock->id = 1;

        $marcaMock = Mockery::mock(Marca::class)->makePartial();
        $marcaMock->id = 1;

        $this->mockRepository->shouldReceive('tieneProductos')
            ->once()
            ->with(1)
            ->andReturn(false);

        $this->mockRepository->shouldReceive('obtenerTemaMarcas')
            ->once()
            ->andReturn($temaMock);

        $this->mockRepository->shouldReceive('eliminar')
            ->once()
            ->with($marcaMock, 1)
            ->andReturn(true);

        $resultado = $this->service->eliminar($marcaMock);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function lanza_excepcion_al_eliminar_marca_con_productos(): void
    {
        $this->expectException(MarcaException::class);
        $this->expectExceptionMessage('No se puede eliminar la marca porque está en uso.');

        $marcaMock = Mockery::mock(Marca::class)->makePartial();
        $marcaMock->id = 1;

        $this->mockRepository->shouldReceive('tieneProductos')
            ->once()
            ->with(1)
            ->andReturn(true);

        $this->service->eliminar($marcaMock);
    }

    #[Test]
    public function lanza_excepcion_si_no_existe_tema_marcas(): void
    {
        $this->expectException(MarcaException::class);
        $this->expectExceptionMessage('No existe el tema "MARCAS" en la base de datos.');

        $marcaMock = Mockery::mock(Marca::class)->makePartial();
        $marcaMock->id = 1;

        $this->mockRepository->shouldReceive('tieneProductos')
            ->once()
            ->with(1)
            ->andReturn(false);

        $this->mockRepository->shouldReceive('obtenerTemaMarcas')
            ->once()
            ->andReturn(null);

        $this->service->eliminar($marcaMock);
    }
}
