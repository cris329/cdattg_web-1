<?php

declare(strict_types=1);

namespace App\Inventario\Services\FormOptions;

use App\Inventario\Interfaces\Services\FormOptionsServiceInterface;
use App\Inventario\Interfaces\Repositories\ParametroTema\ParametroTemaRepositoryInterface;

class FormOptionsService implements FormOptionsServiceInterface
{
    public function __construct(
        protected ParametroTemaRepositoryInterface $parametroTemaRepository
    ) {}

    /**
     * Obtiene todas las opciones para formularios de productos
     *
     * @param string|null $temaEstados
     * @return array
     */
    public function obtenerOpcionesProducto(?string $temaEstados = null): array
    {
        $temaEstados = $temaEstados ?? config('inventario.temas.estados_producto', 'ESTADOS DE PRODUCTO');

        return [
            'tiposProductos' => $this->obtenerTiposProducto(),
            'unidadesMedida' => $this->obtenerUnidadesMedida(),
            'estados' => $this->obtenerEstados($temaEstados),
            'categorias' => $this->obtenerCategorias(),
            'marcas' => $this->obtenerMarcas(),
        ];
    }

    /**
     * Obtiene opciones para formularios de órdenes
     *
     * @return array
     */
    public function obtenerOpcionesOrden(): array
    {
        return [
            'tiposOrden' => $this->obtenerTiposOrden(),
            'estadosOrden' => $this->obtenerEstadosOrden(),
        ];
    }

    /**
     * Obtiene tipos de producto
     * @return \Illuminate\Support\Collection
     */
    public function obtenerTiposProducto()
    {
        return $this->parametroTemaRepository->obtenerPorTema(
            config('inventario.temas.tipos_producto', 'TIPOS DE PRODUCTO')
        );
    }

    /**
     * Obtiene unidades de medida
     *
     * @return \Illuminate\Support\Collection
     */
    public function obtenerUnidadesMedida()
    {
        return $this->parametroTemaRepository->obtenerPorTema(
            config('inventario.temas.unidades_medida', 'UNIDADES DE MEDIDA')
        );
    }

    /**
     * Obtiene estados
     *
     * @param string $tema
     * @return \Illuminate\Support\Collection
     */
    public function obtenerEstados(string $tema)
    {
        return $this->parametroTemaRepository->obtenerPorTema($tema);
    }

    /**
     * Obtiene categorías
     * @return \Illuminate\Support\Collection
     */
    public function obtenerCategorias()
    {
        return $this->parametroTemaRepository->obtenerPorTema(
            config('inventario.temas.categorias', 'CATEGORIAS')
        );
    }

    /**
     * Obtiene marcas
     * @return \Illuminate\Support\Collection
     */
    public function obtenerMarcas()
    {
        return $this->parametroTemaRepository->obtenerPorTema(
            config('inventario.temas.marcas', 'MARCAS')
        );
    }

    /**
     * Obtiene tipos de orden
     * @return \Illuminate\Support\Collection
     */
    public function obtenerTiposOrden()
    {
        return $this->parametroTemaRepository->obtenerPorTema(
            config('inventario.temas.tipos_orden', 'TIPOS DE ORDEN')
        );
    }

    /**
     * Obtiene estados de orden
     * @return \Illuminate\Support\Collection
     */
    public function obtenerEstadosOrden()
    {
        return $this->parametroTemaRepository->obtenerPorTema(
            config('inventario.temas.estados_orden', 'ESTADOS DE ORDEN')
        );
    }

    /**
     * Obtiene el estado "AGOTADO" de productos
     * @param string|null $temaEstados
     * @return \App\Models\ParametroTema|null
     */
    public function obtenerEstadoAgotado(?string $temaEstados = null)
    {
        $temaEstados = $temaEstados ?? config('inventario.temas.estados_producto', 'ESTADOS DE PRODUCTO');

        return $this->obtenerEstadoOrdenPorNombre('AGOTADO', $temaEstados);
    }

    /**
     * Obtiene un estado de orden por nombre
     * @param string $nombreEstado
     * @param string|null $temaEstados
     * @return \App\Models\ParametroTema|null
     */
    public function obtenerEstadoOrdenPorNombre(string $nombreEstado, ?string $temaEstados = null)
    {
        $temaEstados = $temaEstados ?? config('inventario.temas.estados_orden', 'ESTADOS DE ORDEN');

        return $this->parametroTemaRepository->obtenerEstadoPorNombre($nombreEstado, $temaEstados);
    }
}

