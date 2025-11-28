<?php

namespace Database\Factories;

use App\Models\Pais;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Departamento>
 */
class DepartamentoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        try {
            $paisId = Pais::query()->inRandomOrder()->value('id');
        } catch (\Exception $e) {
            $paisId = null;
        }

        if (!$paisId) {
            try {
                $paisId = Pais::factory()->create()->id;
            } catch (\Exception $e) {
                $paisId = 1; // Valor por defecto para Colombia
            }
        }

        return [
            'departamento' => $this->faker->unique()->state(),
            'pais_id' => $paisId,
            'status' => $this->faker->boolean(90),
        ];
    }
}
