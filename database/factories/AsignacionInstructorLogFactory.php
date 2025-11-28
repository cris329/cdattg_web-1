<?php

namespace Database\Factories;

use App\Models\AsignacionInstructorLog;
use App\Models\FichaCaracterizacion;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AsignacionInstructorLog>
 */
class AsignacionInstructorLogFactory extends Factory
{
    protected $model = AsignacionInstructorLog::class;

    public function definition(): array
    {
        $instructorId = 1;
        $fichaId = 1;
        $userId = config('app.audit_default_user_id', 1);

        if (Schema::hasTable('instructors')) {
            try {
                $instructorId = Instructor::query()->inRandomOrder()->value('id');
                if (! $instructorId) {
                    $instructorId = Instructor::factory()->create()->id;
                }
            } catch (\Exception $e) {
                $instructorId = Instructor::factory()->create()->id;
            }
        }

        if (Schema::hasTable('fichas_caracterizacion')) {
            try {
                $fichaId = FichaCaracterizacion::query()->inRandomOrder()->value('id');
                if (! $fichaId) {
                    $fichaId = FichaCaracterizacion::factory()->create()->id;
                }
            } catch (\Exception $e) {
                $fichaId = FichaCaracterizacion::factory()->create()->id;
            }
        }

        if (Schema::hasTable('users')) {
            try {
                $userId = User::query()->inRandomOrder()->value('id') ?? $userId;
            } catch (\Exception $e) {
                $userId = config('app.audit_default_user_id', 1);
            }
        }

        $acciones = ['CREAR', 'ACTUALIZAR', 'ELIMINAR', 'ASIGNAR', 'DESASIGNAR'];
        $resultados = ['EXITOSO', 'ERROR', 'PENDIENTE'];

        return [
            'instructor_id' => $instructorId,
            'ficha_id' => $fichaId,
            'accion' => $this->faker->randomElement($acciones),
            'detalles' => [
                'comentario' => $this->faker->sentence(),
                'timestamp' => now()->toIso8601String(),
            ],
            'resultado' => $this->faker->randomElement($resultados),
            'mensaje' => $this->faker->optional()->sentence(),
            'user_id' => $userId,
            'fecha_accion' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'datos_anteriores' => $this->faker->optional()->randomElement([null, ['campo' => 'valor_anterior']]),
            'datos_nuevos' => $this->faker->optional()->randomElement([null, ['campo' => 'valor_nuevo']]),
        ];
    }
}
