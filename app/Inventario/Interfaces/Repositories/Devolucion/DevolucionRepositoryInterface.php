<?php

declare(strict_types=1);

namespace App\Inventario\Interfaces\Repositories\Devolucion;

use App\Models\Inventario\Devolucion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DevolucionRepositoryInterface
{
    public function obtenerPrestamosPendientes(int $estadoAprobadaId, ?int $userId = null): LengthAwarePaginator;
    public function obtenerHistorial(?int $userId = null): LengthAwarePaginator;
    public function encontrarConRelaciones(int $id): ?Devolucion;
    public function obtenerPrestamosActivosUsuario(int $userId, int $estadoAprobadaId): LengthAwarePaginator;
    public function obtenerHistorialPrestamosUsuario(int $userId): LengthAwarePaginator;
}

