<?php

declare(strict_types=1);

namespace App\Services\Inventario\Interfaces;

use App\Models\Inventario\Producto;

interface StockValidatorServiceInterface
{
    public function estaBajoUmbralMinimo(Producto $producto): bool;
    public function estaNivelCritico(Producto $producto): bool;
    public function hayStockSuficiente(Producto $producto, int $cantidadRequerida): bool;
    public function verificarYNotificarCambioStock(Producto $producto, int $cantidadAnterior): void;
    public function getUmbralMinimo(): int;
    public function getUmbralCritico(): int;
    public function calcularPorcentajeStock(Producto $producto, int $stockMaximo): float;
    public function obtenerNivelStock(Producto $producto): string;
    public function validarStockSuficiente(Producto $producto, int $cantidadRequerida): void;
}
