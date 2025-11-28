<?php

namespace App\Console\Commands;

use App\Models\Aprendiz;
use App\Models\Persona;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerificarRelacionPersona extends Command
{
    protected $signature = 'aprendices:verificar-relacion-persona';
    protected $description = 'Verifica la relación entre aprendices y personas';

    public function handle()
    {
        $this->info('🔍 Verificando relación aprendiz-persona...');
        $this->newLine();

        // Verificar primer aprendiz
        $aprendiz = Aprendiz::first();

        if (!$aprendiz) {
            $this->error('No hay aprendices en la base de datos.');
            return Command::FAILURE;
        }

        $this->line("📋 Aprendiz ID: {$aprendiz->id}");
        $this->line("📋 Persona ID (campo): {$aprendiz->persona_id}");
        $this->newLine();

        // Verificar si la relación carga
        $persona = $aprendiz->persona;

        if ($persona) {
            $this->info("✅ Relación carga correctamente");
            $this->line("👤 Nombre: {$persona->nombre_completo}");
            $this->line("📧 Email: {$persona->email}");
            $this->line("🆔 Documento: {$persona->numero_documento}");
        } else {
            $this->error("❌ La relación NO carga");

            // Verificar si existe la persona en la tabla
            $personaDirecta = Persona::find($aprendiz->persona_id);
            if ($personaDirecta) {
                $this->warn("⚠️  La persona SÍ existe en la tabla personas");
                $this->line("👤 Nombre: {$personaDirecta->nombre_completo}");
                $this->newLine();
                $this->error("🔥 PROBLEMA: La relación está rota en el modelo");
            } else {
                $this->error("⚠️  La persona NO existe en la tabla personas");
                $this->line("El persona_id {$aprendiz->persona_id} no existe");
            }
        }

        $this->newLine();
        $this->info('📊 Verificando la definición de la relación...');

        // Verificar la tabla y columnas
        $aprendizTable = (new Aprendiz())->getTable();
        $personaTable = (new Persona())->getTable();

        $this->line("Tabla aprendiz: {$aprendizTable}");
        $this->line("Tabla persona: {$personaTable}");

        // Verificar columnas de la tabla aprendices
        $columns = DB::select("DESCRIBE {$aprendizTable}");
        $this->newLine();
        $this->line("📋 Columnas de {$aprendizTable}:");

        foreach ($columns as $column) {
            if (strpos($column->Field, 'persona') !== false) {
                $this->info("  - {$column->Field} ({$column->Type})");
            }
        }

        $this->newLine();

        // Probar con eager loading
        $this->info('🔄 Probando con eager loading...');
        $aprendizConPersona = Aprendiz::with('persona')->first();

        if ($aprendizConPersona && $aprendizConPersona->persona) {
            $this->info("✅ Eager loading funciona: {$aprendizConPersona->persona->nombre_completo}");
        } else {
            $this->error("❌ Eager loading NO funciona");
        }

        return Command::SUCCESS;
    }
}

