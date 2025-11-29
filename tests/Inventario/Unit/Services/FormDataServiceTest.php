<?php

declare(strict_types=1);

namespace Tests\Inventario\Unit\Services;

use Tests\TestCase;
use App\Inventario\Services\FormData\FormDataService;
use App\Inventario\Interfaces\Repositories\ContratoConvenio\ContratoConvenioRepositoryInterface;
use App\Inventario\Interfaces\Repositories\Proveedor\ProveedorRepositoryInterface;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class FormDataServiceTest extends TestCase
{
    protected FormDataService $service;
    protected $mockContratoRepository;
    protected $mockProveedorRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockContratoRepository = Mockery::mock(ContratoConvenioRepositoryInterface::class);
        $this->mockProveedorRepository = Mockery::mock(ProveedorRepositoryInterface::class);

        $this->service = new FormDataService(
            $this->mockContratoRepository,
            $this->mockProveedorRepository
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
        $this->assertInstanceOf(FormDataService::class, $this->service);
    }

    #[Test]
    public function puede_obtener_datos_formulario(): void
    {
        $this->mockContratoRepository->shouldReceive('obtenerTodos')
            ->once()
            ->andReturn(collect([]));

        $this->mockProveedorRepository->shouldReceive('obtenerTodos')
            ->once()
            ->andReturn(collect([]));

        $datos = $this->service->obtenerDatosFormulario();

        $this->assertIsArray($datos);
        $this->assertArrayHasKey('contratosConvenios', $datos);
        $this->assertArrayHasKey('ambientes', $datos);
        $this->assertArrayHasKey('proveedores', $datos);
    }

    #[Test]
    public function puede_obtener_contratos_convenios(): void
    {
        $contratosMock = collect([]);

        $this->mockContratoRepository->shouldReceive('obtenerTodos')
            ->once()
            ->andReturn($contratosMock);

        $contratos = $this->service->obtenerContratosConvenios();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $contratos);
    }

    #[Test]
    public function puede_obtener_proveedores(): void
    {
        $proveedoresMock = collect([]);

        $this->mockProveedorRepository->shouldReceive('obtenerTodos')
            ->once()
            ->andReturn($proveedoresMock);

        $proveedores = $this->service->obtenerProveedores();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $proveedores);
    }
}
