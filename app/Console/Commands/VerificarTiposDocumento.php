<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Parametro;
use App\Models\Tema;

class VerificarTiposDocumento extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aprendices:verificar-tipos-documento';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica los tipos de documento disponibles en el sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando tipos de documento disponibles...');

        // Buscar el tema "TIPO DE DOCUMENTO"
        $temaTipoDoc = Tema::where('name', 'TIPO DE DOCUMENTO')->first();

        if (!$temaTipoDoc) {
            $this->warn('⚠️  No se encontró el tema "TIPO DE DOCUMENTO"');
            $this->info('📋 Temas disponibles:');
            Tema::all(['id', 'name'])->each(function($tema) {
                $this->line("   - ID {$tema->id}: {$tema->name}");
            });
            return 0;
        }

        $this->info("✅ Tema encontrado: {$temaTipoDoc->name} (ID: {$temaTipoDoc->id})");

        // Obtener parámetros asociados al tema
        $parametros = $temaTipoDoc->parametros()->wherePivot('status', 1)->get();

        if ($parametros->isEmpty()) {
            $this->warn('⚠️  No hay parámetros activos para el tema "TIPO DE DOCUMENTO"');
        } else {
            $this->info('📋 Tipos de documento disponibles:');
            $parametros->each(function($parametro) {
                $this->line("   - ID {$parametro->id}: {$parametro->name}");
            });
        }

        // Verificar personas sin tipo de documento
        $personasSinTipoDoc = \App\Models\Persona::whereNull('tipo_documento')->count();
        $this->info("📊 Personas sin tipo de documento: {$personasSinTipoDoc}");

        return 0;
    }
}

