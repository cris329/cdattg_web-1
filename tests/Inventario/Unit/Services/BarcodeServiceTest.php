<?php

declare(strict_types=1);

namespace Tests\Inventario\Unit\Services;

use Tests\TestCase;
use App\Inventario\Services\Barcode\BarcodeService;
use App\Inventario\Interfaces\Repositories\Producto\ProductoRepositoryInterface;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class BarcodeServiceTest extends TestCase
{
    private const LONGITUD_CODIGO_BARRAS = 11;
    private const CODIGO_BARRAS_VALIDO = '12345678901';
    private const CODIGO_BARRAS_INVALIDO = '123';
    private const CODIGO_BARRAS_MAX_INICIAL = '00000000001';
    private const CODIGO_BARRAS_SIGUIENTE = '00000000002';
    private const CODIGO_BARRAS_SIGUIENTE_2 = '00000000003';
    private const CODIGO_BARRAS_CON_LETRAS = 'ABC12345678901DEF';

    protected BarcodeService $service;
    protected $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(ProductoRepositoryInterface::class);

        $this->service = new BarcodeService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function servicio_puede_instanciarse(): void
    {
        $this->assertInstanceOf(BarcodeService::class, $this->service);
    }

    #[Test]
    public function puede_resolver_codigo_barras_valido(): void
    {
        $resultado = $this->service->resolverCodigoBarras(self::CODIGO_BARRAS_VALIDO);

        $this->assertEquals(self::CODIGO_BARRAS_VALIDO, $resultado);
    }

    #[Test]
    public function genera_codigo_barras_si_no_es_valido(): void
    {
        $this->mockRepository->shouldReceive('obtenerMaxCodigoBarras')
            ->once()
            ->andReturn(self::CODIGO_BARRAS_MAX_INICIAL);

        $this->mockRepository->shouldReceive('existeCodigoBarras')
            ->once()
            ->with(Mockery::pattern('/^\d{' . self::LONGITUD_CODIGO_BARRAS . '}$/'))
            ->andReturn(false);

        $resultado = $this->service->resolverCodigoBarras(self::CODIGO_BARRAS_INVALIDO);

        $this->assertIsString($resultado);
        $this->assertEquals(self::LONGITUD_CODIGO_BARRAS, strlen($resultado));
    }

    #[Test]
    public function genera_siguiente_codigo_barras(): void
    {
        $this->mockRepository->shouldReceive('obtenerMaxCodigoBarras')
            ->once()
            ->andReturn(self::CODIGO_BARRAS_MAX_INICIAL);

        $this->mockRepository->shouldReceive('existeCodigoBarras')
            ->once()
            ->with(self::CODIGO_BARRAS_SIGUIENTE)
            ->andReturn(false);

        $codigo = $this->service->generarSiguienteCodigoBarras();

        $this->assertIsString($codigo);
        $this->assertEquals(self::LONGITUD_CODIGO_BARRAS, strlen($codigo));
    }

    #[Test]
    public function genera_codigo_barras_incrementando_si_existe(): void
    {
        $this->mockRepository->shouldReceive('obtenerMaxCodigoBarras')
            ->once()
            ->andReturn(self::CODIGO_BARRAS_MAX_INICIAL);

        $this->mockRepository->shouldReceive('existeCodigoBarras')
            ->once()
            ->with(self::CODIGO_BARRAS_SIGUIENTE)
            ->andReturn(true);

        $this->mockRepository->shouldReceive('existeCodigoBarras')
            ->once()
            ->with(self::CODIGO_BARRAS_SIGUIENTE_2)
            ->andReturn(false);

        $codigo = $this->service->generarSiguienteCodigoBarras();

        $this->assertEquals(self::LONGITUD_CODIGO_BARRAS, strlen($codigo));
        $this->assertEquals(self::CODIGO_BARRAS_SIGUIENTE_2, $codigo);
    }

    #[Test]
    public function puede_normalizar_codigo_barras(): void
    {
        $resultado = $this->service->normalizarCodigoBarras(self::CODIGO_BARRAS_VALIDO);

        $this->assertEquals(self::CODIGO_BARRAS_VALIDO, $resultado);
    }

    #[Test]
    public function retorna_null_si_codigo_no_es_normalizable(): void
    {
        $resultado = $this->service->normalizarCodigoBarras(self::CODIGO_BARRAS_INVALIDO);

        $this->assertNull($resultado);
    }

    #[Test]
    public function retorna_null_si_codigo_esta_vacio(): void
    {
        $resultado = $this->service->normalizarCodigoBarras(null);

        $this->assertNull($resultado);
    }

    #[Test]
    public function normaliza_codigo_eliminando_caracteres_no_numericos(): void
    {
        $resultado = $this->service->normalizarCodigoBarras(self::CODIGO_BARRAS_CON_LETRAS);

        if (strlen(preg_replace('/\D/', '', self::CODIGO_BARRAS_CON_LETRAS)) === self::LONGITUD_CODIGO_BARRAS) {
            $this->assertEquals(self::CODIGO_BARRAS_VALIDO, $resultado);
        } else {
            $this->assertNull($resultado);
        }
    }
}
