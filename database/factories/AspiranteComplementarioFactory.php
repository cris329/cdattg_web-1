<?php

namespace Database\Factories;

use App\Models\Complementarios\AspiranteComplementario;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Persona;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Complementarios\AspiranteComplementario>
 */
class AspiranteComplementarioFactory extends Factory
{
    protected $model = AspiranteComplementario::class;

    public function definition(): array
    {
        $observaciones = [
            'El aspirante cumple con todos los requisitos.',
            'Pendiente de documentación adicional.',
            'Completar proceso de inscripción.',
            'Revisar disponibilidad de horarios.',
            'Aspirante en lista de espera.',
            'Documentación completa y verificada.',
            'Requiere seguimiento especial.',
            null, // Algunos sin observaciones
        ];
        
        return [
            'persona_id' => Persona::factory(),
            'complementario_id' => ComplementarioOfertado::factory(),
            'observaciones' => $this->faker->optional(0.7)->randomElement($observaciones),
            'estado' => $this->faker->randomElement([1, 2, 3, 4]),
        ];
    }

    /**
     * Estado: En proceso
     */
    public function enProceso(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 1,
        ]);
    }

    /**
     * Estado: Completo (documento subido)
     */
    public function completo(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 2,
        ]);
    }

    /**
     * Estado: Admitido
     */
    public function admitido(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 3,
        ]);
    }

    /**
     * Estado: Rechazado
     */
    public function rechazado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 2,
        ]);
    }

    /**
     * Con persona específica
     */
    public function paraPersona(Persona $persona): static
    {
        return $this->state(fn (array $attributes) => [
            'persona_id' => $persona->id,
        ]);
    }

    /**
     * Para programa específico
     */
    public function paraPrograma(ComplementarioOfertado $programa): static
    {
        return $this->state(fn (array $attributes) => [
            'complementario_id' => $programa->id,
        ]);
    }

    /**
     * Con observaciones específicas
     */
    public function conObservaciones(string $observaciones): static
    {
        return $this->state(fn (array $attributes) => [
            'observaciones' => $observaciones,
        ]);
    }
}


