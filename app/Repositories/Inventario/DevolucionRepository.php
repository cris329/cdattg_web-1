<?php

declare(strict_types=1);

namespace App\Repositories\Inventario;

use App\Models\Inventario\DetalleOrden;
use App\Models\Inventario\Devolucion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DevolucionRepository
{
    /**
     * Obtiene préstamos pendientes de devolución
     *
     * @param int $estadoAprobadaId
     * @return LengthAwarePaginator
     */
    public function obtenerPrestamosPendientes(int $estadoAprobadaId): LengthAwarePaginator
    {
        $prestamos = DetalleOrden::with(['orden.tipoOrden', 'producto', 'devoluciones'])
            ->whereHas('orden', function ($query) {
                $query->whereNotNull('fecha_devolucion');
            })
            ->where('estado_orden_id', $estadoAprobadaId)
            ->get()
            ->filter(function ($detalle) {
                return !$detalle->estaCompletamenteDevuelto();
            });

        $page = request()->get('page', 1);
        $perPage = 10;
        $items = $prestamos->forPage($page, $perPage)->values();
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $prestamos->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
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
        $prestamos = DetalleOrden::with(['orden.tipoOrden', 'producto', 'devoluciones'])
            ->whereHas('orden', function ($query) use ($userId) {
                $query->where('user_create_id', $userId)
                    ->whereNotNull('fecha_devolucion');
            })
            ->where('estado_orden_id', $estadoAprobadaId)
            ->get()
            ->filter(function ($detalle) {
                return !$detalle->estaCompletamenteDevuelto();
            });

        $page = request()->get('page', 1);
        $perPage = 10;
        $items = $prestamos->forPage($page, $perPage)->values();
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $prestamos->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
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
}

