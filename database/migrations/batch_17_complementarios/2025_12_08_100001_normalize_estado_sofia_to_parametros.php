<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Parametro;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obtener los IDs de los parámetros
        $noRegistrado = Parametro::where('name', 'NO REGISTRADO')->first();
        $registrado = Parametro::where('name', 'REGISTRADO')->first();
        $requiereCambio = Parametro::where('name', 'REQUIERE CAMBIO')->first();

        if (!$noRegistrado || !$registrado || !$requiereCambio) {
            throw new \Exception('Los parámetros de estados Sofía no existen. Ejecuta primero la migración de creación de parámetros.');
        }

        // Crear columna temporal para almacenar el parametro_id
        Schema::table('personas', function (Blueprint $table) {
            $table->unsignedBigInteger('estado_sofia_parametro_id')->nullable()->after('estado_sofia');
        });

        // Migrar datos: 0 -> NO REGISTRADO, 1 -> REGISTRADO, 2 -> REQUIERE CAMBIO
        DB::table('personas')
            ->where('estado_sofia', 0)
            ->update(['estado_sofia_parametro_id' => $noRegistrado->id]);

        DB::table('personas')
            ->where('estado_sofia', 1)
            ->update(['estado_sofia_parametro_id' => $registrado->id]);

        DB::table('personas')
            ->where('estado_sofia', 2)
            ->update(['estado_sofia_parametro_id' => $requiereCambio->id]);

        // Detectar el driver de base de datos
        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite: crear nueva columna con nombre correcto, copiar datos, eliminar antiguas
            Schema::table('personas', function (Blueprint $table) use ($noRegistrado) {
                $table->unsignedBigInteger('estado_sofia_new')->nullable()->default($noRegistrado->id)->after('status');
            });
            
            // Copiar datos
            DB::statement('UPDATE personas SET estado_sofia_new = estado_sofia_parametro_id WHERE estado_sofia_parametro_id IS NOT NULL');
            DB::statement("UPDATE personas SET estado_sofia_new = {$noRegistrado->id} WHERE estado_sofia_new IS NULL");
            
            // Eliminar columnas antiguas
            Schema::table('personas', function (Blueprint $table) {
                $table->dropColumn(['estado_sofia', 'estado_sofia_parametro_id']);
            });
            
            // Intentar renombrar usando RENAME COLUMN (SQLite 3.25.0+)
            try {
                DB::statement('ALTER TABLE personas RENAME COLUMN estado_sofia_new TO estado_sofia');
            } catch (\Exception $e) {
                // Si no soporta RENAME COLUMN, recrear la columna con el nombre correcto
                Schema::table('personas', function (Blueprint $table) use ($noRegistrado) {
                    $table->unsignedBigInteger('estado_sofia')->nullable()->default($noRegistrado->id)->after('status');
                });
                DB::statement('UPDATE personas SET estado_sofia = estado_sofia_new');
                Schema::table('personas', function (Blueprint $table) {
                    $table->dropColumn('estado_sofia_new');
                });
            }
        } else {
            // MySQL/MariaDB
            DB::statement('ALTER TABLE personas DROP COLUMN estado_sofia');
            DB::statement('ALTER TABLE personas CHANGE estado_sofia_parametro_id estado_sofia BIGINT UNSIGNED NULL');
            DB::statement("ALTER TABLE personas MODIFY COLUMN estado_sofia BIGINT UNSIGNED NULL DEFAULT {$noRegistrado->id}");
        }

        // Agregar la foreign key
        Schema::table('personas', function (Blueprint $table) {
            $table->foreign('estado_sofia')
                ->references('id')
                ->on('parametros')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Obtener los IDs de los parámetros
        $noRegistrado = Parametro::where('name', 'NO REGISTRADO')->first();
        $registrado = Parametro::where('name', 'REGISTRADO')->first();
        $requiereCambio = Parametro::where('name', 'REQUIERE CAMBIO')->first();

        if (!$noRegistrado || !$registrado || !$requiereCambio) {
            return;
        }

        // Eliminar foreign key
        Schema::table('personas', function (Blueprint $table) {
            $table->dropForeign(['estado_sofia']);
        });

        // Crear columna temporal tinyInteger
        Schema::table('personas', function (Blueprint $table) {
            $table->tinyInteger('estado_sofia_tmp')->default(0)->after('estado_sofia');
        });

        // Migrar datos de vuelta
        DB::table('personas')
            ->where('estado_sofia', $noRegistrado->id)
            ->update(['estado_sofia_tmp' => 0]);

        DB::table('personas')
            ->where('estado_sofia', $registrado->id)
            ->update(['estado_sofia_tmp' => 1]);

        DB::table('personas')
            ->where('estado_sofia', $requiereCambio->id)
            ->update(['estado_sofia_tmp' => 2]);

        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite: usar Schema para modificar
            Schema::table('personas', function (Blueprint $table) {
                $table->dropColumn('estado_sofia');
            });
            
            Schema::table('personas', function (Blueprint $table) {
                $table->dropColumn('estado_sofia_tmp');
            });
            
            Schema::table('personas', function (Blueprint $table) {
                $table->tinyInteger('estado_sofia')->default(0)->after('status');
            });
        } else {
            // MySQL/MariaDB
            DB::statement('ALTER TABLE personas DROP COLUMN estado_sofia');
            DB::statement('ALTER TABLE personas CHANGE estado_sofia_tmp estado_sofia TINYINT NOT NULL DEFAULT 0');
            DB::statement("ALTER TABLE personas MODIFY COLUMN estado_sofia TINYINT NOT NULL DEFAULT 0 COMMENT '0: No registrado, 1: Registrado, 2: Requiere cambio de cédula'");
        }
    }
};

