<?php

namespace Database\Factories;

use App\Models\Persona;
use App\Models\PersonaContactAlert;
use App\Models\PersonaImport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PersonaContactAlert>
 */
class PersonaContactAlertFactory extends Factory
{
    protected $model = PersonaContactAlert::class;

    public function definition(): array
    {
        return [
            'persona_id' => Persona::factory(),
            'persona_import_id' => PersonaImport::factory(),
            'missing_email' => $this->faker->boolean(30),
            'missing_celular' => $this->faker->boolean(20),
            'missing_telefono' => $this->faker->boolean(40),
            'observaciones' => $this->faker->optional()->sentence(),
            'raw_payload' => [
                'primer_nombre' => $this->faker->firstName(),
                'primer_apellido' => $this->faker->lastName(),
                'numero_documento' => $this->faker->numerify('##########'),
            ],
        ];
    }
}
