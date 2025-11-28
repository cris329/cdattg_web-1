<?php

namespace Database\Factories;

use App\Models\AprendizFicha;
use App\Models\AsistenciaAprendiz;
use App\Models\Evidencias;
use App\Models\InstructorFichaCaracterizacion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AsistenciaAprendiz>
 */
class AsistenciaAprendizFactory extends Factory
{
    protected $model = AsistenciaAprendiz::class;

    public function definition(): array
    {
        $instructorFichaId = 1;
        $aprendizFichaId = 1;
        $evidenciaId = null;

        if (Schema::hasTable('instructor_fichas_caracterizacion')) {
            try {
                $instructorFichaId = InstructorFichaCaracterizacion::query()->inRandomOrder()->value('id');
                if (! $instructorFichaId) {
                    $instructorFichaId = InstructorFichaCaracterizacion::factory()->create()->id;
                }
            } catch (\Exception $e) {
                $instructorFichaId = InstructorFichaCaracterizacion::factory()->create()->id;
            }
        }

        if (Schema::hasTable('aprendiz_fichas_caracterizacion')) {
            try {
                $aprendizFichaId = AprendizFicha::query()->inRandomOrder()->value('id');
                if (! $aprendizFichaId) {
                    $aprendizFichaId = AprendizFicha::factory()->create()->id;
                }
            } catch (\Exception $e) {
                $aprendizFichaId = AprendizFicha::factory()->create()->id;
            }
        }

        if (Schema::hasTable('evidencias') && $this->faker->boolean(30)) {
            try {
                $evidenciaId = Evidencias::query()->inRandomOrder()->value('id');
            } catch (\Exception $e) {
                $evidenciaId = null;
            }
        }

        $horaIngreso = $this->faker->dateTimeBetween('08:00:00', '10:00:00')->format('H:i:s');
        $horaSalida = null;
        if ($this->faker->boolean(80)) {
            $horaSalida = $this->faker->dateTimeBetween($horaIngreso, '18:00:00')->format('H:i:s');
        }

        return [
            'instructor_ficha_id' => $instructorFichaId,
            'aprendiz_ficha_id' => $aprendizFichaId,
            'evidencia_id' => $evidenciaId,
            'hora_ingreso' => $horaIngreso,
            'hora_salida' => $horaSalida,
        ];
    }
}
