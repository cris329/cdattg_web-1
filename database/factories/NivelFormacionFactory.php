<?php

namespace Database\Factories;

use App\Models\NivelFormacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NivelFormacion>
 */
class NivelFormacionFactory extends Factory
{
    protected $model = NivelFormacion::class;

    public function definition(): array
    {
        $niveles = [
            'TÉCNICO',
            'TECNÓLOGO',
            'ESPECIALIZACIÓN TECNOLÓGICA',
            'ESPECIALIZACIÓN TÉCNICA',
        ];

        return [
            'nivel_formacion' => $this->faker->randomElement($niveles),
        ];
    }
}
