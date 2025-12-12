<?php

declare(strict_types=1);

namespace App\Inventario\Repositories\Producto;

use App\Models\Inventario\Producto;
use App\Models\ParametroTema;
use App\Inventario\Interfaces\Repositories\Producto\ProductoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductoRepository implements ProductoRepositoryInterface
{

    /**
     * Obtiene productos con filtros y relaciones
     *
     * @param array $filtros
     * @return LengthAwarePaginator
     */
    public function obtenerConFiltros(array $filtros = []): LengthAwarePaginator
    {
        $query = Producto::with([
            'tipoProducto.parametro',
            'unidadMedida.parametro',
            'estado.parametro',
            'contratoConvenio',
            'ambiente',
            'proveedor'
        ]);

        if (!empty($filtros['search'])) {
            $search = $filtros['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('codigo_barras', 'LIKE', "%{$search}%")
                    ->orWhere('descripcion', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($filtros['tipo_producto_id'])) {
            $query->where('tipo_producto_id', $filtros['tipo_producto_id']);
        }

        if (!empty($filtros['categoria_id'])) {
            $query->where('categoria_id', $filtros['categoria_id']);
        }

        if (!empty($filtros['marca_id'])) {
            $query->where('marca_id', $filtros['marca_id']);
        }

        if (!empty($filtros['estado_producto_id'])) {
            $estadoFiltro = $filtros['estado_producto_id'];

            if ($estadoFiltro === 'bajo_stock') {
                $query->where('cantidad', '>', 0)
                    ->where('cantidad', '<=', 5);
            } elseif ($estadoFiltro === 'solo_agotado') {
                $query->where('cantidad', '<=', 0);
            } else {
                $query->where('estado_producto_id', $estadoFiltro);
            }
        }

        if (isset($filtros['stock_minimo'])) {
            $query->where('cantidad', '>=', $filtros['stock_minimo']);
        }

        if (isset($filtros['solo_con_stock'])) {
            $query->where('cantidad', '>', 0);
        }

        $perPage = $filtros['per_page'] ?? 10;

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    /**
     * Obtiene producto con todas sus relaciones
     *
     * @param int $id
     * @return Producto|null
     */
    public function encontrarConRelaciones(int $id): ?Producto
    {
        return Producto::with([
            'tipoProducto.parametro',
            'unidadMedida.parametro',
            'estado.parametro',
            'categoria',
            'marca',
            'contratoConvenio',
            'ambiente',
            'proveedor'
        ])->find($id);
    }

    /**
     * Busca producto por código de barras
     *
     * @param string $codigo
     * @return Producto|null
     */
    public function buscarPorCodigoBarras(string $codigo): ?Producto
    {
        return Producto::where('codigo_barras', $codigo)->first();
    }

    /**
     * Obtiene productos para catálogo (con filtros y ordenamiento)
     *
     * @param array $filtros
     * @return LengthAwarePaginator
     */
    public function obtenerParaCatalogo(array $filtros = []): LengthAwarePaginator
    {
        $query = Producto::select([
            'id',
            'name',
            'codigo_barras',
            'cantidad',
            'imagen',
            'tipo_producto_id',
            'categoria_id',
            'estado_producto_id',
            'created_at'
        ])->where('cantidad', '>', 0);

        if (!empty($filtros['search'])) {
            $query->where('name', 'LIKE', "%{$filtros['search']}%");
        }

        if (!empty($filtros['tipo_producto_id'])) {
            $query->where('tipo_producto_id', $filtros['tipo_producto_id']);
        }

        if (!empty($filtros['categoria_id'])) {
            $query->where('categoria_id', $filtros['categoria_id']);
        }

        if (!empty($filtros['estado_agotado_id'])) {
            $query->where('estado_producto_id', '!=', $filtros['estado_agotado_id']);
        }

        $sortBy = $filtros['sort_by'] ?? 'random';
        switch ($sortBy) {
            case 'stock-asc':
                $query->orderBy('cantidad', 'asc');
                break;
            case 'stock-desc':
                $query->orderBy('cantidad', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'random':
                $query->inRandomOrder();
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $perPage = $filtros['per_page'] ?? 12;

        return $query->paginate($perPage);
    }

    /**
     * Busca productos para AJAX (usado en método buscar)
     *
     * @param array $filtros
     * @return Collection
     */
    public function buscarParaAjax(array $filtros = []): Collection
    {
        $query = Producto::with([
            'tipoProducto.parametro',
            'unidadMedida.parametro',
            'estado.parametro',
            'contratoConvenio',
            'ambiente'
        ])
        ->where('cantidad', '>', 0)
        ->orderBy('name', 'asc');

        if (!empty($filtros['estado_agotado_id'])) {
            $query->where('estado_producto_id', '!=', $filtros['estado_agotado_id']);
        }

        if (!empty($filtros['search'])) {
            $query->where('name', 'LIKE', "%{$filtros['search']}%");
        }

        if (!empty($filtros['tipo_producto_id'])) {
            $query->where('tipo_producto_id', $filtros['tipo_producto_id']);
        }

        return $query->get();
    }

    /**
     * Obtiene tipos de productos activos ordenados
     *
     * @return Collection
     */
    public function obtenerTiposProductos(): Collection
    {
        return ParametroTema::with(['parametro', 'tema'])
            ->whereHas('tema', function ($query) {
                $query->where('name', 'TIPOS DE PRODUCTO');
            })
            ->where('status', 1)
            ->get()
            ->sortBy(function ($tipo) {
                return mb_strtolower($tipo->parametro->name ?? '');
            })
            ->values();
    }

    /**
     * Encuentra un producto por ID
     *
     * @param int $id
     * @return Producto|null
     */
    public function encontrar(int $id): ?Producto
    {
        return Producto::find($id);
    }

    /**
     * Crea un nuevo producto
     *
     * @param array $datos
     * @return Producto
     */
    public function crear(array $datos): Producto
    {
        return Producto::create($datos);
    }

    /**
     * Actualiza un producto
     *
     * @param Producto $producto
     * @param array $datos
     * @return bool
     */
    public function actualizar(Producto $producto, array $datos): bool
    {
        return $producto->update($datos);
    }

    /**
     * Elimina un producto
     *
     * @param Producto $producto
     * @return bool
     */
    public function eliminar(Producto $producto): bool
    {
        return $producto->delete();
    }

    /**
     * Actualiza el stock de un producto
     *
     * @param Producto $producto
     * @param int $cantidad
     * @return bool
     */
    public function actualizarStock(Producto $producto, int $cantidad): bool
    {
        $producto->cantidad = $cantidad;
        return $producto->save();
    }

    public function obtenerTodosOrdenadosPorCantidadDesc(): Collection
    {
        return Producto::with([
            'categoria',
            'marca',
            'unidadMedida.parametro',
            'estado.parametro',
            'contratoConvenio',
            'ambiente',
            'proveedor'
        ])
            ->orderBy('cantidad', 'desc')
            ->get();
    }

    /**
     * Obtiene el código de barras máximo
     *
     * @return string|null
     */
    public function obtenerMaxCodigoBarras(): ?string
    {
        return Producto::whereNotNull('codigo_barras')
            ->max('codigo_barras');
    }

    /**
     * Verifica si existe un código de barras
     *
     * @param string $codigo
     * @return bool
     */
    public function existeCodigoBarras(string $codigo): bool
    {
        return Producto::where('codigo_barras', $codigo)->exists();
    }

}

