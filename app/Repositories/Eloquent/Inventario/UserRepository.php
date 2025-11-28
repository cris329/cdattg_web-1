<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent\Inventario;

use App\Services\Inventario\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function obtenerSuperAdministradores(): Collection
    {
        return User::role('SUPER ADMINISTRADOR')->get();
    }
}


