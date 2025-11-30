<?php

declare(strict_types=1);

namespace App\Inventario\Services\FormOptions;

use App\Inventario\Interfaces\Services\FormOptionsServiceInterface;
use App\Models\Tema;

class FormOptionsService implements FormOptionsServiceInterface
{
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
        return $this->obtenerParametrosPorTema(
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
        return $this->obtenerParametrosPorTema(
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
        return $this->obtenerParametrosPorTema($tema);
    }

    /**
     * Obtiene categorías
     * @return \Illuminate\Support\Collection
     */
    public function obtenerCategorias()
    {
        return $this->obtenerParametrosPorTema(
            config('inventario.temas.categorias', 'CATEGORIAS')
        );
    }

    /**
     * Obtiene marcas
     * @return \Illuminate\Support\Collection
     */
    public function obtenerMarcas()
    {
        return $this->obtenerParametrosPorTema(
            config('inventario.temas.marcas', 'MARCAS')
        );
    }

    /**
     * Obtiene tipos de orden
     * @return \Illuminate\Support\Collection
     */
    public function obtenerTiposOrden()
    {
        return $this->obtenerParametrosPorTema(
            config('inventario.temas.tipos_orden', 'TIPOS DE ORDEN')
        );
    }

    /**
     * Obtiene estados de orden
     * @return \Illuminate\Support\Collection
     */
    public function obtenerEstadosOrden()
    {
        return $this->obtenerParametrosPorTema(
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

        $tema = Tema::where('name', $temaEstados)->first();

        if (!$tema) {
            return null;
        }

        // Buscar el parámetro primero
        $parametro = \App\Models\Parametro::where('name', $nombreEstado)->first();
        
        if (!$parametro) {
            return null;
        }

        // Buscar el ParametroTema que relaciona el parámetro con el tema
        return \App\Models\ParametroTema::where('tema_id', $tema->id)
            ->where('parametro_id', $parametro->id)
            ->where('status', 1)
            ->first();
    }

    /**
     * Obtiene parámetros por tema
     * @param string $nombreTema
     * @return \Illuminate\Support\Collection
     */
    private function obtenerParametrosPorTema(string $nombreTema)
    {
        $tema = Tema::where('name', $nombreTema)->first();

        if (!$tema) {
            return collect([]);
        }

        return $tema->parametros()
            ->wherePivot('status', 1)
            ->get()
            ->map(function ($parametro) {
                $objeto = new \stdClass();
                $objeto->id = $parametro->id;
                $objeto->name = $parametro->name;
                $objeto->status = $parametro->status;
                // Crear objeto parametro con los datos necesarios para evitar problemas de serialización
                $objetoParametro = new \stdClass();
                $objetoParametro->id = $parametro->id;
                $objetoParametro->name = $parametro->name;
                $objetoParametro->status = $parametro->status;
                $objeto->parametro = $objetoParametro;
                return $objeto;
            });
    }

}

