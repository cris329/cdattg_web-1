<?php

declare(strict_types=1);

namespace App\Inventario\Repositories\Dashboard;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardRepository
{
    /**
     * Obtiene el total de productos
     *
     * @return int
     */
    public function obtenerTotalProductos(): int
    {
        return (int) DB::table('productos')->count();
    }

    /**
     * Obtiene productos consumibles
     *
     * @return int
     */
    public function obtenerProductosConsumibles(): int
    {
        return (int) DB::table('productos')
            ->join('parametros_temas', 'productos.tipo_producto_id', '=', 'parametros_temas.id')
            ->join('parametros', 'parametros_temas.parametro_id', '=', 'parametros.id')
            ->where('parametros.name', 'CONSUMIBLE')
            ->count();
    }

    /**
     * Obtiene productos no consumibles
     *
     * @return int
     */
    public function obtenerProductosNoConsumibles(): int
    {
        return (int) DB::table('productos')
            ->join('parametros_temas', 'productos.tipo_producto_id', '=', 'parametros_temas.id')
            ->join('parametros', 'parametros_temas.parametro_id', '=', 'parametros.id')
            ->where('parametros.name', 'NO CONSUMIBLE')
            ->count();
    }

    /**
     * Obtiene productos por vencer (próximos 30 días)
     *
     * @return int
     */
    public function obtenerProductosPorVencer(): int
    {
        $fechaActual = Carbon::now();
        $fechaLimite = Carbon::now()->addDays(30);

        return (int) DB::table('productos')
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '>', $fechaActual)
            ->whereDate('fecha_vencimiento', '<=', $fechaLimite)
            ->count();
    }

    /**
     * Obtiene productos con stock bajo (menor a 10)
     *
     * @return int
     */
    public function obtenerProductosStockBajo(): int
    {
        return (int) DB::table('productos')
            ->where('cantidad', '<', 10)
            ->count();
    }

    /**
     * Obtiene el total de categorías activas
     *
     * @return int
     */
    public function obtenerTotalCategorias(): int
    {
        return (int) DB::table('temas')
            ->join('parametros_temas', 'temas.id', '=', 'parametros_temas.tema_id')
            ->where('temas.name', 'CATEGORIAS')
            ->where('parametros_temas.status', 1)
            ->count();
    }

    /**
     * Obtiene los productos más solicitados
     *
     * @param int $limite
     * @return array
     */
    public function obtenerProductosMasSolicitados(int $limite = 5): array
    {
        $productos = DB::table('detalle_ordenes')
            ->join('productos', 'detalle_ordenes.producto_id', '=', 'productos.id')
            ->select('productos.name as nombre', DB::raw('SUM(detalle_ordenes.cantidad) as solicitudes'))
            ->groupBy('productos.id', 'productos.name')
            ->orderBy('solicitudes', 'desc')
            ->limit($limite)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->nombre,
                    'solicitudes' => (int) $item->solicitudes,
                ];
            })
            ->toArray();

        return $productos ?: [];
    }

    /**
     * Obtiene productos agrupados por categoría
     *
     * @return array
     */
    public function obtenerProductosPorCategoria(): array
    {
        return DB::table('productos')
            ->join('parametros', 'productos.categoria_id', '=', 'parametros.id')
            ->select('parametros.name as categoria', DB::raw('count(*) as total'))
            ->groupBy('parametros.id', 'parametros.name')
            ->get()
            ->map(function ($item) {
                return [
                    'categoria' => $item->categoria,
                    'total' => (int) $item->total,
                ];
            })
            ->toArray();
    }

    /**
     * Obtiene productos recientes con estado
     *
     * @param int $limite
     * @return array
     */
    public function obtenerProductosRecientes(int $limite = 5): array
    {
        return DB::table('productos')
            ->leftJoin('parametros_temas as estado_pt', 'productos.estado_producto_id', '=', 'estado_pt.id')
            ->leftJoin('parametros as estado_p', 'estado_pt.parametro_id', '=', 'estado_p.id')
            ->select(
                'productos.name',
                'productos.cantidad',
                'estado_p.name as estado_nombre',
                'productos.created_at'
            )
            ->orderBy('productos.created_at', 'desc')
            ->limit($limite)
            ->get()
            ->map(function ($producto) {
                return [
                    'name' => $producto->name,
                    'cantidad' => (int) $producto->cantidad,
                    'estado' => $producto->estado_nombre ? [
                        'parametro' => [
                            'name' => $producto->estado_nombre,
                        ],
                    ] : null,
                    'created_at' => $producto->created_at,
                ];
            })
            ->toArray();
    }
}

