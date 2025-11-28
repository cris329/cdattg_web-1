<?php

namespace Tests;

use App\Exceptions\MigrationBatchException;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Ejecuta las migraciones para los tests usando el sistema de migraciones modulares.
     * Esto asegura que las migraciones se ejecuten en el orden correcto según las dependencias.
     *
     * @return void
     */
    protected function migrateDatabases()
    {
        // Limpiar completamente la base de datos
        // Usar migrate:reset para limpiar todas las migraciones primero
        try {
            $this->artisan('migrate:reset', ['--force' => true]);
        } catch (\Exception $e) {
            // Si no hay migraciones, continuar
        }

        // Eliminar todas las tablas manualmente (para SQLite)
        $tables = \Illuminate\Support\Facades\DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name != 'sqlite_sequence'");
        foreach ($tables as $table) {
            \Illuminate\Support\Facades\DB::statement('DROP TABLE IF EXISTS ' . $table->name);
        }

        // Ejecutar todas las migraciones modulares en orden
        $batches = [
            'batch_01_sistema_base',
            'batch_02_permisos',
            'batch_03_parametros',
            'batch_04_ubicaciones',
            'batch_05_personas',
            'batch_06_infraestructura',
            'batch_07_programas',
            'batch_08_fichas',
            'batch_09_instructores_aprendices',
            'batch_10_relaciones',
            'batch_11_jornadas_horarios',
            'batch_12_asistencias',
            'batch_13_competencias',
            'batch_14_evidencias',
            'batch_15_logs_auditoria',
            'batch_16_inventario',
            'batch_17_complementarios',
            'batch_18_entrada_salida',
        ];

        foreach ($batches as $batch) {
            $path = "database/migrations/{$batch}";
            if (is_dir(base_path($path))) {
                $result = $this->artisan('migrate', [
                    '--path' => $path,
                    '--force' => true,
                ]);

                if ($result !== 0) {
                    throw new MigrationBatchException($batch);
                }
            }
        }

        // seeders
        $this->artisan('db:seed');
    }
}
