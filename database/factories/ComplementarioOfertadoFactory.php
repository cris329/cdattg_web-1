<?php

namespace Database\Factories;

use App\Models\Ambiente;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\JornadaFormacion;
use App\Models\ParametroTema;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Complementarios\ComplementarioOfertado>
 */
class ComplementarioOfertadoFactory extends Factory
{
    protected $model = ComplementarioOfertado::class;

    public function definition(): array
    {
        return [
            'codigo' => $this->generarCodigo(),
            'nombre' => $this->obtenerNombre(),
            'justificacion' => $this->faker->paragraph(2),
            'requisitos_ingreso' => $this->faker->paragraph(2),
            'duracion' => $this->faker->numberBetween(30, 120),
            'cupos' => $this->faker->numberBetween(10, 50),
            'estado_id' => $this->obtenerEstadoId(),
            'modalidad_id' => $this->obtenerModalidadId(),
            'jornada_id' => $this->obtenerJornadaId(),
            'ambiente_id' => $this->obtenerAmbienteId(),
        ];
    }

    /**
     * Estado: Sin Oferta
     */
    public function sinOferta(): static
    {
        return $this->state(fn () => [
            'estado_id' => $this->getEstadoIdByName('Sin Oferta'),
        ]);
    }

    /**
     * Estado: Con Oferta
     */
    public function conOferta(): static
    {
        return $this->state(fn () => [
            'estado_id' => $this->getEstadoIdByName('Con Oferta'),
        ]);
    }

    /**
     * Estado: Cupos Llenos
     */
    public function cuposLlenos(): static
    {
        return $this->state(fn () => [
            'estado_id' => $this->getEstadoIdByName('Cupos Llenos'),
        ]);
    }

    /**
     * Genera un código único para el programa complementario
     */
    private function generarCodigo(): string
    {
        return 'COMP' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Obtiene un nombre para el programa complementario
     */
    private function obtenerNombre(): string
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
        
        try {
            return $this->faker->unique()->randomElement($nombres);
        } catch (\OverflowException $e) {
            // Si se agotaron los nombres únicos, generar uno nuevo
            return $this->faker->words(3, true) . ' ' . $this->faker->unique()->numberBetween(1000, 9999);
        }
    }

    /**
     * Obtiene un ID de modalidad (ParametroTema)
     */
    private function obtenerModalidadId(): ?int
    {
        try {
            $modalidadId = ParametroTema::where('tema_id', 5)
                ->whereIn('parametro_id', [18, 19, 20])
                ->inRandomOrder()
                ->value('id');
            
            if ($modalidadId) {
                return $modalidadId;
            }
        } catch (\Exception $e) {
            // Continuar si hay error
        }
        
        // Si no se encontró, intentar crear los registros necesarios
        return $this->crearModalidadSiNoExiste();
    }

    /**
     * Crea los registros de modalidad si no existen
     */
    private function crearModalidadSiNoExiste(): ?int
    {
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
                return $modalidad->id;
            } catch (\Exception $e) {
                // Si falla, intentar obtener cualquier ParametroTema del tema 5
                return ParametroTema::where('tema_id', 5)->value('id');
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtiene un ID de jornada de formación
     */
    private function obtenerJornadaId(): int
    {
        $jornadaId = JornadaFormacion::inRandomOrder()->value('id');
        
        if (!$jornadaId) {
            $jornada = JornadaFormacion::factory()->create();
            $jornadaId = $jornada->id;
        }
        
        return $jornadaId;
    }

    /**
     * Obtiene un ID de ambiente (puede ser null)
     */
    private function obtenerAmbienteId(): ?int
    {
        return Ambiente::where('status', 1)->inRandomOrder()->value('id');
    }

    /**
     * Obtiene un ID de estado parametrizado
     */
    private function obtenerEstadoId(): ?int
    {
        try {
            // Buscar el tema ESTADO_PROGRAMA_COMPLEMENTARIO
            $temaEstado = \App\Models\Tema::where('name', 'ESTADO_PROGRAMA_COMPLEMENTARIO')->first();
            
            if ($temaEstado) {
                // Obtener un ParametroTema aleatorio de este tema
                $estadoId = \App\Models\ParametroTema::where('tema_id', $temaEstado->id)
                    ->inRandomOrder()
                    ->value('id');
                
                if ($estadoId) {
                    return $estadoId;
                }
            }
        } catch (\Exception $e) {
            // Si hay error, continuar con null
        }
        
        // Si no se encontró estado parametrizado, intentar obtener cualquier ParametroTema como fallback
        return \App\Models\ParametroTema::inRandomOrder()->value('id');
    }

    /**
     * Helper para obtener el ID de ParametroTema por nombre de estado
     */
    private function getEstadoIdByName(string $nombreEstado): ?int
    {
        try {
            $temaEstado = \App\Models\Tema::where('name', 'ESTADO_PROGRAMA_COMPLEMENTARIO')->first();
            
            if ($temaEstado) {
                $parametro = \App\Models\Parametro::where('name', $nombreEstado)->first();
                
                if ($parametro) {
                    $parametroTema = \App\Models\ParametroTema::where('tema_id', $temaEstado->id)
                        ->where('parametro_id', $parametro->id)
                        ->first();
                    
                    if ($parametroTema) {
                        return $parametroTema->id;
                    }
                }
            }
        } catch (\Exception $e) {
            // Si hay error, retornar null
        }
        
        return null;
    }

    /**
     * Con cupos disponibles
     */
    public function conCupos(int $cupos = null): static
    {
        $cuposFinal = $cupos ?? $this->faker->numberBetween(20, 50);
        
        return $this->state(fn () => [
            'cupos' => $cuposFinal,
            'estado_id' => $this->getEstadoIdByName('Con Oferta'),
        ]);
    }

    /**
     * Con duración específica
     */
    public function conDuracion(int $horas): static
    {
        return $this->state(fn () => [
            'duracion' => $horas,
        ]);
    }
}
