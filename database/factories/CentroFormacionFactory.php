<?php

namespace Database\Factories;

use App\Models\CentroFormacion;
use App\Models\Regional;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CentroFormacion>
 */
class CentroFormacionFactory extends Factory
{
    protected $model = CentroFormacion::class;

    public function definition(): array
    {
        $regionalId = 1;
        if (Schema::hasTable('regionals')) {
            try {
                $regionalId = Regional::query()->inRandomOrder()->value('id');
                if (! $regionalId) {
                    $regionalId = Regional::factory()->create()->id;
                }
            } catch (\Exception $e) {
                $regionalId = Regional::factory()->create()->id;
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
            'nombre' => $this->faker->company().' - Centro de Formación',
            'regional_id' => $regionalId,
            'telefono' => $this->faker->numerify('60#######'),
            'direccion' => $this->faker->address(),
            'web' => $this->faker->optional()->url(),
            'status' => $this->faker->boolean(90),
            'user_create_id' => $userId,
            'user_update_id' => $userId,
        ];
    }
}
