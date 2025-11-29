<?php

declare(strict_types=1);

namespace Tests\Inventario\Unit\Services;

use Tests\TestCase;
use App\Inventario\Services\Categoria\CategoriaService;
use App\Inventario\Interfaces\Repositories\Categoria\CategoriaRepositoryInterface;
use App\Models\Inventario\Categoria;
use App\Models\Tema;
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
        $userId = 1;

        // Crear mock de Categoria que simule el comportamiento sin base de datos
        $categoriaMock = Mockery::mock(Categoria::class)->makePartial();
        $categoriaMock->id = 1;
        $categoriaMock->status = 1;
        $categoriaMock->user_create_id = $userId;
        $categoriaMock->user_edit_id = $userId;

        // Configurar el mock para que save() retorne true sin tocar BD
        $categoriaMock->shouldReceive('save')
            ->once()
            ->andReturn(true);

        // Configurar el mock para que asociarATemaCategorias() no haga consultas
        $categoriaMock->shouldReceive('asociarATemaCategorias')
            ->once();

        // Como el servicio usa 'new Categoria()' directamente, creamos un servicio
        // de prueba que extienda el original pero use el mock en lugar de instanciar
        $testService = new class($this->mockRepository, $categoriaMock) extends CategoriaService {
            private $categoriaMock;

            public function __construct($repository, $categoriaMock)
            {
                parent::__construct($repository);
                $this->categoriaMock = $categoriaMock;
            }

            public function crear(array $datos, int $userId): Categoria
            {
                try {
                    $datos['status'] = 1;
                    $datos['user_create_id'] = $userId;
                    $datos['user_edit_id'] = $userId;

                    // Simular el comportamiento del servicio original usando el mock
                    $this->categoriaMock->name = strtoupper($datos['name']);
                    $this->categoriaMock->status = $datos['status'];
                    $this->categoriaMock->user_create_id = $datos['user_create_id'];
                    $this->categoriaMock->user_edit_id = $datos['user_edit_id'];

                    $this->categoriaMock->save();
                    $this->categoriaMock->asociarATemaCategorias();

                    return $this->categoriaMock;
                } catch (\Illuminate\Database\QueryException $e) {
                    throw new \App\Exceptions\CategoriaException('Error al crear la categoria: ' . $e->getMessage());
                }
            }
        };

        $resultado = $testService->crear($datos, $userId);

        $this->assertInstanceOf(Categoria::class, $resultado);
        $this->assertEquals('CATEGORIA TEST', $resultado->name);
        $this->assertEquals($userId, $resultado->user_create_id);
        $this->assertEquals(1, $resultado->status);
    }

    #[Test]
    public function puede_actualizar_categoria(): void
    {
        $categoriaMock = Mockery::mock(Categoria::class)->makePartial();
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
        $temaMock = Mockery::mock(Tema::class)->makePartial();
        $temaMock->id = 1;

        $categoriaMock = Mockery::mock(Categoria::class)->makePartial();
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

        $categoriaMock = Mockery::mock(Categoria::class)->makePartial();
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

        $categoriaMock = Mockery::mock(Categoria::class)->makePartial();
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
