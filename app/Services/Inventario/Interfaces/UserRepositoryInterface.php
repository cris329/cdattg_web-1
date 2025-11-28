<?php

declare(strict_types=1);

namespace App\Services\Inventario\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function obtenerSuperAdministradores(): Collection;
}


