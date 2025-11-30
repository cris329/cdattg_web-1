<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder para el módulo de Inventario - ORIENTADO SOLO A TESTS
 * 
 * Este seeder SOLO ejecuta los seeders base necesarios para que los factories
 * funcionen correctamente en tests.
 * 
 * Uso en tests:
 *   $this->seed(\Database\Seeders\InventarioSeeder::class);
 * 
 * Este seeder NO limpia datos ni crea datos de ejemplo.
 * RefreshDatabase ya maneja la limpieza en tests.
 */
class InventarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Este seeder SOLO ejecuta los seeders base necesarios para tests.
     * No limpia datos ni crea datos de ejemplo.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seeder del módulo de Inventario (solo para tests)...');

        // Ejecutar seeders base necesarios
        $this->ejecutarSeedersBase();

        // Verificar que todo esté correcto
        $this->verificarDatosBase();

        $this->command->info('✅ Seeder del módulo de Inventario completado.');
    }

    /**
     * Ejecuta los seeders base necesarios para el módulo de inventario
     */
    private function ejecutarSeedersBase(): void
    {
        $this->command->info('📦 Ejecutando seeders base...');

        $seedersBase = [
            RolePermissionSeeder::class,  // Permisos y roles
            ParametroSeeder::class,       // Parámetros base
            TemaSeeder::class,             // Temas y parametros_temas
        ];

        foreach ($seedersBase as $seeder) {
            $this->command->line("   → Ejecutando {$seeder}...");
            $this->call($seeder);
        }

        $this->command->info('✅ Seeders base ejecutados correctamente.');
    }

    /**
     * Verifica que los datos base necesarios existan
     */
    private function verificarDatosBase(): void
    {
        $this->command->info('🔍 Verificando datos base...');

        // Verificar que existan temas necesarios
        $temasNecesarios = [
            'TIPOS DE PRODUCTO',
            'UNIDADES DE MEDIDA',
            'ESTADOS DE PRODUCTO',
            'TIPOS DE ORDEN',
            'ESTADOS DE ORDEN',
            'ESTADOS DE APROBACIONES',
            'CATEGORÍAS',
            'MARCAS',
        ];

        foreach ($temasNecesarios as $temaNombre) {
            $tema = \App\Models\Tema::where('name', $temaNombre)->first();
            if (!$tema) {
                $this->command->warn("   ⚠️  Tema '{$temaNombre}' no encontrado.");
            } else {
                $this->command->line("   ✓ Tema '{$temaNombre}' existe.");
            }
        }

        // Verificar que existan usuarios (opcional)
        $usuariosCount = User::count();
        if ($usuariosCount === 0) {
            $this->command->info('   ℹ️  No hay usuarios en la base de datos.');
            $this->command->info('   💡 Puedes crear usuarios con User::factory()->create() en tus tests.');
        } else {
            $this->command->line("   ✓ {$usuariosCount} usuario(s) encontrado(s).");
        }

        $this->command->info('✅ Verificación de datos base completada.');
    }
}
