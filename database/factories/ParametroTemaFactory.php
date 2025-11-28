<?php

namespace Database\Factories;

use App\Models\Parametro;
use App\Models\ParametroTema;
use App\Models\Tema;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParametroTema>
 */
class ParametroTemaFactory extends Factory
{
    protected $model = ParametroTema::class;

    public function definition(): array
    {
        $parametroId = 1;
        $temaId = 1;

        if (Schema::hasTable('parametros')) {
            try {
                $parametroId = Parametro::query()->inRandomOrder()->value('id');
                if (! $parametroId) {
                    $parametroId = Parametro::factory()->create()->id;
                }
            } catch (\Exception $e) {
                $parametroId = Parametro::factory()->create()->id;
            }
        }

        if (Schema::hasTable('temas')) {
            try {
                $temaId = Tema::query()->inRandomOrder()->value('id');
                if (! $temaId) {
                    $temaId = Tema::factory()->create()->id;
                }
            } catch (\Exception $e) {
                $temaId = Tema::factory()->create()->id;
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
            'parametro_id' => $parametroId,
            'tema_id' => $temaId,
            'status' => $this->faker->boolean(90),
            'user_create_id' => $userId,
            'user_edit_id' => $userId,
        ];
    }
}
