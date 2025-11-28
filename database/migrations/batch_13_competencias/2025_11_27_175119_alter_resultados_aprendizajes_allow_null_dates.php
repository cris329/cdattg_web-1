<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite no soporta MODIFY, necesitamos recrear la tabla
            DB::statement('PRAGMA foreign_keys=off;');
            
            DB::statement('
                CREATE TABLE resultados_aprendizajes_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    codigo TEXT NOT NULL,
                    nombre TEXT NOT NULL,
                    duracion REAL NOT NULL,
                    fecha_inicio DATE,
                    fecha_fin DATE,
                    user_create_id INTEGER,
                    user_edit_id INTEGER,
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP,
                    status INTEGER DEFAULT 1,
                    FOREIGN KEY (user_create_id) REFERENCES users(id),
                    FOREIGN KEY (user_edit_id) REFERENCES users(id)
                )
            ');

            DB::statement('
                INSERT INTO resultados_aprendizajes_new 
                SELECT * FROM resultados_aprendizajes
            ');

            DB::statement('DROP TABLE resultados_aprendizajes');
            DB::statement('ALTER TABLE resultados_aprendizajes_new RENAME TO resultados_aprendizajes');
            
            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            Schema::table('resultados_aprendizajes', function (Blueprint $table) {
                $table->date('fecha_inicio')->nullable()->change();
                $table->date('fecha_fin')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // Para SQLite, revertir es complejo, simplemente no hacemos nada
            // ya que la estructura original ya tenía fecha_inicio y fecha_fin como NOT NULL
            return;
        }

        Schema::table('resultados_aprendizajes', function (Blueprint $table) {
            $table->date('fecha_inicio')->nullable(false)->change();
            $table->date('fecha_fin')->nullable(false)->change();
        });
    }
};
