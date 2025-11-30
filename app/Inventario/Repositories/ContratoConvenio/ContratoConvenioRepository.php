<?php

declare(strict_types=1);

namespace App\Inventario\Repositories\ContratoConvenio;

use App\Models\Inventario\ContratoConvenio;
use App\Inventario\Interfaces\Repositories\ContratoConvenio\ContratoConvenioRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ContratoConvenioRepository implements ContratoConvenioRepositoryInterface
{

    /**
     * Obtiene todos los contratos y convenios
     *
     * @return Collection
     */
    public function obtenerTodos(): Collection
    {
        return ContratoConvenio::orderBy('name')->get();
    }

    /**
     * Obtiene contratos con filtros y relaciones
     *
     * @param array $filtros
     * @return LengthAwarePaginator
     */
    public function obtenerConFiltros(array $filtros = []): LengthAwarePaginator
    {
        $query = ContratoConvenio::with([
            'proveedor',
            'estado.parametro',
            'userCreate.persona',
            'userUpdate.persona'
        ])->latest();

        if (!empty($filtros['search'])) {
            $search = $filtros['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('codigo', 'LIKE', "%{$search}%")
                    ->orWhereHas('proveedor', function ($proveedorQuery) use ($search) {
                        $proveedorQuery->where('proveedor', 'LIKE', "%{$search}%");
                    });
            });
        }

        $perPage = $filtros['per_page'] ?? 10;
        return $query->paginate($perPage);
    }

    /**
     * Encuentra un contrato por ID con relaciones
     *
     * @param int $id
     * @return ContratoConvenio|null
     */
    public function encontrarConRelaciones(int $id): ?ContratoConvenio
    {
        return ContratoConvenio::with([
            'proveedor',
            'productos',
            'estado.parametro',
            'userCreate.persona',
            'userUpdate.persona'
        ])->find($id);
    }

    /**
     * Crea un nuevo contrato
     *
     * @param array $datos
     * @return ContratoConvenio
     */
    public function crear(array $datos): ContratoConvenio
    {
        return ContratoConvenio::create($datos);
    }

    /**
     * Actualiza un contrato
     *
     * @param int $id
     * @param array $datos
     * @return bool
     */
    public function actualizar(int $id, array $datos): bool
    {
        return ContratoConvenio::where('id', $id)->update($datos) > 0;
    }

    /**
     * Elimina un contrato
     *
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool
    {
        return ContratoConvenio::destroy($id) > 0;
    }

    /**
     * Verifica si un contrato tiene productos asociados
     *
     * @param int $id
     * @return bool
     */
    public function tieneProductos(int $id): bool
    {
        return ContratoConvenio::where('id', $id)
            ->whereHas('productos')
            ->exists();
    }
}

