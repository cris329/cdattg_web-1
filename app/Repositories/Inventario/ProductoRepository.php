<?php

declare(strict_types=1);

namespace App\Repositories\Inventario;

use App\Models\Inventario\Producto;
use App\Core\Traits\HasCache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductoRepository
{
    use HasCache;

    public function __construct()
    {
        $this->cacheType = 'productos';
        $this->cacheTags = ['productos', 'inventario'];
    }

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
                $q->where('producto', 'LIKE', "%{$search}%")
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

        if (isset($filtros['stock_minimo'])) {
            $query->where('cantidad', '>=', $filtros['stock_minimo']);
        }

        if (isset($filtros['solo_con_stock'])) {
            $query->where('cantidad', '>', 0);
        }

        $perPage = $filtros['per_page'] ?? 10;

        return $query->orderBy('producto', 'asc')->paginate($perPage);
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
        $query = Producto::with([
            'tipoProducto.parametro',
            'unidadMedida.parametro',
            'estado.parametro',
            'contratoConvenio',
            'ambiente'
        ])->where('cantidad', '>', 0);

        if (!empty($filtros['search'])) {
            $query->where('producto', 'LIKE', "%{$filtros['search']}%");
        }

        if (!empty($filtros['tipo_producto_id'])) {
            $query->where('tipo_producto_id', $filtros['tipo_producto_id']);
        }

        if (!empty($filtros['estado_agotado_id'])) {
            $query->where('estado_producto_id', '!=', $filtros['estado_agotado_id']);
        }

        $sortBy = $filtros['sort_by'] ?? 'name';
        switch ($sortBy) {
            case 'stock-asc':
                $query->orderBy('cantidad', 'asc');
                break;
            case 'stock-desc':
                $query->orderBy('cantidad', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('producto', 'asc');
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
        ->orderBy('producto', 'asc');

        if (!empty($filtros['estado_agotado_id'])) {
            $query->where('estado_producto_id', '!=', $filtros['estado_agotado_id']);
        }

        if (!empty($filtros['search'])) {
            $query->where('producto', 'LIKE', "%{$filtros['search']}%");
        }

        if (!empty($filtros['tipo_producto_id'])) {
            $query->where('tipo_producto_id', $filtros['tipo_producto_id']);
        }

        return $query->get();
    }


    /**
     * Invalida caché
     *
     * @return void
     */
    public function invalidarCache(): void
    {
        $this->flushCache();
    }
}

