<?php

namespace Database\Factories;

use App\Models\Tema;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tema>
 */
class TemaFactory extends Factory
{
    protected $model = Tema::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generar un nombre único para el tema
        $nombreBase = $this->faker->words(random_int(2, 4), true);
        $nombreUnico = strtoupper($nombreBase . ' ' . bin2hex(random_bytes(8)));

        // Obtener o crear usuario para user_create_id y user_edit_id
        $userId = null;
        if (Schema::hasTable('users')) {
            try {
                $userId = User::query()->inRandomOrder()->value('id');
                if (!$userId) {
                    $userId = User::factory()->create()->id;
                }
            } catch (\Exception $e) {
                $userId = config('app.audit_default_user_id', null);
            }
        }

        return [
            'name' => $nombreUnico,
            'status' => $this->faker->boolean(90),
            'user_create_id' => $userId,
            'user_edit_id' => $userId,
        ];
    }
}
