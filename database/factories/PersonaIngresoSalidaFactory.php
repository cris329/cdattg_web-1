<?php

namespace Database\Factories;

use App\Models\Ambiente;
use App\Models\FichaCaracterizacion;
use App\Models\Persona;
use App\Models\PersonaIngresoSalida;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PersonaIngresoSalida>
 */
class PersonaIngresoSalidaFactory extends Factory
{
    protected $model = PersonaIngresoSalida::class;

    public function definition(): array
    {
        $personaId = 1;
        $sedeId = 1;
        $ambienteId = null;
        $fichaId = null;

        if (Schema::hasTable('personas')) {
            try {
                $personaId = Persona::query()->inRandomOrder()->value('id');
                if (! $personaId) {
                    $personaId = Persona::factory()->create()->id;
                }
            } catch (\Exception $e) {
                $personaId = Persona::factory()->create()->id;
            }
        }

        if (Schema::hasTable('sedes')) {
            try {
                $sedeId = Sede::query()->inRandomOrder()->value('id');
                if (! $sedeId) {
                    $sedeId = Sede::factory()->create()->id;
                }
            } catch (\Exception $e) {
                $sedeId = Sede::factory()->create()->id;
            }
        }

        if (Schema::hasTable('ambientes') && $this->faker->boolean(70)) {
            try {
                $ambienteId = Ambiente::query()->inRandomOrder()->value('id');
            } catch (\Exception $e) {
                $ambienteId = null;
            }
        }

        if (Schema::hasTable('fichas_caracterizacion') && $this->faker->boolean(40)) {
            try {
                $fichaId = FichaCaracterizacion::query()->inRandomOrder()->value('id');
            } catch (\Exception $e) {
                $fichaId = null;
            }
        }

        $tiposPersona = ['INSTRUCTOR', 'APRENDIZ', 'VISITANTE'];
        $fechaEntrada = $this->faker->dateTimeBetween('-30 days', 'now');
        $horaEntrada = $this->faker->time('H:i:s');
        $timestampEntrada = \Carbon\Carbon::parse($fechaEntrada->format('Y-m-d').' '.$horaEntrada);

        $fechaSalida = null;
        $horaSalida = null;
        $timestampSalida = null;

        if ($this->faker->boolean(70)) {
            $fechaSalida = $this->faker->dateTimeBetween($fechaEntrada, 'now');
            $horaSalida = $this->faker->time('H:i:s');
            $timestampSalida = \Carbon\Carbon::parse($fechaSalida->format('Y-m-d').' '.$horaSalida);
        }

        $userId = config('app.audit_default_user_id', 1);
        if (Schema::hasTable('users')) {
            try {
                $userId = User::query()->inRandomOrder()->value('id') ?? $userId;
            } catch (\Exception $e) {
                $userId = config('app.audit_default_user_id', 1);
            }
        }

        return [
            'persona_id' => $personaId,
            'sede_id' => $sedeId,
            'tipo_persona' => $this->faker->randomElement($tiposPersona),
            'fecha_entrada' => $fechaEntrada->format('Y-m-d'),
            'hora_entrada' => $horaEntrada,
            'timestamp_entrada' => $timestampEntrada,
            'fecha_salida' => $fechaSalida ? $fechaSalida->format('Y-m-d') : null,
            'hora_salida' => $horaSalida,
            'timestamp_salida' => $timestampSalida,
            'ambiente_id' => $ambienteId,
            'ficha_caracterizacion_id' => $fichaId,
            'observaciones' => $this->faker->optional()->sentence(),
            'user_create_id' => $userId,
            'user_edit_id' => $userId,
        ];
    }
}
