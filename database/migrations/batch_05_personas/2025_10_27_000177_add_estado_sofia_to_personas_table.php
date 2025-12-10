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
        // Obtener los parámetros (pueden no existir aún si se ejecuta antes del seeder)
        $noRegistrado = Parametro::find(277);
        $registrado = Parametro::find(278);
        $requiereCambio = Parametro::find(279);

        // Verificar si la columna ya existe (puede ser TINYINT de una ejecución anterior)
        if (Schema::hasColumn('personas', 'estado_sofia')) {
            // Si ya existe como TINYINT, convertirla a BIGINT y migrar datos si los parámetros existen
            $driver = DB::getDriverName();
            
            if ($driver === 'sqlite') {
                // SQLite: crear nueva columna, migrar datos, eliminar antigua
                Schema::table('personas', function (Blueprint $table) {
                    $table->unsignedBigInteger('estado_sofia_new')->nullable()->after('status');
                });
                
                // Migrar datos si los parámetros existen
                if ($noRegistrado && $registrado && $requiereCambio) {
                    DB::table('personas')
                        ->where('estado_sofia', 0)
                        ->update(['estado_sofia_new' => $noRegistrado->id]);
                    
                    DB::table('personas')
                        ->where('estado_sofia', 1)
                        ->update(['estado_sofia_new' => $registrado->id]);
                    
                    DB::table('personas')
                        ->where('estado_sofia', 2)
                        ->update(['estado_sofia_new' => $requiereCambio->id]);
                } else {
                    // Si no existen parámetros, copiar valores como están (se migrarán después)
                    DB::statement('UPDATE personas SET estado_sofia_new = estado_sofia WHERE estado_sofia IN (0, 1, 2)');
                }
                
                Schema::table('personas', function (Blueprint $table) {
                    $table->dropColumn('estado_sofia');
                });
                
                try {
                    DB::statement('ALTER TABLE personas RENAME COLUMN estado_sofia_new TO estado_sofia');
                } catch (\Exception $e) {
                    Schema::table('personas', function (Blueprint $table) {
                        $table->unsignedBigInteger('estado_sofia')->nullable()->after('status');
                    });
                    DB::statement('UPDATE personas SET estado_sofia = estado_sofia_new');
                    Schema::table('personas', function (Blueprint $table) {
                        $table->dropColumn('estado_sofia_new');
                    });
                }
            } else {
                // MySQL/MariaDB: cambiar tipo y migrar datos
                if ($noRegistrado && $registrado && $requiereCambio) {
                    // Crear columna temporal
                    Schema::table('personas', function (Blueprint $table) {
                        $table->unsignedBigInteger('estado_sofia_parametro_id')->nullable()->after('estado_sofia');
                    });
                    
                    // Migrar datos
                    DB::table('personas')
                        ->where('estado_sofia', 0)
                        ->update(['estado_sofia_parametro_id' => $noRegistrado->id]);
                    
                    DB::table('personas')
                        ->where('estado_sofia', 1)
                        ->update(['estado_sofia_parametro_id' => $registrado->id]);
                    
                    DB::table('personas')
                        ->where('estado_sofia', 2)
                        ->update(['estado_sofia_parametro_id' => $requiereCambio->id]);
                    
                    // Cambiar tipo y copiar datos
                    DB::statement('ALTER TABLE personas DROP COLUMN estado_sofia');
                    DB::statement('ALTER TABLE personas CHANGE estado_sofia_parametro_id estado_sofia BIGINT UNSIGNED NULL');
                    DB::statement("ALTER TABLE personas MODIFY COLUMN estado_sofia BIGINT UNSIGNED NULL DEFAULT {$noRegistrado->id}");
                } else {
                    // Si no existen parámetros, solo cambiar tipo (el default se establecerá después)
                    DB::statement('ALTER TABLE personas MODIFY COLUMN estado_sofia BIGINT UNSIGNED NULL');
                }
            }
        } else {
            // Si no existe, crearla como BIGINT desde el principio
            Schema::table('personas', function (Blueprint $table) use ($noRegistrado) {
                $default = $noRegistrado ? $noRegistrado->id : 277; // 277 = NO REGISTRADO según ParametroSeeder
                $table->unsignedBigInteger('estado_sofia')->nullable()->default($default)->after('status');
            });
        }

        // Intentar agregar foreign key (solo si los parámetros existen)
        if ($noRegistrado) {
            try {
                Schema::table('personas', function (Blueprint $table) {
                    $table->foreign('estado_sofia')
                        ->references('id')
                        ->on('parametros')
                        ->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Si falla, se agregará después cuando existan los parámetros
            }
        } else {
            // Si los parámetros no existen, establecer el default después cuando existan
            // Por ahora, el modelo Persona establecerá el valor por defecto al crear
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar foreign key si existe
        try {
            Schema::table('personas', function (Blueprint $table) {
                $table->dropForeign(['estado_sofia']);
            });
        } catch (\Exception $e) {
            // Si no existe, continuar
        }

        Schema::table('personas', function (Blueprint $table) {
            $table->dropColumn('estado_sofia');
        });
    }
};
