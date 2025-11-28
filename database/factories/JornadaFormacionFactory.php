<?php

namespace Database\Factories;

use App\Models\JornadaFormacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JornadaFormacion>
 */
class JornadaFormacionFactory extends Factory
{
    protected $model = JornadaFormacion::class;

    public function definition(): array
    {
        $jornadas = [
            'Mañana' => ['inicio' => '06:00:00', 'fin' => '13:10:00'],
            'Tarde' => ['inicio' => '13:00:00', 'fin' => '18:10:00'],
            'Noche' => ['inicio' => '17:50:00', 'fin' => '23:10:00'],
        ];
        
        $jornada = $this->faker->randomElement(array_keys($jornadas));
        $horarios = $jornadas[$jornada];
        
        return [
            'jornada' => $jornada,
            'hora_inicio' => $horarios['inicio'],
            'hora_fin' => $horarios['fin'],
        ];
    }
}

