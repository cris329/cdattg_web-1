<?php

declare(strict_types=1);

namespace App\Inventario\Repositories\Devolucion;

use App\Models\Inventario\DetalleOrden;
use App\Models\Inventario\Devolucion;
use App\Inventario\Interfaces\Repositories\Devolucion\DevolucionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DevolucionRepository implements DevolucionRepositoryInterface
{
    /**
     * Obtiene préstamos pendientes de devolución
     *
     * @param int $estadoAprobadaId
     * @return LengthAwarePaginator
     */
    public function obtenerPrestamosPendientes(int $estadoAprobadaId): LengthAwarePaginator
    {
        $prestamos = DetalleOrden::with(['orden.tipoOrden.parametro', 'producto', 'devoluciones'])
            ->whereHas('orden', function ($query) {
                $query->whereNotNull('fecha_devolucion');
            })
            ->where('estado_orden_id', $estadoAprobadaId)
            ->get()
            ->filter(function ($detalle) {
                return !$detalle->estaCompletamenteDevuelto();
            });

        return $this->paginacionManual($prestamos, 10);
    }

    /**
     * Obtiene historial de devoluciones
     *
     * @return LengthAwarePaginator
     */
    public function obtenerHistorial(): LengthAwarePaginator
    {
        return Devolucion::with(['detalleOrden.producto', 'detalleOrden.orden', 'userCreate'])
            ->orderBy('fecha_devolucion', 'desc')
            ->paginate(20);
    }

    /**
     * Obtiene devolución con relaciones
     *
     * @param int $id
     * @return Devolucion|null
     */
    public function encontrarConRelaciones(int $id): ?Devolucion
    {
        return Devolucion::with([
            'detalleOrden.producto',
            'detalleOrden.orden',
            'userCreate',
            'userUpdate'
        ])->find($id);
    }

    /**
     * Obtiene préstamos activos del usuario
     *
     * @param int $userId
     * @param int $estadoAprobadaId
     * @return LengthAwarePaginator
     */
    public function obtenerPrestamosActivosUsuario(int $userId, int $estadoAprobadaId): LengthAwarePaginator
    {
        $prestamos = DetalleOrden::with(['orden.tipoOrden.parametro', 'producto', 'devoluciones'])
            ->whereHas('orden', function ($query) use ($userId) {
                $query->where('user_create_id', $userId)
                    ->whereNotNull('fecha_devolucion');
            })
            ->where('estado_orden_id', $estadoAprobadaId)
            ->get()
            ->filter(function ($detalle) {
                return !$detalle->estaCompletamenteDevuelto();
            });

        return $this->paginacionManual($prestamos, 10);
    }

    /**
     * Obtiene historial de préstamos del usuario
     *
     * @param int $userId
     * @return LengthAwarePaginator
     */
    public function obtenerHistorialPrestamosUsuario(int $userId): LengthAwarePaginator
    {
        return DetalleOrden::with(['orden.tipoOrden', 'producto', 'devoluciones'])
            ->whereHas('orden', function ($query) use ($userId) {
                $query->where('user_create_id', $userId)
                    ->whereNotNull('fecha_devolucion');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    /**
     * Crea paginación manual para colecciones filtradas
     *
     * @param \Illuminate\Support\Collection $items
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    private function paginacionManual(\Illuminate\Support\Collection $items, int $perPage): LengthAwarePaginator
    {
        $page = request()->get('page', 1);
        $paginatedItems = $items->forPage((int) $page, $perPage)->values();
        
        try {
            $path = request()->url();
        } catch (\Exception $e) {
            $path = route('inventario.devoluciones.index');
        }
        
        if (!$path) {
            $path = route('inventario.devoluciones.index');
        }
        
        $query = request()->query() ?? [];

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $items->count(),
            $perPage,
            (int) $page,
            ['path' => $path, 'query' => $query]
        );
    }
}

