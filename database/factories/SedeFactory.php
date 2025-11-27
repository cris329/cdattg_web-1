<?php

namespace Database\Factories;

use App\Models\Municipio;
use App\Models\Regional;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sede>
 */
class SedeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Municipio
        $municipioId = 1;
        if (Schema::hasTable('municipios')) {
            try {
                $municipioId = Municipio::query()->inRandomOrder()->value('id') ?? 1;
            } catch (\Exception $e) {
                try {
                    $municipioId = Municipio::factory()->create()->id;
                } catch (\Exception $e) {
                    $municipioId = 1;
                }
            }
        }

        // Regional - usar valor por defecto directamente sin consultar
        // Esto evita errores cuando la tabla no existe durante las migraciones modulares
        $regionalId = 1;

        // User
        $userId = config('app.audit_default_user_id', 1);
        if (Schema::hasTable('users')) {
            try {
                $userId = User::query()->inRandomOrder()->value('id') ?? $userId;
            } catch (\Exception $e) {
                try {
                    $userId = User::factory()->create()->id;
                } catch (\Exception $e) {
                    $userId = config('app.audit_default_user_id', 1);
                }
            }
        }

        return [
            'sede' => $this->faker->unique()->company(),
            'direccion' => $this->faker->address(),
            'municipio_id' => $municipioId,
            'regional_id' => $regionalId,
            'user_create_id' => $userId,
            'user_edit_id' => $userId,
            'status' => $this->faker->boolean(),
        ];
    }
}
