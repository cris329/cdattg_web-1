<?php

namespace Database\Factories;

use App\Models\PersonaImport;
use App\Models\PersonaImportIssue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PersonaImportIssue>
 */
class PersonaImportIssueFactory extends Factory
{
    protected $model = PersonaImportIssue::class;

    public function definition(): array
    {
        $issueTypes = ['duplicate', 'validation_error', 'missing_data', 'database_error'];

        return [
            'persona_import_id' => PersonaImport::factory(),
            'row_number' => $this->faker->numberBetween(1, 1000),
            'issue_type' => $this->faker->randomElement($issueTypes),
            'numero_documento' => $this->faker->optional()->numerify('##########'),
            'email' => $this->faker->optional()->email(),
            'celular' => $this->faker->optional()->numerify('3##########'),
            'error_message' => $this->faker->sentence(),
            'raw_payload' => [
                'nombre' => $this->faker->name(),
                'documento' => $this->faker->numerify('##########'),
            ],
        ];
    }
}
