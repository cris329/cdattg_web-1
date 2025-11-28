<?php

namespace Database\Factories;

use App\Models\Competencia;
use App\Models\ResultadosAprendizaje;
use App\Models\ResultadosCompetencia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ResultadosCompetencia>
 */
class ResultadosCompetenciaFactory extends Factory
{
    protected $model = ResultadosCompetencia::class;

    public function definition(): array
    {
        $rapId = 1;
        $competenciaId = 1;

        if (Schema::hasTable('resultados_aprendizajes')) {
            try {
                $rapId = ResultadosAprendizaje::query()->inRandomOrder()->value('id');
                if (! $rapId) {
                    $rapId = ResultadosAprendizaje::factory()->create()->id;
                }
            } catch (\Exception $e) {
                $rapId = ResultadosAprendizaje::factory()->create()->id;
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

        $userId = config('app.audit_default_user_id', 1);
        if (Schema::hasTable('users')) {
            try {
                $userId = User::query()->inRandomOrder()->value('id') ?? $userId;
            } catch (\Exception $e) {
                $userId = config('app.audit_default_user_id', 1);
            }
        }

        return [
            'rap_id' => $rapId,
            'competencia_id' => $competenciaId,
            'user_create_id' => $userId,
            'user_edit_id' => $userId,
        ];
    }
}
