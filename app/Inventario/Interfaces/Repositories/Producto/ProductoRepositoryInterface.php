<?php

declare(strict_types=1);

namespace App\Inventario\Interfaces\Repositories\Producto;

use App\Models\Inventario\Producto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductoRepositoryInterface
{
    public function obtenerConFiltros(array $filtros = []): LengthAwarePaginator;
    public function encontrarConRelaciones(int $id): ?Producto;
    public function encontrar(int $id): ?Producto;
    public function buscarPorCodigoBarras(string $codigo): ?Producto;
    public function obtenerParaCatalogo(array $filtros = []): LengthAwarePaginator;
    public function buscarParaAjax(array $filtros = []): Collection;
    public function obtenerTiposProductos(): Collection;
    public function crear(array $datos): Producto;
    public function actualizar(Producto $producto, array $datos): bool;
    public function eliminar(Producto $producto): bool;
    public function actualizarStock(Producto $producto, int $cantidad): bool;
    public function obtenerMaxCodigoBarras(): ?string;
    public function existeCodigoBarras(string $codigo): bool;
    public function obtenerTodosOrdenadosPorCantidadDesc(): Collection;
}
