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
        // 1. Crear tema para estados de programas complementarios
        $temaId = null;
        if (Schema::hasTable('temas')) {
            $temaId = DB::table('temas')->insertGetId([
                'name' => 'ESTADO_PROGRAMA_COMPLEMENTARIO',
                'status' => 1,
                'user_create_id' => null,
                'user_edit_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Crear parámetros para los estados
        $parametros = [];
        if (Schema::hasTable('parametros') && $temaId) {
            $estados = [
                ['name' => 'Sin Oferta', 'valor_legacy' => 0],
                ['name' => 'Con Oferta', 'valor_legacy' => 1],
                ['name' => 'Cupos Llenos', 'valor_legacy' => 2],
            ];

            foreach ($estados as $estado) {
                $parametroId = DB::table('parametros')->insertGetId([
                    'name' => $estado['name'],
                    'status' => 1,
                    'user_create_id' => null,
                    'user_edit_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $parametros[$estado['valor_legacy']] = $parametroId;

                // 3. Crear relación en parametros_temas
                if (Schema::hasTable('parametros_temas')) {
                    $parametroTemaId = DB::table('parametros_temas')->insertGetId([
                        'tema_id' => $temaId,
                        'parametro_id' => $parametroId,
                        'status' => 1,
                        'user_create_id' => null,
                        'user_edit_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Guardar el ID de parametros_temas para usar como FK
                    $parametros[$estado['valor_legacy']] = $parametroTemaId;
                }
            }
        }

        // 4. Modificar tabla complementarios_ofertados si existe
        if (Schema::hasTable('complementarios_ofertados')) {
            Schema::table('complementarios_ofertados', function (Blueprint $table) use ($parametros) {
                // Renombrar columna estado a estado_old para backup
                if (Schema::hasColumn('complementarios_ofertados', 'estado')) {
                    $table->renameColumn('estado', 'estado_old');
                }

                // Agregar nueva columna estado_id como FK
                if (!Schema::hasColumn('complementarios_ofertados', 'estado_id')) {
                    $table->foreignId('estado_id')
                        ->nullable()
                        ->after('cupos')
                        ->constrained('parametros_temas')
                        ->onDelete('set null');
                }
            });

            // 5. Migrar datos existentes si hay parámetros creados
            if (!empty($parametros) && Schema::hasColumn('complementarios_ofertados', 'estado_old')) {
                foreach ($parametros as $valorLegacy => $parametroTemaId) {
                    DB::table('complementarios_ofertados')
                        ->where('estado_old', $valorLegacy)
                        ->update(['estado_id' => $parametroTemaId]);
                }
            }

            // 6. Eliminar columna estado_old después de migración
            Schema::table('complementarios_ofertados', function (Blueprint $table) {
                if (Schema::hasColumn('complementarios_ofertados', 'estado_old')) {
                    $table->dropColumn('estado_old');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir cambios en orden inverso
        if (Schema::hasTable('complementarios_ofertados')) {
            // 1. Restaurar columna estado_old
            Schema::table('complementarios_ofertados', function (Blueprint $table) {
                if (!Schema::hasColumn('complementarios_ofertados', 'estado_old')) {
                    $table->tinyInteger('estado_old')->nullable()->after('cupos');
                }
            });

            // 2. Revertir datos (no podemos saber el mapeo inverso sin guardarlo, así que usamos valores por defecto)
            DB::table('complementarios_ofertados')->update(['estado_old' => 0]);

            // 3. Eliminar columna estado_id
            Schema::table('complementarios_ofertados', function (Blueprint $table) {
                if (Schema::hasColumn('complementarios_ofertados', 'estado_id')) {
                    $table->dropForeign(['estado_id']);
                    $table->dropColumn('estado_id');
                }
            });

            // 4. Renombrar estado_old a estado
            Schema::table('complementarios_ofertados', function (Blueprint $table) {
                if (Schema::hasColumn('complementarios_ofertados', 'estado_old')) {
                    $table->renameColumn('estado_old', 'estado');
                }
            });
        }

        // 5. Eliminar datos de parametros_temas, parametros y tema
        if (Schema::hasTable('parametros_temas')) {
            DB::table('parametros_temas')
                ->whereIn('parametro_id', function ($query) {
                    $query->select('id')
                        ->from('parametros')
                        ->whereIn('name', ['Sin Oferta', 'Con Oferta', 'Cupos Llenos']);
                })
                ->delete();
        }

        if (Schema::hasTable('parametros')) {
            DB::table('parametros')
                ->whereIn('name', ['Sin Oferta', 'Con Oferta', 'Cupos Llenos'])
                ->delete();
        }

        if (Schema::hasTable('temas')) {
            DB::table('temas')
                ->where('name', 'ESTADO_PROGRAMA_COMPLEMENTARIO')
                ->delete();
        }
    }
};
