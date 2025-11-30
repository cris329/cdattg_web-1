<?php

namespace Tests;

use App\Exceptions\MigrationBatchException;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar APP_KEY para tests si no está configurado
        if (empty(config('app.key'))) {
            config(['app.key' => 'base64:' . base64_encode('12345678901234567890123456789012')]);
        }
    }

    /**
     * Sobrescribe el método migrateDatabases() que usa RefreshDatabase.
     * Esto asegura que las migraciones se ejecuten en el orden correcto según los batches
     * en lugar del orden alfabético por nombre de archivo.
     *
     * @return void
     */
    protected function migrateDatabases()
    {
        // Forzar el uso de SQLite para tests (sobrescribe cualquier configuración del .env)
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => database_path('testing.sqlite')]);
        
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        // Asegurar que el archivo SQLite existe
        $databasePath = config("database.connections.{$connection}.database");
        if ($driver === 'sqlite' && !file_exists($databasePath)) {
            touch($databasePath);
        }

        // Limpiar completamente la base de datos según el driver
        if ($driver === 'sqlite') {
            // Para SQLite, eliminar todas las tablas manualmente
            try {
                $tables = DB::connection($connection)->select("SELECT name FROM sqlite_master WHERE type='table' AND name != 'sqlite_sequence'");
                foreach ($tables as $table) {
                    DB::connection($connection)->statement('DROP TABLE IF EXISTS ' . $table->name);
                }
                // También limpiar la tabla de migraciones
                DB::connection($connection)->statement('DROP TABLE IF EXISTS migrations');
            } catch (\Exception $e) {
                // Continuar si hay error
            }
        } else {
            // Para MySQL/PostgreSQL, eliminar todas las tablas manualmente para evitar problemas de orden
            try {
                // Primero desactivar las foreign keys temporalmente
                if ($driver === 'mysql') {
                    DB::connection($connection)->statement('SET FOREIGN_KEY_CHECKS=0');
                }
                
                // Obtener todas las tablas
                $tables = DB::connection($connection)->select("SHOW TABLES");
                $tableKey = 'Tables_in_' . config("database.connections.{$connection}.database");
                
                foreach ($tables as $table) {
                    $tableName = $table->$tableKey;
                    DB::connection($connection)->statement("DROP TABLE IF EXISTS `{$tableName}`");
                }
                
                // Reactivar las foreign keys
                if ($driver === 'mysql') {
                    DB::connection($connection)->statement('SET FOREIGN_KEY_CHECKS=1');
                }
            } catch (\Exception $e) {
                // Si falla, intentar con migrate:reset
                try {
                    $this->artisan('migrate:reset', ['--force' => true]);
                } catch (\Exception $e2) {
                    // Continuar si no hay migraciones
                }
            }
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
                    '--database' => $connection,
                ]);

                if ($result !== 0) {
                    throw new MigrationBatchException($batch);
                }
            }
        }
    }
}
