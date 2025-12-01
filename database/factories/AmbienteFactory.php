<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Exceptions\AmbienteFactoryException;
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

        // Obtener un piso existente o crear uno nuevo (piso_id es NOT NULL)
        $pisoId = null;
        if (Schema::hasTable('pisos')) {
            try {
                $piso = \App\Models\Piso::inRandomOrder()->first();
                if ($piso) {
                    $pisoId = $piso->id;
                }
            } catch (\Exception $e) {
                // Ignorar error de consulta
            }
        }

        // Si no hay piso, crear uno nuevo
        if (!$pisoId) {
            try {
                $piso = \App\Models\Piso::factory()->create();
                $pisoId = $piso->id;
            } catch (\Exception $e) {
                throw new AmbienteFactoryException(
                    'No se pudo crear un Piso para el Ambiente. Error: ' . $e->getMessage(),
                    0,
                    $e
                );
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
