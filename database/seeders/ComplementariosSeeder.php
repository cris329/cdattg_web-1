<?php

namespace Database\Seeders;

use App\Models\AspiranteComplementario;
use App\Models\ComplementarioOfertado;
use App\Models\Persona;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplementariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Creando datos de prueba para módulo de complementarios...');

        // Crear programas complementarios
        $this->crearProgramas();
        
        // Crear aspirantes
        $this->crearAspirantes();

        $this->command->info('✅ Datos de complementarios creados exitosamente!');
    }

    /**
     * Crear programas complementarios
     */
    private function crearProgramas(): void
    {
        $this->command->info('📚 Creando programas complementarios...');

        // Crear algunos programas con oferta activa
        $programasActivos = ComplementarioOfertado::factory()
            ->count(5)
            ->conOferta()
            ->conCupos(30)
            ->create();

        $this->command->info("   ✓ Creados {$programasActivos->count()} programas con oferta activa");

        // Crear algunos programas sin oferta
        $programasSinOferta = ComplementarioOfertado::factory()
            ->count(2)
            ->sinOferta()
            ->create();

        $this->command->info("   ✓ Creados {$programasSinOferta->count()} programas sin oferta");

        // Crear algunos programas con cupos llenos
        $programasLlenos = ComplementarioOfertado::factory()
            ->count(1)
            ->cuposLlenos()
            ->create();

        $this->command->info("   ✓ Creados {$programasLlenos->count()} programas con cupos llenos");
    }

    /**
     * Crear aspirantes
     */
    private function crearAspirantes(): void
    {
        $this->command->info('👥 Creando aspirantes...');

        $programas = ComplementarioOfertado::all();

        if ($programas->isEmpty()) {
            $this->command->warn('   ⚠ No hay programas disponibles. Creando programas primero...');
            $programas = ComplementarioOfertado::factory()->count(3)->conOferta()->create();
        }

        foreach ($programas as $programa) {
            // Crear aspirantes en proceso
            $enProceso = AspiranteComplementario::factory()
                ->count(rand(5, 15))
                ->enProceso()
                ->paraPrograma($programa)
                ->create();

            // Crear aspirantes completos (con documento)
            $completos = AspiranteComplementario::factory()
                ->count(rand(3, 10))
                ->completo()
                ->paraPrograma($programa)
                ->create();

            // Crear aspirantes admitidos
            $admitidos = AspiranteComplementario::factory()
                ->count(rand(2, 8))
                ->admitido()
                ->paraPrograma($programa)
                ->create();

            // Crear algunos rechazados
            $rechazados = AspiranteComplementario::factory()
                ->count(rand(1, 3))
                ->rechazado()
                ->paraPrograma($programa)
                ->create();

            $total = $enProceso->count() + $completos->count() + $admitidos->count() + $rechazados->count();
            
            $this->command->info("   ✓ Programa '{$programa->nombre}': {$total} aspirantes");
            $this->command->line("      - En proceso: {$enProceso->count()}");
            $this->command->line("      - Completos: {$completos->count()}");
            $this->command->line("      - Admitidos: {$admitidos->count()}");
            $this->command->line("      - Rechazados: {$rechazados->count()}");
        }

        $totalAspirantes = AspiranteComplementario::count();
        $this->command->info("   ✓ Total de aspirantes creados: {$totalAspirantes}");
    }
}
