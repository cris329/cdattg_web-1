<?php

namespace Database\Factories;

use App\Models\TipoPrograma;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TipoPrograma>
 */
class TipoProgramaFactory extends Factory
{
    protected $model = TipoPrograma::class;

    public function definition(): array
    {
        $tipos = [
            'PROGRAMA DE FORMACIÓN TITULADA',
            'PROGRAMA DE FORMACIÓN COMPLEMENTARIA',
            'ESPECIALIZACIÓN',
        ];

        return [
            'tipo_programa' => $this->faker->randomElement($tipos),
        ];
    }
}
