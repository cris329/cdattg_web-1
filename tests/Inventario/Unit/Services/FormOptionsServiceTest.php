<?php

declare(strict_types=1);

namespace Tests\Inventario\Unit\Services;

use Tests\TestCase;
use App\Inventario\Services\FormOptions\FormOptionsService;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class FormOptionsServiceTest extends TestCase
{
    protected FormOptionsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new FormOptionsService();
    }

    #[Test]
    public function servicio_puede_instanciarse(): void
    {
        $this->assertInstanceOf(FormOptionsService::class, $this->service);
    }

    #[Test]
    public function puede_obtener_opciones_producto_retorna_estructura_correcta(): void
    {
        $opciones = $this->service->obtenerOpcionesProducto();

        $this->assertIsArray($opciones);
        $this->assertArrayHasKey('tiposProductos', $opciones);
        $this->assertArrayHasKey('unidadesMedida', $opciones);
        $this->assertArrayHasKey('estados', $opciones);
        $this->assertArrayHasKey('categorias', $opciones);
        $this->assertArrayHasKey('marcas', $opciones);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $opciones['tiposProductos']);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $opciones['unidadesMedida']);
    }

    #[Test]
    public function puede_obtener_opciones_orden_retorna_estructura_correcta(): void
    {
        $opciones = $this->service->obtenerOpcionesOrden();

        $this->assertIsArray($opciones);
        $this->assertArrayHasKey('tiposOrden', $opciones);
        $this->assertArrayHasKey('estadosOrden', $opciones);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $opciones['tiposOrden']);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $opciones['estadosOrden']);
    }

    #[Test]
    public function puede_obtener_tipos_producto_retorna_collection(): void
    {
        $tipos = $this->service->obtenerTiposProducto();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $tipos);
    }

    #[Test]
    public function puede_obtener_unidades_medida_retorna_collection(): void
    {
        $unidades = $this->service->obtenerUnidadesMedida();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $unidades);
    }

    #[Test]
    public function puede_obtener_categorias_retorna_collection(): void
    {
        $categorias = $this->service->obtenerCategorias();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $categorias);
    }

    #[Test]
    public function puede_obtener_marcas_retorna_collection(): void
    {
        $marcas = $this->service->obtenerMarcas();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $marcas);
    }

    #[Test]
    public function puede_obtener_estado_orden_por_nombre_retorna_null_o_parametro(): void
    {
        $estado = $this->service->obtenerEstadoOrdenPorNombre('EN ESPERA');

        if ($estado !== null) {
            $this->assertInstanceOf(\App\Models\ParametroTema::class, $estado);
        } else {
            $this->assertNull($estado);
        }
    }
}
