<?php

namespace App\Console\Commands;

use App\Models\Aprendiz;
use Illuminate\Console\Command;

class DebugListadoAprendices extends Command
{
    protected $signature = 'aprendices:debug-listado';
    protected $description = 'Debug del listado de aprendices como lo hace el controlador';

    public function handle()
    {
        $this->info('🔍 Simulando el listado del controlador...');
        $this->newLine();

        // Simular exactamente lo que hace el controlador index
        $query = Aprendiz::with(['persona', 'fichaCaracterizacion', 'aprendizFichas.ficha']);
        $aprendices = $query->paginate(10);

        $this->info("Total de aprendices: {$aprendices->total()}");
        $this->info("Aprendices en página 1: {$aprendices->count()}");
        $this->newLine();

        $tableData = [];

        foreach ($aprendices as $aprendiz) {
            $personaCarga = $aprendiz->persona ? 'SI' : 'NO';
            $nombrePersona = $aprendiz->persona?->nombre_completo ?? 'NULL';

            $tableData[] = [
                'ID' => $aprendiz->id,
                'Persona ID' => $aprendiz->persona_id,
                'Relación Carga' => $personaCarga,
                'Nombre' => $nombrePersona,
            ];
        }

        $this->table(
            ['ID', 'Persona ID', 'Relación Carga', 'Nombre'],
            $tableData
        );

        $this->newLine();

        // Verificar si hay alguno sin persona
        $sinPersona = $aprendices->filter(function($a) {
            return is_null($a->persona);
        });

        if ($sinPersona->count() > 0) {
            $this->error("⚠️  Hay {$sinPersona->count()} aprendices sin persona cargada");
        } else {
            $this->info("✅ Todos los aprendices tienen persona cargada correctamente");
        }

        // Verificar las relaciones cargadas
        $this->newLine();
        $this->info('📦 Verificando relaciones cargadas en el primer aprendiz:');
        $primer = $aprendices->first();
        if ($primer) {
            $relaciones = $primer->getRelations();
            $this->line('Relaciones cargadas: ' . implode(', ', array_keys($relaciones)));

            if (isset($relaciones['persona'])) {
                $this->info('✅ Relación "persona" está cargada');
            } else {
                $this->error('❌ Relación "persona" NO está cargada');
            }
        }

        return Command::SUCCESS;
    }
}

