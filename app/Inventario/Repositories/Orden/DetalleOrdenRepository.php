<?php

declare(strict_types=1);

namespace App\Inventario\Repositories\Orden;

use App\Models\Inventario\DetalleOrden;
use App\Inventario\Interfaces\Repositories\Orden\DetalleOrdenRepositoryInterface;

class DetalleOrdenRepository implements DetalleOrdenRepositoryInterface
{
    /**
     * Crea un nuevo detalle de orden
     *
     * @param array $datos
     * @return DetalleOrden
     */
    public function crear(array $datos): DetalleOrden
    {
        return DetalleOrden::create($datos);
    }

    /**
     * Actualiza un detalle de orden
     *
     * @param DetalleOrden $detalleOrden
     * @param array $datos
     * @return bool
     */
    public function actualizar(DetalleOrden $detalleOrden, array $datos): bool
    {
        return $detalleOrden->update($datos);
    }

    /**
     * Elimina un detalle de orden
     *
     * @param DetalleOrden $detalleOrden
     * @return bool
     */
    public function eliminar(DetalleOrden $detalleOrden): bool
    {
        return $detalleOrden->delete();
    }

    /**
     * Elimina todos los detalles de una orden
     *
     * @param int $ordenId
     * @return bool
     */
    public function eliminarPorOrden(int $ordenId): bool
    {
        return DetalleOrden::where('orden_id', $ordenId)->delete() > 0;
    }

    /**
     * Encuentra un detalle de orden por ID
     *
     * @param int $id
     * @return DetalleOrden|null
     */
    public function encontrar(int $id): ?DetalleOrden
    {
        return DetalleOrden::find($id);
    }

    /**
     * Encuentra un detalle de orden con relaciones
     *
     * @param int $id
     * @return DetalleOrden|null
     */
    public function encontrarConRelaciones(int $id): ?DetalleOrden
    {
        return DetalleOrden::with([
            'orden.tipoOrden.parametro',
            'producto',
            'estadoOrden.parametro',
            'devoluciones'
        ])->find($id);
    }
}

