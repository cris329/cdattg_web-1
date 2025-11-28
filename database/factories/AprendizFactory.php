<?php

namespace Database\Factories;

use App\Models\Aprendiz;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Aprendiz>
 */
class AprendizFactory extends Factory
{
    protected $model = Aprendiz::class;

    public function definition(): array
    {
        // Obtener o crear usuario para user_create_id y user_edit_id
        $userId = null;
        try {
            $userId = User::query()->inRandomOrder()->value('id');
            if (!$userId) {
                $userId = User::factory()->create()->id;
            }
        } catch (\Exception $e) {
            $userId = User::factory()->create()->id;
        }

        return [
            'persona_id' => Persona::factory(),
            'estado' => (rand(1, 100) <= 90) ? 1 : 0,
            'user_create_id' => $userId ?? 1,
            'user_edit_id' => $userId ?? 1,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Aprendiz $aprendiz) {
            $persona = $aprendiz->persona;

            if (! $persona) {
                return;
            }

            $email = strtolower($persona->email);
            $user = $persona->user;

            if (! $user) {
                $user = User::factory()
                    ->forPersona($persona)
                    ->state([
                        'email' => $email,
                        'status' => $aprendiz->estado ? 1 : 0,
                    ])
                    ->create();
            } else {
                $user->update(['status' => $aprendiz->estado ? 1 : 0]);
            }

            if (! $user->hasRole('APRENDIZ')) {
                $user->assignRole('APRENDIZ');
            }
        });
    }

    public function createdBy(int $userId): static
    {
        return $this->state(fn () => [
            'user_create_id' => $userId,
            'user_edit_id' => $userId,
        ]);
    }
}


