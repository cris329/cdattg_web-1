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
        $validar = Parametro::where('name', 'VALIDAR')->first();
        $exitoso = Parametro::where('name', 'EXITOSO')->first();
        $error = Parametro::where('name', 'ERROR')->first();
        $advertencia = Parametro::where('name', 'ADVERTENCIA')->first();

        if (!$validar || !$exitoso || !$error || !$advertencia) {
            throw new \Exception('Los parámetros de validación Sofía no existen. Ejecuta primero la migración de creación de parámetros.');
        }

        // Crear columnas temporales
        Schema::table('senasofiaplus_validation_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('accion_parametro_id')->nullable()->after('accion');
            $table->unsignedBigInteger('resultado_parametro_id')->nullable()->after('resultado');
        });

        // Migrar datos de accion (solo 'validar')
        DB::table('senasofiaplus_validation_logs')
            ->where('accion', 'validar')
            ->update(['accion_parametro_id' => $validar->id]);

        // Migrar datos de resultado
        DB::table('senasofiaplus_validation_logs')
            ->where('resultado', 'exitoso')
            ->update(['resultado_parametro_id' => $exitoso->id]);

        DB::table('senasofiaplus_validation_logs')
            ->where('resultado', 'error')
            ->update(['resultado_parametro_id' => $error->id]);

        DB::table('senasofiaplus_validation_logs')
            ->where('resultado', 'advertencia')
            ->update(['resultado_parametro_id' => $advertencia->id]);

        // Eliminar índices que usan las columnas antiguas
        Schema::table('senasofiaplus_validation_logs', function (Blueprint $table) {
            $table->dropIndex(['accion', 'resultado']);
        });

        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite: crear nuevas columnas, copiar datos, eliminar antiguas
            Schema::table('senasofiaplus_validation_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('accion_new')->after('aspirante_id');
                $table->unsignedBigInteger('resultado_new')->after('accion_new');
            });
            
            // Copiar datos
            DB::statement('UPDATE senasofiaplus_validation_logs SET accion_new = accion_parametro_id, resultado_new = resultado_parametro_id');
            
            // Eliminar columnas antiguas
            Schema::table('senasofiaplus_validation_logs', function (Blueprint $table) {
                $table->dropColumn(['accion', 'resultado', 'accion_parametro_id', 'resultado_parametro_id']);
            });
            
            // Renombrar usando RENAME COLUMN si está disponible
            try {
                DB::statement('ALTER TABLE senasofiaplus_validation_logs RENAME COLUMN accion_new TO accion');
                DB::statement('ALTER TABLE senasofiaplus_validation_logs RENAME COLUMN resultado_new TO resultado');
            } catch (\Exception $e) {
                // Si no soporta, recrear con nombres correctos
                Schema::table('senasofiaplus_validation_logs', function (Blueprint $table) {
                    $table->unsignedBigInteger('accion')->after('aspirante_id');
                    $table->unsignedBigInteger('resultado')->after('accion');
                });
                DB::statement('UPDATE senasofiaplus_validation_logs SET accion = accion_new, resultado = resultado_new');
                Schema::table('senasofiaplus_validation_logs', function (Blueprint $table) {
                    $table->dropColumn(['accion_new', 'resultado_new']);
                });
            }
        } else {
            // MySQL/MariaDB
            DB::statement('ALTER TABLE senasofiaplus_validation_logs DROP COLUMN accion, DROP COLUMN resultado');
            DB::statement('ALTER TABLE senasofiaplus_validation_logs CHANGE accion_parametro_id accion BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE senasofiaplus_validation_logs CHANGE resultado_parametro_id resultado BIGINT UNSIGNED NOT NULL');
        }

        // Agregar foreign keys
        Schema::table('senasofiaplus_validation_logs', function (Blueprint $table) {
            $table->foreign('accion')
                ->references('id')
                ->on('parametros')
                ->onDelete('restrict');

            $table->foreign('resultado')
                ->references('id')
                ->on('parametros')
                ->onDelete('restrict');
        });

        // Recrear índice compuesto
        Schema::table('senasofiaplus_validation_logs', function (Blueprint $table) {
            $table->index(['accion', 'resultado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Obtener los IDs de los parámetros
        $validar = Parametro::where('name', 'VALIDAR')->first();
        $exitoso = Parametro::where('name', 'EXITOSO')->first();
        $error = Parametro::where('name', 'ERROR')->first();
        $advertencia = Parametro::where('name', 'ADVERTENCIA')->first();

        if (!$validar || !$exitoso || !$error || !$advertencia) {
            return;
        }

        // Eliminar índices
        Schema::table('senasofiaplus_validation_logs', function (Blueprint $table) {
            $table->dropIndex(['accion', 'resultado']);
        });

        // Eliminar foreign keys
        Schema::table('senasofiaplus_validation_logs', function (Blueprint $table) {
            $table->dropForeign(['accion']);
            $table->dropForeign(['resultado']);
        });

        // Crear columnas temporales enum
        Schema::table('senasofiaplus_validation_logs', function (Blueprint $table) {
            $table->enum('accion_tmp', ['validar'])->after('accion');
            $table->enum('resultado_tmp', ['exitoso', 'error', 'advertencia'])->after('resultado');
        });

        // Migrar datos de vuelta
        DB::table('senasofiaplus_validation_logs')
            ->where('accion', $validar->id)
            ->update(['accion_tmp' => 'validar']);

        DB::table('senasofiaplus_validation_logs')
            ->where('resultado', $exitoso->id)
            ->update(['resultado_tmp' => 'exitoso']);

        DB::table('senasofiaplus_validation_logs')
            ->where('resultado', $error->id)
            ->update(['resultado_tmp' => 'error']);

        DB::table('senasofiaplus_validation_logs')
            ->where('resultado', $advertencia->id)
            ->update(['resultado_tmp' => 'advertencia']);

        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite: usar Schema para modificar
            Schema::table('senasofiaplus_validation_logs', function (Blueprint $table) {
                $table->dropColumn(['accion', 'resultado']);
            });
            
            Schema::table('senasofiaplus_validation_logs', function (Blueprint $table) {
                $table->dropColumn(['accion_tmp', 'resultado_tmp']);
            });
            
            Schema::table('senasofiaplus_validation_logs', function (Blueprint $table) {
                $table->string('accion')->default('validar')->after('aspirante_id');
                $table->string('resultado')->after('accion');
            });
        } else {
            // MySQL/MariaDB
            DB::statement('ALTER TABLE senasofiaplus_validation_logs DROP COLUMN accion, DROP COLUMN resultado');
            DB::statement("ALTER TABLE senasofiaplus_validation_logs CHANGE accion_tmp accion ENUM('validar') NOT NULL");
            DB::statement("ALTER TABLE senasofiaplus_validation_logs CHANGE resultado_tmp resultado ENUM('exitoso', 'error', 'advertencia') NOT NULL");
        }

        // Recrear índice compuesto
        Schema::table('senasofiaplus_validation_logs', function (Blueprint $table) {
            $table->index(['accion', 'resultado']);
        });
    }
};

