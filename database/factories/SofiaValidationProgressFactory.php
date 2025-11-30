<?php

namespace Database\Factories;

use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Complementarios\SofiaValidationProgress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Complementarios\SofiaValidationProgress>
 */
class SofiaValidationProgressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SofiaValidationProgress::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'complementario_id' => ComplementarioOfertado::factory(),
            'user_id' => User::factory(),
            'status' => 'pending',
            'total_aspirantes' => $this->faker->numberBetween(1, 100),
            'processed_aspirantes' => 0,
            'successful_validations' => 0,
            'failed_validations' => 0,
            'errors' => [],
            'started_at' => null,
            'completed_at' => null,
        ];
    }

    /**
     * Indicate that the validation is processing.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    /**
     * Indicate that the validation is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Indicate that the validation failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'completed_at' => now(),
            'errors' => ['Error de ejemplo'],
        ]);
    }
}
