<?php

namespace App\Console\Commands;

use App\Models\Aprendiz;
use Illuminate\Console\Command;

class ListarAprendicesProblematicos extends Command
{
    protected $signature = 'aprendices:listar-problematicos';
    protected $description = 'Lista todos los aprendices con datos problemáticos';

    public function handle()
    {
        $this->info('🔍 Buscando aprendices con problemas...');
        $this->newLine();

        $aprendices = Aprendiz::all();
        $problematicos = [];

        foreach ($aprendices as $aprendiz) {
            // Cargar la relación persona
            $persona = $aprendiz->persona;

            if (is_null($persona)) {
                $problematicos[] = [
                    'ID Aprendiz' => $aprendiz->id,
                    'Persona ID' => $aprendiz->persona_id ?? 'NULL',
                    'Ficha ID' => $aprendiz->ficha_caracterizacion_id ?? 'Sin asignar',
                    'Estado' => $aprendiz->estado ? 'Activo' : 'Inactivo',
                    'Creado' => $aprendiz->created_at->format('Y-m-d'),
                ];
            }
        }

        if (!empty($problematicos)) {
            $this->error("⚠️  Encontrados " . count($problematicos) . " aprendices con problemas:");
            $this->newLine();
            $this->table(
                ['ID Aprendiz', 'Persona ID', 'Ficha ID', 'Estado', 'Creado'],
                $problematicos
            );
            $this->newLine();
            $this->warn('💡 Para corregir estos registros:');
            $this->line('   1. Asignar una persona válida desde el panel de edición');
            $this->line('   2. O eliminarlos si son registros inválidos con:');
            $this->line('      php artisan tinker');
            $this->line('      App\Models\Aprendiz::find(ID)->delete()');
        } else {
            $this->info('✅ No se encontraron aprendices con problemas.');
        }

        return Command::SUCCESS;
    }
}

