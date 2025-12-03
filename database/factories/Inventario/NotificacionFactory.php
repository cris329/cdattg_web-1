<?php

namespace Database\Factories\Inventario;

use App\Models\Inventario\Notificacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventario\Notificacion>
 */
class NotificacionFactory extends Factory
{
    protected $model = Notificacion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userId = null;
        if (Schema::hasTable('users')) {
            try {
                $userId = User::query()->inRandomOrder()->value('id');
                if (!$userId) {
                    $userId = User::factory()->create()->id;
                }
            } catch (\Exception $e) {
                $userId = null;
            }
        }

        // Tipos de notificación comunes en el sistema
        $tiposNotificacion = [
            'App\Notifications\NuevaOrdenNotification',
            'App\Notifications\OrdenAprobadaNotification',
            'App\Notifications\StockBajoNotification',
            'App\Notifications\DevolucionRegistradaNotification',
        ];

        $tipo = $this->faker->randomElement($tiposNotificacion);
        $notificableType = User::class;
        $notificableId = $userId ?? 1;

        // Datos típicos de una notificación
        $datos = [
            'mensaje' => $this->faker->sentence(),
            'titulo' => $this->faker->words(3, true),
            'icono' => 'info',
            'color' => 'blue',
        ];

        return [
            'id' => Str::uuid()->toString(),
            'tipo' => $tipo,
            'datos' => json_encode($datos),
            'notificable_type' => $notificableType,
            'notificable_id' => $notificableId,
            'leida_en' => $this->faker->boolean(30) ? now() : null, // 30% de probabilidad de estar leída
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'leida_en' => null,
        ]);
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'leida_en' => now()->subMinutes(random_int(1, 1440)), // Leída hace entre 1 minuto y 24 horas
        ]);
    }

    /**
     * Indicate that the notification is for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'notificable_type' => User::class,
            'notificable_id' => $user->id,
        ]);
    }
}

