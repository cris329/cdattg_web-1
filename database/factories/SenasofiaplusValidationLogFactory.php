<?php

namespace Database\Factories;

use App\Models\Complementarios\AspiranteComplementario;
use App\Models\SenasofiaplusValidationLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SenasofiaplusValidationLog>
 */
class SenasofiaplusValidationLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'aspirante_id' => AspiranteComplementario::factory(),
            'accion' => 'validar',
            'detalles' => null,
            'resultado' => $this->faker->randomElement(['exitoso', 'error', 'advertencia']),
            'mensaje' => $this->faker->sentence(),
            'user_id' => User::factory(),
            'fecha_accion' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'datos_anteriores' => null,
            'datos_nuevos' => null,
        ];
    }

    /**
     * Indicate that the log is successful.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'resultado' => 'exitoso',
        ]);
    }

    /**
     * Indicate that the log has an error.
     */
    public function error(): static
    {
        return $this->state(fn (array $attributes) => [
            'resultado' => 'error',
        ]);
    }
}
