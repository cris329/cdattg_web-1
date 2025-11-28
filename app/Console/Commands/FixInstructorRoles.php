<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Instructor;
use Spatie\Permission\Models\Role;

class FixInstructorRoles extends Command
{
    protected $signature = 'roles:fix-instructors {--dry-run : Solo mostrar qué se haría sin ejecutar cambios}';
    protected $description = 'Asigna el rol INSTRUCTOR a todos los instructores que no lo tienen';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🔍 MODO DRY-RUN: Solo se mostrarán los cambios que se harían');
            $this->newLine();
        }

        $this->info('🔧 Corrigiendo roles de instructores...');
        $this->newLine();

        $instructores = Instructor::with(['persona.user'])->get();
        $corregidos = 0;
        $yaTienenRol = 0;

        // Asegurar que el rol INSTRUCTOR existe
        $instructorRole = Role::firstOrCreate(['name' => 'INSTRUCTOR']);

        foreach ($instructores as $instructor) {
            if ($instructor->persona && $instructor->persona->user) {
                $user = $instructor->persona->user;
                $nombre = trim($instructor->persona->primer_nombre . ' ' . $instructor->persona->primer_apellido);

                if (!$user->hasRole('INSTRUCTOR')) {
                    if (!$dryRun) {
                        $user->syncRoles(['INSTRUCTOR']);
                    }
                    $corregidos++;
                    $this->line("✅ {$nombre}: " . ($dryRun ? "Se asignaría rol INSTRUCTOR" : "Rol INSTRUCTOR asignado"));
                } else {
                    $yaTienenRol++;
                    $this->line("ℹ️  {$nombre}: Ya tiene rol INSTRUCTOR");
                }
            }
        }

        $this->newLine();
        $this->info("📊 RESUMEN:");
        $this->line("   - Corregidos: {$corregidos}");
        $this->line("   - Ya tenían rol: {$yaTienenRol}");
        $this->line("   - Total procesados: " . $instructores->count());

        if ($corregidos > 0) {
            $this->newLine();
            $this->info("✅ " . ($dryRun ? "Se corregirían" : "Se corrigieron") . " {$corregidos} instructores.");
        } else {
            $this->info("✅ Todos los instructores ya tienen el rol correcto.");
        }
    }
}

