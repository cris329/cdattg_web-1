<?php

declare(strict_types=1);

namespace App\Inventario\Repositories\Categoria;

use App\Models\Parametro;
use App\Models\ParametroTema;
use App\Models\Tema;
use App\Models\Inventario\Producto;
use App\Models\Inventario\Categoria;
use App\Inventario\Interfaces\Repositories\Categoria\CategoriaRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CategoriaRepository implements CategoriaRepositoryInterface
{
    private const TEMA_CATEGORIAS = 'CATEGORIAS';

    /**
     * Obtiene el tema de categorías
     *
     * @return Tema|null
     */
    public function obtenerTemaCategorias(): ?Tema
    {
        return Tema::where('name', self::TEMA_CATEGORIAS)->first();
    }

    /**
     * Obtiene categorías con filtros
     *
     * @param array $filtros
     * @return LengthAwarePaginator
     */
    public function obtenerConFiltros(array $filtros = []): LengthAwarePaginator
    {
        $temaCategorias = $this->obtenerTemaCategorias();

        if (!$temaCategorias) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
        }

        $query = $temaCategorias->parametros()
            ->with(['userCreate.persona', 'userUpdate.persona'])
            ->wherePivot('status', 1);

        if (!empty($filtros['search'])) {
            $search = $filtros['search'];
            $query->where(function ($q) use ($search) {
                $q->where('parametros.name', 'LIKE', "%{$search}%");
            });
        }

        $perPage = $filtros['per_page'] ?? 10;
        $categorias = $query->paginate($perPage);

        // Cargar conteo de productos para cada categoría
        foreach ($categorias as $categoria) {
            $categoria->productos_count = Producto::where('categoria_id', $categoria->id)->count();
        }

        return $categorias;
    }

    /**
     * Encuentra una categoría por ID
     *
     * @param int $id
     * @return Categoria|null
     */
    public function encontrar(int $id): ?Categoria
    {
        return Categoria::find($id);
    }

    /**
     * Encuentra múltiples categorías por IDs
     *
     * @param array $ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function encontrarMultiples(array $ids): Collection
    {
        return Categoria::whereIn('id', $ids)->get()->keyBy('id');
    }

    /**
     * Encuentra una categoría por ID con relaciones
     *
     * @param int $id
     * @return Parametro|null
     */
    public function encontrarConRelaciones(int $id): ?Parametro
    {
        return Parametro::with(['userCreate.persona', 'userUpdate.persona'])->find($id);
    }

    /**
     * Actualiza una categoría
     *
     * @param int $id
     * @param array $datos
     * @return bool
     */
    public function actualizar(int $id, array $datos): bool
    {
        return Parametro::where('id', $id)->update($datos) > 0;
    }

    /**
     * Elimina una categoría
     *
     * @param Parametro $categoria
     * @param int $temaId
     * @return bool
     */
    public function eliminar(Parametro $categoria, int $temaId): bool
    {
        // Desvincular del tema "CATEGORIAS"
        ParametroTema::where('parametro_id', $categoria->id)
            ->where('tema_id', $temaId)
            ->delete();

        return $categoria->delete();
    }

    /**
     * Verifica si una categoría tiene productos asociados
     *
     * @param int $id
     * @return bool
     */
    public function tieneProductos(int $id): bool
    {
        return Producto::where('categoria_id', $id)->exists();
    }
}

