<?php

namespace Database\Seeders\Concerns;

use App\Exceptions\TableTruncateException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait TruncatesTables
{
    protected function truncateModel(string $modelClass): void
    {
        if ($this->isTestingEnvironment()) {
            return;
        }

        /** @var Model $model */
        $model = new $modelClass();
        $this->truncate($model->getTable(), fn () => $model->newQuery()->truncate());
    }

    protected function truncateTable(string $table): void
    {
        if ($this->isTestingEnvironment()) {
            return;
        }

        $this->truncate($table, fn () => DB::table($table)->truncate());
    }

    /**
     * Punto central donde se reduce toda la lógica duplicada y compleja.
     */
    private function truncate(string $table, callable $truncateAction): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $this->truncateSqlite($table);
            return;
        }

        $this->truncateOtherDrivers($truncateAction);
    }

    /**
     * Manejo especializado para SQLite.
     */
    private function truncateSqlite(string $table): void
    {
        try {
            DB::statement('PRAGMA foreign_keys = OFF');
            DB::statement("DELETE FROM {$table}");
            DB::statement("DELETE FROM sqlite_sequence WHERE name='{$table}'");
            DB::statement('PRAGMA foreign_keys = ON');
        } catch (\Throwable $e) {
            throw new TableTruncateException("Error al truncar tabla SQLite: {$table}", 0, $e);
        }
    }

    /**
     * Manejo para MySQL, PostgreSQL, SQL Server, etc.
     */
    private function truncateOtherDrivers(callable $truncateAction): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            $truncateAction();
        } catch (\Throwable $e) {
            throw new TableTruncateException("Error al truncar tabla en driver no SQLite.", 0, $e);
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * Verifica si el entorno es testing.
     */
    private function isTestingEnvironment(): bool
    {
        return app()->environment('testing');
    }
}
