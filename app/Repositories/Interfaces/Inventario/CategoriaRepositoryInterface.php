<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces\Inventario;

use App\Models\Parametro;
use App\Models\Tema;
use App\Models\Inventario\Categoria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CategoriaRepositoryInterface
{
    public function obtenerTemaCategorias(): ?Tema;
    public function obtenerConFiltros(array $filtros = []): LengthAwarePaginator;
    public function encontrar(int $id): ?Categoria;
    public function encontrarMultiples(array $ids): Collection;
    public function encontrarConRelaciones(int $id): ?Parametro;
    public function actualizar(int $id, array $datos): bool;
    public function eliminar(Parametro $categoria, int $temaId): bool;
    public function tieneProductos(int $id): bool;
}

