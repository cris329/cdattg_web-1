<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces\Notificaciones;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface
{
    public function obtenerPorUsuarioPaginadas(int $userId, int $perPage): LengthAwarePaginator;
    public function obtenerNoLeidasLimitadas(int $userId, int $limit): Collection;
    public function contarNoLeidas(int $userId): int;
    public function marcarComoLeida(int $userId, string $notificationId): bool;
    public function marcarTodasComoLeidas(int $userId): int;
    public function eliminar(int $userId, string $notificationId): bool;
}
