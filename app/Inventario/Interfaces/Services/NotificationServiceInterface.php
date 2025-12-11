<?php

declare(strict_types=1);

namespace App\Inventario\Interfaces\Services;

use App\Models\Inventario\Devolucion;
use App\Models\Inventario\Orden;
use App\Models\Inventario\Producto;

interface NotificationServiceInterface
{
    public function notificarNuevaOrden(Orden $orden): void;
    public function notificarStockBajo(Producto $producto, int $cantidad, int $umbral): void;
    public function notificarDevolucion(Devolucion $devolucion): void;
}

