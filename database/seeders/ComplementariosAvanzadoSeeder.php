<?php

namespace Database\Seeders;

use App\Models\Complementarios\AspiranteComplementario;
use App\Models\ComplementarioOfertado;
use App\Models\Persona;
use Illuminate\Database\Seeder;

class ComplementariosAvanzadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Uso: php artisan db:seed --class=ComplementariosAvanzadoSeeder
     */
    public function run(): void
    {
        $this->command->info('🚀 Creando datos avanzados de complementarios...');

        // Opción 1: Crear un programa específico con muchos aspirantes
        $this->crearProgramaConAspirantes();

        // Opción 2: Crear múltiples programas con distribución realista
        $this->crearProgramasDistribuidos();

        // Opción 3: Crear aspirantes para programas existentes
        $this->agregarAspirantesAProgramasExistentes();

        $this->command->info('✅ Datos avanzados creados exitosamente!');
    }

    /**
     * Crear un programa específico con muchos aspirantes
     */
    private function crearProgramaConAspirantes(): void
    {
        $this->command->info('📚 Creando programa con muchos aspirantes...');

        $programa = ComplementarioOfertado::factory()
            ->conOferta()
            ->conCupos(50)
            ->conDuracion(80)
            ->create([
                'nombre' => 'Auxiliar de Cocina',
                'codigo' => 'COMP0001',
            ]);

        // Crear 30 aspirantes en diferentes estados
        AspiranteComplementario::factory()
            ->count(10)
            ->enProceso()
            ->paraPrograma($programa)
            ->create();

        AspiranteComplementario::factory()
            ->count(10)
            ->completo()
            ->paraPrograma($programa)
            ->create();

        AspiranteComplementario::factory()
            ->count(8)
            ->admitido()
            ->paraPrograma($programa)
            ->create();

        AspiranteComplementario::factory()
            ->count(2)
            ->rechazado()
            ->paraPrograma($programa)
            ->create();

        $this->command->info("   ✓ Programa '{$programa->nombre}' creado con 30 aspirantes");
    }

    /**
     * Crear múltiples programas con distribución realista
     */
    private function crearProgramasDistribuidos(): void
    {
        $this->command->info('📚 Creando programas con distribución realista...');

        $programas = [
            ['nombre' => 'Acabados en Madera', 'cupos' => 25, 'duracion' => 60],
            ['nombre' => 'Confección de Prendas', 'cupos' => 30, 'duracion' => 70],
            ['nombre' => 'Mecánica Básica Automotriz', 'cupos' => 20, 'duracion' => 90],
            ['nombre' => 'Cultivos de Huertas Urbanas', 'cupos' => 15, 'duracion' => 50],
        ];

        foreach ($programas as $data) {
            $programa = ComplementarioOfertado::factory()
                ->conOferta()
                ->create($data);

            // Crear aspirantes proporcionales a los cupos
            $cantidadAspirantes = (int) ($data['cupos'] * 1.5); // 50% más aspirantes que cupos

            AspiranteComplementario::factory()
                ->count($cantidadAspirantes)
                ->state(function () {
                    return ['estado' => rand(1, 3)]; // Estados variados
                })
                ->paraPrograma($programa)
                ->create();

            $this->command->info("   ✓ Programa '{$programa->nombre}' con {$cantidadAspirantes} aspirantes");
        }
    }

    /**
     * Agregar aspirantes a programas existentes
     */
    private function agregarAspirantesAProgramasExistentes(): void
    {
        $this->command->info('👥 Agregando aspirantes a programas existentes...');

        $programas = ComplementarioOfertado::where('estado', 1)->get();

        if ($programas->isEmpty()) {
            $this->command->warn('   ⚠ No hay programas activos. Creando algunos primero...');
            $programas = ComplementarioOfertado::factory()->count(3)->conOferta()->create();
        }

        foreach ($programas as $programa) {
            // Obtener cantidad actual de aspirantes
            $aspirantesActuales = $programa->aspirantes()->count();
            
            // Agregar más aspirantes si hay cupos disponibles
            $cuposDisponibles = $programa->cupos - $aspirantesActuales;
            
            if ($cuposDisponibles > 0) {
                $cantidadAgregar = min(rand(5, 15), $cuposDisponibles);
                
                AspiranteComplementario::factory()
                    ->count($cantidadAgregar)
                    ->state(function () {
                        return ['estado' => rand(1, 2)]; // En proceso o completo
                    })
                    ->paraPrograma($programa)
                    ->create();

                $this->command->info("   ✓ Agregados {$cantidadAgregar} aspirantes a '{$programa->nombre}'");
            } else {
                $this->command->line("   - Programa '{$programa->nombre}' sin cupos disponibles");
            }
        }
    }
}
