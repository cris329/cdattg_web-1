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
        // Obtener los IDs de los parámetros por ID (284, 285, 286, 287 según ParametroSeeder)
        $pending = Parametro::find(284);
        $processing = Parametro::find(285);
        $completed = Parametro::find(286);
        $failed = Parametro::find(287);

        // Si los parámetros no existen, solo cambiar la estructura sin migrar datos
        if (!$pending || !$processing || !$completed || !$failed) {
            // Solo cambiar el tipo de columna sin migrar datos
            if (!Schema::hasTable('sofia_validation_progress')) {
                return;
            }

            $driver = DB::getDriverName();
            
            if ($driver === 'sqlite') {
                // SQLite: crear nueva columna BIGINT, luego eliminar antigua
                if (Schema::hasColumn('sofia_validation_progress', 'status')) {
                    Schema::table('sofia_validation_progress', function (Blueprint $table) {
                        $table->unsignedBigInteger('status_new')->nullable()->after('user_id');
                    });
                    DB::statement('UPDATE sofia_validation_progress SET status_new = NULL');
                    Schema::table('sofia_validation_progress', function (Blueprint $table) {
                        $table->dropColumn('status');
                    });
                    try {
                        DB::statement('ALTER TABLE sofia_validation_progress RENAME COLUMN status_new TO status');
                    } catch (\Exception $e) {
                        Schema::table('sofia_validation_progress', function (Blueprint $table) {
                            $table->unsignedBigInteger('status')->nullable()->after('user_id');
                        });
                        Schema::table('sofia_validation_progress', function (Blueprint $table) {
                            $table->dropColumn('status_new');
                        });
                    }
                }
            } else {
                // MySQL/MariaDB: cambiar tipo directamente
                if (Schema::hasColumn('sofia_validation_progress', 'status')) {
                    DB::statement('ALTER TABLE sofia_validation_progress MODIFY COLUMN status BIGINT UNSIGNED NULL');
                }
            }
            
            // Intentar agregar foreign key (puede fallar si los parámetros no existen)
            try {
                if (Schema::hasColumn('sofia_validation_progress', 'status')) {
                    Schema::table('sofia_validation_progress', function (Blueprint $table) {
                        $table->foreign('status')
                            ->references('id')
                            ->on('parametros')
                            ->onDelete('restrict');
                    });
                }
            } catch (\Exception $e) {
                // Si falla, se agregará después cuando existan los parámetros
            }
            
            return;
        }

        // Crear columna temporal
        Schema::table('sofia_validation_progress', function (Blueprint $table) {
            $table->unsignedBigInteger('status_parametro_id')->nullable()->after('status');
        });

        // Migrar datos
        DB::table('sofia_validation_progress')
            ->where('status', 'pending')
            ->update(['status_parametro_id' => $pending->id]);

        DB::table('sofia_validation_progress')
            ->where('status', 'processing')
            ->update(['status_parametro_id' => $processing->id]);

        DB::table('sofia_validation_progress')
            ->where('status', 'completed')
            ->update(['status_parametro_id' => $completed->id]);

        DB::table('sofia_validation_progress')
            ->where('status', 'failed')
            ->update(['status_parametro_id' => $failed->id]);

        // Para registros con status null o desconocido, usar pending por defecto
        DB::table('sofia_validation_progress')
            ->whereNull('status_parametro_id')
            ->update(['status_parametro_id' => $pending->id]);

        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite: crear nueva columna, copiar datos, eliminar antigua
            Schema::table('sofia_validation_progress', function (Blueprint $table) use ($pending) {
                $table->unsignedBigInteger('status_new')->default($pending->id)->after('user_id');
            });
            
            // Copiar datos
            DB::statement('UPDATE sofia_validation_progress SET status_new = status_parametro_id WHERE status_parametro_id IS NOT NULL');
            DB::statement("UPDATE sofia_validation_progress SET status_new = {$pending->id} WHERE status_new IS NULL");
            
            // Eliminar columnas antiguas
            Schema::table('sofia_validation_progress', function (Blueprint $table) {
                $table->dropColumn(['status', 'status_parametro_id']);
            });
            
            // Renombrar usando RENAME COLUMN si está disponible
            try {
                DB::statement('ALTER TABLE sofia_validation_progress RENAME COLUMN status_new TO status');
            } catch (\Exception $e) {
                // Si no soporta, recrear con nombre correcto
                Schema::table('sofia_validation_progress', function (Blueprint $table) use ($pending) {
                    $table->unsignedBigInteger('status')->default($pending->id)->after('user_id');
                });
                DB::statement('UPDATE sofia_validation_progress SET status = status_new');
                Schema::table('sofia_validation_progress', function (Blueprint $table) {
                    $table->dropColumn('status_new');
                });
            }
        } else {
            // MySQL/MariaDB
            DB::statement('ALTER TABLE sofia_validation_progress DROP COLUMN status');
            DB::statement("ALTER TABLE sofia_validation_progress CHANGE status_parametro_id status BIGINT UNSIGNED NOT NULL DEFAULT {$pending->id}");
        }

        // Agregar foreign key
        Schema::table('sofia_validation_progress', function (Blueprint $table) {
            $table->foreign('status')
                ->references('id')
                ->on('parametros')
                ->onDelete('restrict');
        });

        // Agregar índice en status
        Schema::table('sofia_validation_progress', function (Blueprint $table) {
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Obtener los IDs de los parámetros por ID (284, 285, 286, 287 según ParametroSeeder)
        $pending = Parametro::find(284);
        $processing = Parametro::find(285);
        $completed = Parametro::find(286);
        $failed = Parametro::find(287);

        if (!$pending || !$processing || !$completed || !$failed) {
            return;
        }

        // Eliminar índice
        Schema::table('sofia_validation_progress', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        // Eliminar foreign key
        Schema::table('sofia_validation_progress', function (Blueprint $table) {
            $table->dropForeign(['status']);
        });

        // Crear columna temporal string
        Schema::table('sofia_validation_progress', function (Blueprint $table) {
            $table->string('status_tmp')->default('pending')->after('status');
        });

        // Migrar datos de vuelta
        DB::table('sofia_validation_progress')
            ->where('status', $pending->id)
            ->update(['status_tmp' => 'pending']);

        DB::table('sofia_validation_progress')
            ->where('status', $processing->id)
            ->update(['status_tmp' => 'processing']);

        DB::table('sofia_validation_progress')
            ->where('status', $completed->id)
            ->update(['status_tmp' => 'completed']);

        DB::table('sofia_validation_progress')
            ->where('status', $failed->id)
            ->update(['status_tmp' => 'failed']);

        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite: usar Schema para modificar
            Schema::table('sofia_validation_progress', function (Blueprint $table) {
                $table->dropColumn('status');
            });
            
            Schema::table('sofia_validation_progress', function (Blueprint $table) {
                $table->dropColumn('status_tmp');
            });
            
            Schema::table('sofia_validation_progress', function (Blueprint $table) {
                $table->string('status')->default('pending')->after('user_id');
            });
        } else {
            // MySQL/MariaDB
            DB::statement('ALTER TABLE sofia_validation_progress DROP COLUMN status');
            DB::statement("ALTER TABLE sofia_validation_progress CHANGE status_tmp status VARCHAR(255) NOT NULL DEFAULT 'pending'");
        }
    }
};

