<?php

declare(strict_types=1);

namespace App\Inventario\Services\Notification;

use App\Inventario\Interfaces\Services\NotificationServiceInterface;
use App\Inventario\Interfaces\Services\UserRepositoryInterface;
use App\Models\Inventario\Devolucion;
use App\Models\Inventario\Orden;
use App\Models\Inventario\Producto;
use App\Notifications\DevolucionRegistradaNotification;
use App\Notifications\NuevaOrdenNotification;
use App\Notifications\StockBajoNotification;
use Illuminate\Support\Facades\Notification;

class NotificationService implements NotificationServiceInterface
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function notificarNuevaOrden(Orden $orden): void
    {
        $superadmins = $this->userRepository->obtenerSuperAdministradores();

        if ($superadmins->isNotEmpty()) {
            Notification::send($superadmins, new NuevaOrdenNotification($orden));
        }
    }

    public function notificarStockBajo(Producto $producto, int $cantidad, int $umbral): void
    {
        $superadmins = $this->userRepository->obtenerSuperAdministradores();

        if ($superadmins->isEmpty()) {
            return;
        }

        foreach ($superadmins as $admin) {
            $admin->notify(new StockBajoNotification($producto, $cantidad, $umbral));
        }
    }

    public function notificarDevolucion(Devolucion $devolucion): void
    {
        $superadmins = $this->userRepository->obtenerSuperAdministradores();

        if ($superadmins->isEmpty()) {
            return;
        }

        Notification::send($superadmins, new DevolucionRegistradaNotification($devolucion));
    }
}

