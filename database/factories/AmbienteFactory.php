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
        $userId = null;
        if (Schema::hasTable('users')) {
            try {
                $userId = User::query()->inRandomOrder()->value('id');
                if (!$userId) {
                    $userId = User::factory()->create()->id;
                }
            } catch (\Exception $e) {
                $userId = User::factory()->create()->id;
            }
        }

        return [
            'title' => $this->faker->unique()->words(2, true),
            'piso_id' => 1,
            'user_create_id' => $userId ?? 1,
            'user_edit_id' => $userId ?? 1,
            'status' => 1,
        ];
    }
}
