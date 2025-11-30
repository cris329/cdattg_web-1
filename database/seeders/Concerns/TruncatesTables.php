<?php

namespace Database\Seeders\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait TruncatesTables
{
    protected function truncateModel(string $modelClass): void
    {
        // En testing, RefreshDatabase ya limpia la base de datos
        // No necesitamos truncar en testing
        if (app()->environment('testing')) {
            return;
        }

        /** @var Model $model */
        $model = new $modelClass();
        $table = $model->getTable();
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // En SQLite, necesitamos usar DELETE FROM en lugar de TRUNCATE
            // y deshabilitar foreign keys antes
            try {
                DB::statement('PRAGMA foreign_keys = OFF');
                DB::statement("DELETE FROM {$table}");
                DB::statement("DELETE FROM sqlite_sequence WHERE name='{$table}'");
                DB::statement('PRAGMA foreign_keys = ON');
            } catch (\Exception $e) {
                // Si falla, simplemente continuar - el seeder usará updateOrCreate de todas formas
            }
        } else {
            Schema::disableForeignKeyConstraints();
            try {
                $model->newQuery()->truncate();
            } catch (\Exception $e) {
                // Si falla, simplemente continuar
            } finally {
                Schema::enableForeignKeyConstraints();
            }
        }
    }

    protected function truncateTable(string $table): void
    {
        // En testing, RefreshDatabase ya limpia la base de datos
        // No necesitamos truncar en testing
        if (app()->environment('testing')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // En SQLite, necesitamos usar DELETE FROM en lugar de TRUNCATE
            // y deshabilitar foreign keys antes
            try {
                DB::statement('PRAGMA foreign_keys = OFF');
                DB::statement("DELETE FROM {$table}");
                DB::statement("DELETE FROM sqlite_sequence WHERE name='{$table}'");
                DB::statement('PRAGMA foreign_keys = ON');
            } catch (\Exception $e) {
                // Si falla, simplemente continuar - el seeder usará updateOrCreate de todas formas
            }
        } else {
            Schema::disableForeignKeyConstraints();
            try {
                DB::table($table)->truncate();
            } catch (\Exception $e) {
                // Si falla, simplemente continuar
            } finally {
                Schema::enableForeignKeyConstraints();
            }
        }
    }
}



