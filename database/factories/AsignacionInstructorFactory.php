<?php

namespace Database\Factories;

use App\Models\AsignacionInstructor;
use App\Models\Competencia;
use App\Models\FichaCaracterizacion;
use App\Models\Instructor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AsignacionInstructor>
 */
class AsignacionInstructorFactory extends Factory
{
    protected $model = AsignacionInstructor::class;

    public function definition(): array
    {
        $fichaId = 1;
        $instructorId = 1;
        $competenciaId = 1;

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

        if (Schema::hasTable('competencias')) {
            try {
                $competenciaId = Competencia::query()->inRandomOrder()->value('id');
                if (! $competenciaId) {
                    $competenciaId = Competencia::factory()->create()->id;
                }
            } catch (\Exception $e) {
                $competenciaId = Competencia::factory()->create()->id;
            }
        }

        return [
            'ficha_id' => $fichaId,
            'instructor_id' => $instructorId,
            'competencia_id' => $competenciaId,
        ];
    }
}
