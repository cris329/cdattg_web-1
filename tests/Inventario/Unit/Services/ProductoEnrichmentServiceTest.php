<?php

declare(strict_types=1);

namespace Tests\Inventario\Unit\Services;

use Tests\TestCase;
use App\Inventario\Services\ProductoEnrichment\ProductoEnrichmentService;
use App\Inventario\Interfaces\Repositories\Marca\MarcaRepositoryInterface;
use App\Inventario\Interfaces\Repositories\Categoria\CategoriaRepositoryInterface;
use App\Models\Inventario\Producto;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class ProductoEnrichmentServiceTest extends TestCase
{
    protected ProductoEnrichmentService $service;
    protected $mockMarcaRepository;
    protected $mockCategoriaRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockMarcaRepository = Mockery::mock(MarcaRepositoryInterface::class);
        $this->mockCategoriaRepository = Mockery::mock(CategoriaRepositoryInterface::class);

        $this->service = new ProductoEnrichmentService(
            $this->mockMarcaRepository,
            $this->mockCategoriaRepository
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
        $this->assertInstanceOf(ProductoEnrichmentService::class, $this->service);
    }

    #[Test]
    public function puede_enriquecer_producto_con_relaciones(): void
    {
        $marcaMock = Mockery::mock(\App\Models\Inventario\Marca::class)->makePartial();
        $marcaMock->id = 1;
        
        $categoriaMock = Mockery::mock(\App\Models\Inventario\Categoria::class)->makePartial();
        $categoriaMock->id = 1;

        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->marca_id = 1;
        $productoMock->categoria_id = 1;
        $productoMock->shouldReceive('setRelation')
            ->with('marca', $marcaMock)
            ->once();
        $productoMock->shouldReceive('setRelation')
            ->with('categoria', $categoriaMock)
            ->once();

        $this->mockMarcaRepository->shouldReceive('encontrar')
            ->once()
            ->with(1)
            ->andReturn($marcaMock);

        $this->mockCategoriaRepository->shouldReceive('encontrar')
            ->once()
            ->with(1)
            ->andReturn($categoriaMock);

        $this->service->enriquecerProducto($productoMock);

        $this->assertTrue(true);
    }

    #[Test]
    public function puede_enriquecer_coleccion_con_marcas_y_categorias(): void
    {
        $marcaMock = Mockery::mock(\App\Models\Inventario\Marca::class)->makePartial();
        $marcaMock->id = 1;
        
        $categoriaMock = Mockery::mock(\App\Models\Inventario\Categoria::class)->makePartial();
        $categoriaMock->id = 1;

        $productoMock1 = Mockery::mock(Producto::class)->makePartial();
        $productoMock1->marca_id = 1;
        $productoMock1->categoria_id = 1;
        $productoMock1->shouldReceive('setRelation')->twice();

        $productoMock2 = Mockery::mock(Producto::class)->makePartial();
        $productoMock2->marca_id = 1;
        $productoMock2->categoria_id = 1;
        $productoMock2->shouldReceive('setRelation')->twice();

        $productos = collect([$productoMock1, $productoMock2]);

        $marcasCollection = new \Illuminate\Database\Eloquent\Collection([1 => $marcaMock]);
        $categoriasCollection = new \Illuminate\Database\Eloquent\Collection([1 => $categoriaMock]);

        $this->mockMarcaRepository->shouldReceive('encontrarMultiples')
            ->once()
            ->with([1])
            ->andReturn($marcasCollection);

        $this->mockCategoriaRepository->shouldReceive('encontrarMultiples')
            ->once()
            ->with([1])
            ->andReturn($categoriasCollection);

        $this->service->enriquecerConMarcasYCategorias($productos);

        $this->assertTrue(true);
    }
}
