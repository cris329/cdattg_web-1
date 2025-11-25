<?php

declare(strict_types=1);

namespace App\Livewire\Inventario;

use App\Models\Inventario\Producto;
use App\Models\Inventario\Categoria;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardInventario extends Component
{
    public int $totalProductos = 0;
    public int $productosConsumibles = 0;
    public int $productosNoConsumibles = 0;
    public int $productosPorVencer = 0;
    public int $productosStockBajo = 0;
    public int $totalCategorias = 0;
    public array $productosMasSolicitados = [];
    public array $productosPorCategoria = [];
    public $productosRecientes = [];

    public function mount(): void
    {
        $this->cargarDatos();
    }

    /**
     * Carga todos los datos del dashboard
     */
    public function cargarDatos(): void
    {
        $this->totalProductos = Producto::count();

        $this->productosConsumibles = Producto::whereHas('tipoProducto', function ($query) {
            $query->whereHas('parametro', function ($subQuery) {
                $subQuery->where('name', 'CONSUMIBLE');
            });
        })->count();

        $this->productosNoConsumibles = Producto::whereHas('tipoProducto', function ($query) {
            $query->whereHas('parametro', function ($subQuery) {
                $subQuery->where('name', 'NO CONSUMIBLE');
            });
        })->count();

        $this->productosMasSolicitados = $this->obtenerProductosMasSolicitados();

        $this->productosPorVencer = Producto::whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '>', Carbon::now())
            ->whereDate('fecha_vencimiento', '<=', Carbon::now()->addDays(30))
            ->count();

        $this->productosStockBajo = Producto::where('cantidad', '<', 10)->count();

        $this->totalCategorias = $this->obtenerTotalCategorias();

        $this->productosRecientes = Producto::with(['estado', 'estado.parametro'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($producto) {
                return [
                    'producto' => $producto->producto,
                    'cantidad' => $producto->cantidad,
                    'estado' => $producto->estado ? [
                        'parametro' => $producto->estado->parametro ? [
                            'name' => $producto->estado->parametro->name,
                        ] : null,
                    ] : null,
                    'created_at' => $producto->created_at->toDateTimeString(),
                ];
            })
            ->toArray();

        $this->productosPorCategoria = $this->obtenerProductosPorCategoria();
    }

    /**
     * Obtiene los productos más solicitados
     */
    protected function obtenerProductosMasSolicitados(): array
    {
        $productos = DB::table('detalle_ordenes')
            ->join('productos', 'detalle_ordenes.producto_id', '=', 'productos.id')
            ->select('productos.producto', DB::raw('SUM(detalle_ordenes.cantidad) as solicitudes'))
            ->groupBy('productos.id', 'productos.producto')
            ->orderBy('solicitudes', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'nombre' => $item->producto,
                    'solicitudes' => (int) $item->solicitudes,
                ];
            })
            ->toArray();

        return $productos ?: [];
    }

    /**
     * Obtiene el total de categorías activas
     */
    protected function obtenerTotalCategorias(): int
    {
        $temaCategorias = \App\Models\Tema::where('name', 'CATEGORIAS')->first();

        if (!$temaCategorias) {
            return 0;
        }

        return $temaCategorias->parametros()->wherePivot('status', 1)->count();
    }

    /**
     * Obtiene productos agrupados por categoría
     */
    protected function obtenerProductosPorCategoria(): array
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
     * Refrescar manualmente los datos
     */
    public function refrescar(): void
    {
        $this->cargarDatos();
        $this->dispatch('datos-actualizados');
    }

    public function render()
    {
        return view('livewire.inventario.dashboard-inventario');
    }
}

