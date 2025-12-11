<?php

declare(strict_types=1);

namespace App\Inventario\Interfaces\Repositories\Orden;

use App\Models\Inventario\Orden;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OrdenRepositoryInterface
{
    public function obtenerConFiltros(array $filtros = []): LengthAwarePaginator;
    public function obtenerPendientes(int $estadoEnEsperaId, ?int $userId = null): LengthAwarePaginator;
    public function obtenerCompletadas(int $estadoAprobadaId, ?int $userId = null): LengthAwarePaginator;
    public function obtenerRechazadas(int $estadoRechazadaId, ?int $userId = null): LengthAwarePaginator;
    public function encontrarConRelaciones(int $id): ?Orden;
    public function encontrarConDetallesYDevoluciones(int $id): ?Orden;
    public function obtenerDetallesPendientes(int $estadoEnEsperaId): Collection;
    public function crear(array $datos): Orden;
    public function actualizar(Orden $orden, array $datos): bool;
    public function eliminar(Orden $orden): bool;
}

