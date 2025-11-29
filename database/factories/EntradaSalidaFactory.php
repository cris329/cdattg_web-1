<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Ambiente;
use App\Models\FichaCaracterizacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EntradaSalida>
 */
class EntradaSalidaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Usuario instructor - campo requerido
        $instructorUserId = null;
        if (Schema::hasTable('users')) {
            try {
                $instructorUserId = User::query()->inRandomOrder()->value('id');
            } catch (\Exception $e) {
                // Ignorar error de consulta
            }
        }

        if (!$instructorUserId) {
            $instructorUserId = User::factory()->create()->id;
        }

        // Ficha caracterizacion - campo requerido
        $fichaCaracterizacionId = null;
        if (Schema::hasTable('fichas_caracterizacion')) {
            try {
                $fichaCaracterizacionId = FichaCaracterizacion::query()->inRandomOrder()->value('id');
            } catch (\Exception $e) {
                // Ignorar error de consulta
            }
        }

        if (!$fichaCaracterizacionId) {
            $fichaCaracterizacionId = FichaCaracterizacion::factory()->create()->id;
        }

        // Ambiente - campo requerido
        $ambienteId = null;
        if (Schema::hasTable('ambientes')) {
            try {
                $ambienteId = Ambiente::query()->inRandomOrder()->value('id');
            } catch (\Exception $e) {
                // Ignorar error de consulta
            }
        }

        if (!$ambienteId) {
            $ambienteId = Ambiente::factory()->create()->id;
        }

        return [
            'fecha' => now()->format('Y-m-d'),
            'instructor_user_id' => $instructorUserId,
            'aprendiz' => $this->faker->name(),
            'entrada' => '08:00:00',
            'salida' => null,
            'ficha_caracterizacion_id' => $fichaCaracterizacionId,
            'ambiente_id' => $ambienteId,
            'listado' => null,
        ];
    }
}
