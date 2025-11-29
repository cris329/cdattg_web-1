<?php

namespace Database\Factories;

use App\Models\Ambiente;
use App\Models\ComplementarioOfertado;
use App\Models\JornadaFormacion;
use App\Models\ParametroTema;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ComplementarioOfertado>
 */
class ComplementarioOfertadoFactory extends Factory
{
    protected $model = ComplementarioOfertado::class;

    public function definition(): array
    {
        $nombres = [
            'Auxiliar de Cocina',
            'Acabados en Madera',
            'Confección de Prendas',
            'Mecánica Básica Automotriz',
            'Cultivos de Huertas Urbanas',
            'Normatividad Laboral',
            'Soldadura Básica',
            'Electricidad Residencial',
            'Plomería Básica',
            'Panadería y Repostería',
            'Corte y Confección',
            'Jardinería y Paisajismo',
        ];
        
        $nombre = $this->faker->unique()->randomElement($nombres);
        
        // Obtener IDs reales o crear registros si no existen
        // tema_id 5 es MODALIDADES DE FORMACION, parámetros 18, 19, 20
        $modalidadId = null;
        
        try {
            $modalidadId = ParametroTema::where('tema_id', 5)
                ->whereIn('parametro_id', [18, 19, 20])
                ->inRandomOrder()
                ->value('id');
        } catch (\Exception $e) {
            // Continuar si hay error
        }
            
        if (!$modalidadId) {
            try {
                // Crear el tema si no existe
                $tema = \App\Models\Tema::firstOrCreate(
                    ['id' => 5],
                    ['name' => 'MODALIDADES DE FORMACION', 'status' => 1]
                );
                
                // Crear los parámetros de modalidad si no existen (18, 19, 20)
                $parametrosModalidad = [
                    18 => 'PRESENCIAL',
                    19 => 'VIRTUAL',
                    20 => 'MIXTA',
                ];
                
                foreach ($parametrosModalidad as $paramId => $paramName) {
                    try {
                        \App\Models\Parametro::firstOrCreate(
                            ['id' => $paramId],
                            ['name' => $paramName, 'status' => 1]
                        );
                    } catch (\Exception $e) {
                        // Continuar si el parámetro ya existe
                    }
                }
                
                // Crear un ParametroTema con uno de los parámetros de modalidad
                $parametroId = 18; // Usar PRESENCIAL por defecto
                try {
                    $modalidad = ParametroTema::firstOrCreate([
                        'tema_id' => $tema->id,
                        'parametro_id' => $parametroId,
                    ]);
                    $modalidadId = $modalidad->id;
                } catch (\Exception $e) {
                    // Si falla, intentar obtener cualquier ParametroTema del tema 5
                    $modalidadId = ParametroTema::where('tema_id', 5)->value('id');
                }
            } catch (\Exception $e) {
                // Si todo falla, usar null (permitir que sea nullable si la migración lo permite)
                $modalidadId = null;
            }
        }
        
        $jornadaId = JornadaFormacion::inRandomOrder()->value('id');
        if (!$jornadaId) {
            $jornada = JornadaFormacion::factory()->create();
            $jornadaId = $jornada->id;
        }
        
        $ambienteId = Ambiente::where('status', 1)->inRandomOrder()->value('id');
        // Si no hay ambientes, usar null (permitir que sea nullable si la migración lo permite)

        return [
            'codigo' => 'COMP' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'nombre' => $nombre,
            'justificacion' => $this->faker->paragraph(2),
            'requisitos_ingreso' => $this->faker->paragraph(2),
            'duracion' => $this->faker->numberBetween(30, 120),
            'cupos' => $this->faker->numberBetween(10, 50),
            'estado' => $this->faker->randomElement([0, 1, 2]),
            'modalidad_id' => $modalidadId,
            'jornada_id' => $jornadaId,
            'ambiente_id' => $ambienteId, // nullable según la migración
        ];
    }

    /**
     * Estado: Sin Oferta
     */
    public function sinOferta(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 0,
        ]);
    }

    /**
     * Estado: Con Oferta
     */
    public function conOferta(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 1,
        ]);
    }

    /**
     * Estado: Cupos Llenos
     */
    public function cuposLlenos(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 2,
        ]);
    }

    /**
     * Con cupos disponibles
     */
    public function conCupos(int $cupos = null): static
    {
        return $this->state(fn (array $attributes) => [
            'cupos' => $cupos ?? $this->faker->numberBetween(20, 50),
            'estado' => 1,
        ]);
    }

    /**
     * Con duración específica
     */
    public function conDuracion(int $horas): static
    {
        return $this->state(fn (array $attributes) => [
            'duracion' => $horas,
        ]);
    }
}


