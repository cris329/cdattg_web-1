<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces\Inventario;

use App\Models\Inventario\ContratoConvenio;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ContratoConvenioRepositoryInterface
{
    public function obtenerTodos(): Collection;
    public function obtenerConFiltros(array $filtros = []): LengthAwarePaginator;
    public function encontrarConRelaciones(int $id): ?ContratoConvenio;
    public function crear(array $datos): ContratoConvenio;
    public function actualizar(int $id, array $datos): bool;
    public function eliminar(int $id): bool;
    public function tieneProductos(int $id): bool;
}

