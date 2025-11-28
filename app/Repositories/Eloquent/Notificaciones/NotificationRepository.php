<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent\Notificaciones;

use App\Models\User;
use App\Repositories\Interfaces\Notificaciones\NotificationRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationRepository implements NotificationRepositoryInterface
{
    /**
     * Obtiene notificaciones paginadas de un usuario
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function obtenerPorUsuarioPaginadas(int $userId, int $perPage): LengthAwarePaginator
    {
        $user = User::findOrFail($userId);
        return $user->notifications()->paginate($perPage);
    }

    /**
     * Obtiene notificaciones no leídas limitadas
     *
     * @param int $userId
     * @param int $limit
     * @return Collection
     */
    public function obtenerNoLeidasLimitadas(int $userId, int $limit): Collection
    {
        $user = User::findOrFail($userId);
        return $user->unreadNotifications()->take($limit)->get();
    }

    /**
     * Cuenta notificaciones no leídas
     *
     * @param int $userId
     * @return int
     */
    public function contarNoLeidas(int $userId): int
    {
        $user = User::findOrFail($userId);
        return $user->unreadNotifications()->count();
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
        $user = User::findOrFail($userId);
        $notification = $user->notifications()->where('id', $notificationId)->first();

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Marca todas las notificaciones como leídas
     *
     * @param int $userId
     * @return int
     */
    public function marcarTodasComoLeidas(int $userId): int
    {
        $user = User::findOrFail($userId);
        $count = 0;

        $user->unreadNotifications->each(function ($notification) use (&$count) {
            $notification->markAsRead();
            $count++;
        });

        return $count;
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
        $user = User::findOrFail($userId);
        $notification = $user->notifications()->where('id', $notificationId)->first();

        if ($notification) {
            $notification->delete();
            return true;
        }

        return false;
    }
}
