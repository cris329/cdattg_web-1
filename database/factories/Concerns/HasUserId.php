<?php

declare(strict_types=1);

namespace Database\Factories\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\Schema;

trait HasUserId
{
    /**
     * Obtiene un ID de usuario existente o crea uno nuevo
     */
    protected function getUserId(): int
    {
        if (!Schema::hasTable('users')) {
            return 1;
        }

        try {
            $userId = User::query()->inRandomOrder()->value('id');
            return $userId ?? User::factory()->create()->id;
        } catch (\Exception $e) {
            return User::factory()->create()->id;
        }
    }
}

