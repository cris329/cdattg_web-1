<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ambiente>
 */
class AmbienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Obtener o crear usuario para user_create_id y user_edit_id
        // Usar un ID por defecto para evitar crear usuarios sin persona_id
        $userId = 1;
        if (Schema::hasTable('users')) {
            try {
                $existingUser = User::query()->inRandomOrder()->first();
                if ($existingUser) {
                    $userId = $existingUser->id;
                }
            } catch (\Exception $e) {
                // Si no hay usuarios, usar ID por defecto
                $userId = 1;
            }
        }

        // Obtener un piso existente o usar null si no existe
        $pisoId = null;
        if (Schema::hasTable('pisos')) {
            try {
                $piso = \App\Models\Piso::inRandomOrder()->first();
                if ($piso) {
                    $pisoId = $piso->id;
                }
            } catch (\Exception $e) {
                // Si no hay pisos, usar null
                $pisoId = null;
            }
        }

        return [
            'title' => $this->faker->unique()->words(2, true),
            'piso_id' => $pisoId,
            'user_create_id' => $userId,
            'user_edit_id' => $userId,
            'status' => 1,
        ];
    }
}
