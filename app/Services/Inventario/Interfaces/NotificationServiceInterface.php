<?php

declare(strict_types=1);

namespace App\Services\Inventario\Interfaces;

use App\Models\Inventario\Orden;
use App\Models\Inventario\Producto;

interface NotificationServiceInterface
{
    public function notificarNuevaOrden(Orden $orden): void;
    public function notificarStockBajo(Producto $producto, int $cantidad, int $umbral): void;
}


