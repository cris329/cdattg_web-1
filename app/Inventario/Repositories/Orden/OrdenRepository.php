<?php

declare(strict_types=1);

namespace App\Inventario\Repositories\Orden;

use App\Models\Inventario\Orden;
use App\Models\Inventario\DetalleOrden;
use App\Inventario\Interfaces\Repositories\Orden\OrdenRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrdenRepository implements OrdenRepositoryInterface
{
    /**
     * Obtiene órdenes con filtros
     *
     * @param array $filtros
     * @return LengthAwarePaginator
     */
    public function obtenerConFiltros(array $filtros = []): LengthAwarePaginator
    {
        $query = Orden::with([
            'tipoOrden.parametro',
            'userCreate',
            'detalles.producto',
            'detalles.estadoOrden.parametro'
        ])->latest();

        if (!empty($filtros['search'])) {
            $search = $filtros['search'];
            $query->where(function ($q) use ($search) {
                $q->where('descripcion_orden', 'LIKE', "%{$search}%")
                    ->orWhereHas('userCreate', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('tipoOrden.parametro', function ($tipoQuery) use ($search) {
                        $tipoQuery->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('detalles.producto', function ($productoQuery) use ($search) {
                        $productoQuery->where('producto', 'LIKE', "%{$search}%")
                            ->orWhere('codigo_barras', 'LIKE', "%{$search}%");
                    });

                if (is_numeric($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        if (!empty($filtros['tipo_orden_id'])) {
            $query->where('tipo_orden_id', $filtros['tipo_orden_id']);
        }

        if (!empty($filtros['estado_id'])) {
            $query->whereHas('detalles', function ($q) use ($filtros) {
                $q->where('estado_orden_id', $filtros['estado_id']);
            });
        }

        $perPage = $filtros['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * Obtiene órdenes pendientes (EN ESPERA)
     *
     * @param int $estadoEnEsperaId
     * @return LengthAwarePaginator
     */
    public function obtenerPendientes(int $estadoEnEsperaId): LengthAwarePaginator
    {
        return Orden::with([
            'tipoOrden.parametro',
            'userCreate',
            'detalles.producto',
            'detalles.estadoOrden.parametro'
        ])
        ->whereHas('detalles', function ($q) use ($estadoEnEsperaId) {
            $q->where('estado_orden_id', $estadoEnEsperaId);
        })
        ->latest()
        ->paginate(15);
    }

    /**
     * Obtiene órdenes completadas (APROBADA)
     *
     * @param int $estadoAprobadaId
     * @return LengthAwarePaginator
     */
    public function obtenerCompletadas(int $estadoAprobadaId): LengthAwarePaginator
    {
        return Orden::with([
            'tipoOrden.parametro',
            'userCreate',
            'detalles.producto',
            'detalles.estadoOrden.parametro'
        ])
        ->whereHas('detalles', function ($q) use ($estadoAprobadaId) {
            $q->where('estado_orden_id', $estadoAprobadaId);
        })
        ->latest()
        ->paginate(15);
    }

    /**
     * Obtiene órdenes rechazadas (RECHAZADA)
     *
     * @param int $estadoRechazadaId
     * @return LengthAwarePaginator
     */
    public function obtenerRechazadas(int $estadoRechazadaId): LengthAwarePaginator
    {
        return Orden::with([
            'tipoOrden.parametro',
            'userCreate',
            'detalles.producto',
            'detalles.estadoOrden.parametro'
        ])
        ->whereHas('detalles', function ($q) use ($estadoRechazadaId) {
            $q->where('estado_orden_id', $estadoRechazadaId);
        })
        ->latest()
        ->paginate(15);
    }

    /**
     * Obtiene orden con relaciones (usado en show)
     *
     * @param int $id
     * @return Orden|null
     */
    public function encontrarConRelaciones(int $id): ?Orden
    {
        return Orden::with([
            'tipoOrden.parametro',
            'userCreate',
            'detalles.producto',
            'detalles.estadoOrden.parametro',
            'detalles.aprobacion.aprobador'
        ])->find($id);
    }

    /**
     * Obtiene orden con detalles y devoluciones (usado en update y destroy)
     *
     * @param int $id
     * @return Orden|null
     */
    public function encontrarConDetallesYDevoluciones(int $id): ?Orden
    {
        return Orden::with(['detalles.producto', 'detalles.devoluciones'])->find($id);
    }

    /**
     * Obtiene detalles de orden pendientes de aprobación
     *
     * @param int $estadoEnEsperaId
     * @return Collection
     */
    public function obtenerDetallesPendientes(int $estadoEnEsperaId): Collection
    {
        return DetalleOrden::with([
            'orden.tipoOrden.parametro',
            'orden.userCreate',
            'producto',
            'estadoOrden.parametro',
            'aprobacion'
        ])
        ->where('estado_orden_id', $estadoEnEsperaId)
        ->whereDoesntHave('aprobacion')
        ->latest()
        ->get();
    }

    /**
     * Crea una nueva orden
     *
     * @param array $datos
     * @return Orden
     */
    public function crear(array $datos): Orden
    {
        return Orden::create($datos);
    }

    /**
     * Actualiza una orden
     *
     * @param Orden $orden
     * @param array $datos
     * @return bool
     */
    public function actualizar(Orden $orden, array $datos): bool
    {
        return $orden->update($datos);
    }

    /**
     * Elimina una orden
     *
     * @param Orden $orden
     * @return bool
     */
    public function eliminar(Orden $orden): bool
    {
        return $orden->delete();
    }
}

