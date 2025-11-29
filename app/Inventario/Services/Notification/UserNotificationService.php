<?php

declare(strict_types=1);

namespace App\Inventario\Services\Notification;

use App\Inventario\Interfaces\Repositories\Notification\NotificationRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Servicio para gestión de notificaciones de usuario del módulo de inventario
 * Cumple SRP: responsabilidad única de gestionar notificaciones
 */
class UserNotificationService
{
    protected NotificationRepositoryInterface $repository;

    public function __construct(NotificationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Obtiene notificaciones paginadas del usuario
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function obtenerNotificacionesPaginadas(int $userId, int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? config('inventario.notificaciones.per_page', 10);
        return $this->repository->obtenerPorUsuarioPaginadas($userId, $perPage);
    }

    /**
     * Obtiene notificaciones no leídas del usuario
     *
     * @param int $userId
     * @param int $limit
     * @return Collection
     */
    public function obtenerNoLeidas(int $userId, int $limit = null): Collection
    {
        $limit = $limit ?? config('inventario.notificaciones.dropdown_limit', 5);
        return $this->repository->obtenerNoLeidasLimitadas($userId, $limit);
    }

    /**
     * Cuenta notificaciones no leídas
     *
     * @param int $userId
     * @return int
     */
    public function contarNoLeidas(int $userId): int
    {
        return $this->repository->contarNoLeidas($userId);
    }

    /**
     * Marca una notificación como leída
     *
     * @param int $userId
     * @param string $notificationId
     * @return bool
     */
    public function marcarComoLeida(int $userId, string $notificationId): bool
    {
        return $this->repository->marcarComoLeida($userId, $notificationId);
    }

    /**
     * Marca todas las notificaciones como leídas
     *
     * @param int $userId
     * @return int Número de notificaciones marcadas
     */
    public function marcarTodasComoLeidas(int $userId): int
    {
        return $this->repository->marcarTodasComoLeidas($userId);
    }

    /**
     * Elimina una notificación
     *
     * @param int $userId
     * @param string $notificationId
     * @return bool
     */
    public function eliminar(int $userId, string $notificationId): bool
    {
        return $this->repository->eliminar($userId, $notificationId);
    }

    /**
     * Obtiene datos para el dropdown de notificaciones
     *
     * @param int $userId
     * @return array
     */
    public function obtenerDatosDropdown(int $userId): array
    {
        $limit = config('inventario.notificaciones.dropdown_limit', 5);

        return [
            'notificaciones' => $this->obtenerNoLeidas($userId, $limit),
            'count' => $this->contarNoLeidas($userId)
        ];
    }
}

