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
            throw new \RuntimeException('La tabla users no existe. Ejecuta las migraciones primero.');
        }

        try {
            $userId = User::query()->inRandomOrder()->value('id');
            if ($userId) {
                return $userId;
            }
        } catch (\Exception $e) {
            // Ignorar error de consulta
        }

        // Si no hay usuarios, intentar crear uno
        try {
            return User::factory()->create()->id;
        } catch (\Exception $e) {
            // Si falla la creación (probablemente falta TemaSeeder), verificar si existe el ID por defecto
            $defaultUserId = (int) config('app.audit_default_user_id', 1);
            $userExists = User::query()->where('id', $defaultUserId)->exists();
            
            if ($userExists) {
                return $defaultUserId;
            }
            
            // Si no existe el User por defecto y no se puede crear, lanzar excepción clara
            throw new \RuntimeException(
                'No se pudo obtener o crear un User. ' .
                'Asegúrate de ejecutar los seeders necesarios (TemaSeeder, PersonaSeeder, UsersSeeder). ' .
                'Error original: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }
}

