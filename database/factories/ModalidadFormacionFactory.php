<?php

namespace Database\Factories;

use App\Models\ModalidadFormacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ModalidadFormacion>
 */
class ModalidadFormacionFactory extends Factory
{
    protected $model = ModalidadFormacion::class;

    public function definition(): array
    {
        $modalidades = [
            'PRESENCIAL',
            'VIRTUAL',
            'A DISTANCIA',
            'BIMODAL',
        ];

        return [
            'modalidad_formacion' => $this->faker->randomElement($modalidades),
        ];
    }
}
