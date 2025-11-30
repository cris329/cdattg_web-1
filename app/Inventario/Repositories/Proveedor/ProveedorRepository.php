<?php

declare(strict_types=1);

namespace App\Inventario\Repositories\Proveedor;

use App\Models\Inventario\Proveedor;
use App\Inventario\Interfaces\Repositories\Proveedor\ProveedorRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProveedorRepository implements ProveedorRepositoryInterface
{

    /**
     * Obtiene todos los proveedores
     *
     * @return Collection
     */
    public function obtenerTodos(): Collection
    {
        return Proveedor::orderBy('proveedor')->get();
    }

    /**
     * Obtiene proveedores con filtros y relaciones
     *
     * @param array $filtros
     * @return LengthAwarePaginator
     */
    public function obtenerConFiltros(array $filtros = []): LengthAwarePaginator
    {
        $query = Proveedor::with([
            'userCreate.persona',
            'userUpdate.persona',
            'estado.parametro',
            'departamento',
            'municipio'
        ])
        ->withCount('contratosConvenios')
        ->latest();

        if (!empty($filtros['search'])) {
            $search = $filtros['search'];
            $query->where(function ($q) use ($search) {
                $q->where('proveedor', 'LIKE', "%{$search}%")
                    ->orWhere('nit', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('telefono', 'LIKE', "%{$search}%")
                    ->orWhere('contacto', 'LIKE', "%{$search}%")
                    ->orWhereHas('departamento', function ($departamentoQuery) use ($search) {
                        $departamentoQuery->where('departamento', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('municipio', function ($municipioQuery) use ($search) {
                        $municipioQuery->where('municipio', 'LIKE', "%{$search}%");
                    });
            });
        }

        $perPage = $filtros['per_page'] ?? 10;
        return $query->paginate($perPage);
    }

    /**
     * Encuentra un proveedor por ID con relaciones
     *
     * @param int $id
     * @return Proveedor|null
     */
    public function encontrarConRelaciones(int $id): ?Proveedor
    {
        return Proveedor::with([
            'contratosConvenios',
            'userCreate.persona',
            'userUpdate.persona',
            'estado.parametro',
            'departamento',
            'municipio'
        ])->find($id);
    }

    /**
     * Crea un nuevo proveedor
     *
     * @param array $datos
     * @return Proveedor
     */
    public function crear(array $datos): Proveedor
    {
        return Proveedor::create($datos);
    }

    /**
     * Actualiza un proveedor
     *
     * @param int $id
     * @param array $datos
     * @return bool
     */
    public function actualizar(int $id, array $datos): bool
    {
        return Proveedor::where('id', $id)->update($datos) > 0;
    }

    /**
     * Elimina un proveedor
     *
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool
    {
        return Proveedor::destroy($id) > 0;
    }

    /**
     * Verifica si un proveedor tiene contratos asociados
     *
     * @param int $id
     * @return bool
     */
    public function tieneContratos(int $id): bool
    {
        return Proveedor::where('id', $id)
            ->whereHas('contratosConvenios')
            ->exists();
    }

    /**
     * Verifica si un proveedor tiene productos asociados
     *
     * @param int $id
     * @return bool
     */
    public function tieneProductos(int $id): bool
    {
        return Proveedor::where('id', $id)
            ->whereHas('productos')
            ->exists();
    }
}

