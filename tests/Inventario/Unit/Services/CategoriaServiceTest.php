<?php

declare(strict_types=1);

namespace Tests\Inventario\Unit\Services;

use Tests\TestCase;
use App\Inventario\Services\Categoria\CategoriaService;
use App\Inventario\Interfaces\Repositories\Categoria\CategoriaRepositoryInterface;
use App\Models\Inventario\Categoria;
use App\Exceptions\CategoriaException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class CategoriaServiceTest extends TestCase
{
    protected CategoriaService $service;
    protected $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(CategoriaRepositoryInterface::class);

        $this->service = new CategoriaService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function servicio_puede_instanciarse(): void
    {
        $this->assertInstanceOf(CategoriaService::class, $this->service);
    }

    #[Test]
    public function puede_crear_categoria(): void
    {
        $datos = [
            'name' => 'Categoria Test',
        ];

        $categoriaMock = Mockery::mock(Categoria::class);
        $categoriaMock->id = 1;
        $categoriaMock->name = 'Categoria Test';
        $categoriaMock->shouldReceive('save')->once()->andReturn(true);
        $categoriaMock->shouldReceive('asociarATemaCategorias')->once();

        $service = new class($this->mockRepository) extends CategoriaService {
            protected function createCategoriaMock($datos) {
                $categoria = Mockery::mock(Categoria::class);
                $categoria->id = 1;
                $categoria->name = $datos['name'];
                $categoria->shouldReceive('save')->once()->andReturn(true);
                $categoria->shouldReceive('asociarATemaCategorias')->once();
                return $categoria;
            }
        };

        $this->markTestSkipped('Requiere refactorización del servicio para testear mejor');
    }

    #[Test]
    public function puede_actualizar_categoria(): void
    {
        $categoriaMock = Mockery::mock(Categoria::class);
        $categoriaMock->id = 1;

        $datos = [
            'name' => 'Categoria Actualizada',
        ];

        $this->mockRepository->shouldReceive('actualizar')
            ->once()
            ->with(1, Mockery::on(function ($argument) {
                return $argument['name'] === 'CATEGORIA ACTUALIZADA' &&
                       isset($argument['user_edit_id']);
            }))
            ->andReturn(true);

        $resultado = $this->service->actualizar($categoriaMock, $datos, 1);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function puede_eliminar_categoria_sin_productos(): void
    {
        $temaMock = Mockery::mock();
        $temaMock->id = 1;

        $categoriaMock = Mockery::mock(Categoria::class);
        $categoriaMock->id = 1;

        $this->mockRepository->shouldReceive('tieneProductos')
            ->once()
            ->with(1)
            ->andReturn(false);

        $this->mockRepository->shouldReceive('obtenerTemaCategorias')
            ->once()
            ->andReturn($temaMock);

        $this->mockRepository->shouldReceive('eliminar')
            ->once()
            ->with($categoriaMock, 1)
            ->andReturn(true);

        $resultado = $this->service->eliminar($categoriaMock);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function lanza_excepcion_al_eliminar_categoria_con_productos(): void
    {
        $this->expectException(CategoriaException::class);
        $this->expectExceptionMessage('No se puede eliminar la categoria porque está en uso.');

        $categoriaMock = Mockery::mock(Categoria::class);
        $categoriaMock->id = 1;

        $this->mockRepository->shouldReceive('tieneProductos')
            ->once()
            ->with(1)
            ->andReturn(true);

        $this->service->eliminar($categoriaMock);
    }

    #[Test]
    public function lanza_excepcion_si_no_existe_tema_categorias(): void
    {
        $this->expectException(CategoriaException::class);
        $this->expectExceptionMessage('No existe el tema "CATEGORIAS" en la base de datos.');

        $categoriaMock = Mockery::mock(Categoria::class);
        $categoriaMock->id = 1;

        $this->mockRepository->shouldReceive('tieneProductos')
            ->once()
            ->with(1)
            ->andReturn(false);

        $this->mockRepository->shouldReceive('obtenerTemaCategorias')
            ->once()
            ->andReturn(null);

        $this->service->eliminar($categoriaMock);
    }
}
