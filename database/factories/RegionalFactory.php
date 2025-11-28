<?php

namespace Database\Factories;

use App\Models\Departamento;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Regional>
 */
class RegionalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        try {
            $departamentoId = Departamento::query()->inRandomOrder()->value('id');
        } catch (\Exception $e) {
            $departamentoId = null;
        }

        if (!$departamentoId) {
            try {
                $departamentoId = Departamento::factory()->create()->id;
            } catch (\Exception $e) {
                $departamentoId = null;
            }
        }

        try {
            $userId = User::query()->inRandomOrder()->value('id');
        } catch (\Exception $e) {
            $userId = null;
        }

        if (!$userId) {
            try {
                $userId = User::factory()->create()->id;
            } catch (\Exception $e) {
                $userId = config('app.audit_default_user_id', 1);
            }
        }

        return [
            'nombre' => $this->faker->unique()->company(),
            'departamento_id' => $departamentoId,
            'user_create_id' => $userId,
            'user_edit_id' => $userId,
            'status' => $this->faker->boolean(90),
        ];
    }
}
