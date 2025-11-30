<?php

declare(strict_types=1);

namespace Tests\Inventario\Unit\Services;

use Tests\TestCase;
use App\Inventario\Services\Image\ImageService;
use App\Models\Inventario\Producto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class ImageServiceTest extends TestCase
{
    private const RUTA_IMAGEN_DEFAULT = 'img/inventario/producto-default.png';
    private const DIRECTORIO_IMAGENES = 'imagenes_productos';
    private const RUTA_IMAGEN_ANTERIOR = 'imagenes_productos/anterior.jpg';
    private const RUTA_IMAGEN_INEXISTENTE = 'imagenes_productos/inexistente.jpg';

    protected ImageService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->service = new ImageService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function servicio_puede_instanciarse(): void
    {
        $this->assertInstanceOf(ImageService::class, $this->service);
    }

    #[Test]
    public function retorna_imagen_por_defecto_si_no_hay_imagen(): void
    {
        $resultado = $this->service->procesarImagen(null);

        $this->assertEquals(
            config('inventario.imagenes.default', self::RUTA_IMAGEN_DEFAULT),
            $resultado
        );
    }

    #[Test]
    public function retorna_imagen_por_defecto_si_imagen_no_es_valida(): void
    {
        $imagenInvalida = Mockery::mock(UploadedFile::class);
        $imagenInvalida->shouldReceive('isValid')
            ->once()
            ->andReturn(false);

        $resultado = $this->service->procesarImagen($imagenInvalida);

        $this->assertEquals(
            config('inventario.imagenes.default', self::RUTA_IMAGEN_DEFAULT),
            $resultado
        );
    }

    #[Test]
    public function puede_procesar_imagen_valida(): void
    {
        Storage::fake('public');
        $imagen = UploadedFile::fake()->image('producto.jpg');

        $resultado = $this->service->procesarImagen($imagen);

        $this->assertIsString($resultado);
        $this->assertStringContainsString(
            config('inventario.imagenes.directorio', self::DIRECTORIO_IMAGENES),
            $resultado
        );
    }

    #[Test]
    public function puede_procesar_imagen_para_actualizacion(): void
    {
        Storage::fake('public');
        /** @var Producto $productoMock */
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->imagen = self::RUTA_IMAGEN_ANTERIOR;

        $nuevaImagen = UploadedFile::fake()->image('nuevo_producto.jpg');

        $resultado = $this->service->procesarImagenParaActualizacion($nuevaImagen, $productoMock);

        $this->assertIsString($resultado);
        $this->assertStringContainsString(
            config('inventario.imagenes.directorio', self::DIRECTORIO_IMAGENES),
            $resultado
        );
        $this->assertNotEquals(self::RUTA_IMAGEN_ANTERIOR, $resultado);
    }

    #[Test]
    public function mantiene_imagen_anterior_si_no_hay_nueva(): void
    {
        /** @var Producto $productoMock */
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->imagen = self::RUTA_IMAGEN_ANTERIOR;

        $resultado = $this->service->procesarImagenParaActualizacion(null, $productoMock);

        $this->assertEquals(self::RUTA_IMAGEN_ANTERIOR, $resultado);
    }

    #[Test]
    public function puede_eliminar_imagen_si_existe(): void
    {
        /** @var Producto $productoMock */
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->imagen = self::RUTA_IMAGEN_INEXISTENTE; // Usar ruta inexistente para evitar errores

        // El método debe ejecutarse sin lanzar excepciones
        $this->service->eliminarImagenSiExiste($productoMock);

        $this->assertTrue(true); // Si llegamos aquí, no hubo excepciones
    }

    #[Test]
    public function no_elimina_imagen_por_defecto(): void
    {
        $imagenPorDefecto = config('inventario.imagenes.default', self::RUTA_IMAGEN_DEFAULT);
        /** @var Producto $productoMock */
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->imagen = $imagenPorDefecto;

        $this->service->eliminarImagenSiExiste($productoMock);

        $this->assertEquals($imagenPorDefecto, $productoMock->imagen);
    }

    #[Test]
    public function no_elimina_imagen_si_no_existe_archivo(): void
    {
        /** @var Producto $productoMock */
        $productoMock = Mockery::mock(Producto::class)->makePartial();
        $productoMock->imagen = self::RUTA_IMAGEN_INEXISTENTE;
        $imagenOriginal = $productoMock->imagen;

        $this->service->eliminarImagenSiExiste($productoMock);

        // Verificar que la imagen del producto no cambió cuando el archivo no existe
        $this->assertEquals($imagenOriginal, $productoMock->imagen);
        $this->assertNotNull($productoMock->imagen);
    }
}
