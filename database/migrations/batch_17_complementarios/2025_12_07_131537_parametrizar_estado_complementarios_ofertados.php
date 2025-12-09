<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ESTADO_SIN_OFERTA = 'Sin Oferta';
    private const ESTADO_CON_OFERTA = 'Con Oferta';
    private const ESTADO_CUPOS_LLENOS = 'Cupos Llenos';
    private const TEMA_ESTADO = 'ESTADO_PROGRAMA_COMPLEMENTARIO';
    
    private const ESTADOS = [
        ['name' => self::ESTADO_SIN_OFERTA, 'valor_legacy' => 0],
        ['name' => self::ESTADO_CON_OFERTA, 'valor_legacy' => 1],
        ['name' => self::ESTADO_CUPOS_LLENOS, 'valor_legacy' => 2],
    ];
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $temaId = $this->crearTemaEstados();
        
        if (!$temaId) {
            return; // No se pudo crear el tema, salir temprano
        }
        
        $parametros = $this->crearParametrosYRelaciones($temaId);
        
        if (empty($parametros)) {
            return; // No se pudieron crear parámetros, salir temprano
        }
        
        $this->modificarTablaComplementarios($parametros);
    }

    /**
     * Crea el tema para estados de programas complementarios
     */
    private function crearTemaEstados(): ?int
    {
        if (!Schema::hasTable('temas')) {
            return null;
        }

        return DB::table('temas')->insertGetId([
            'name' => self::TEMA_ESTADO,
            'status' => 1,
            'user_create_id' => null,
            'user_edit_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Crea parámetros y sus relaciones en parametros_temas
     */
    private function crearParametrosYRelaciones(int $temaId): array
    {
        $parametros = [];
        
        if (!Schema::hasTable('parametros') || !Schema::hasTable('parametros_temas')) {
            return $parametros;
        }

        foreach (self::ESTADOS as $estado) {
            $parametroId = DB::table('parametros')->insertGetId([
                'name' => $estado['name'],
                'status' => 1,
                'user_create_id' => null,
                'user_edit_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

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

        return $parametros;
    }

    /**
     * Modifica la tabla complementarios_ofertados
     */
    private function modificarTablaComplementarios(array $parametros): void
    {
        if (!Schema::hasTable('complementarios_ofertados')) {
            return;
        }

        // Renombrar columna estado a estado_old para backup
        Schema::table('complementarios_ofertados', function (Blueprint $table) {
            if (Schema::hasColumn('complementarios_ofertados', 'estado')) {
                $table->renameColumn('estado', 'estado_old');
            }
        });

        // Agregar nueva columna estado_id como FK
        Schema::table('complementarios_ofertados', function (Blueprint $table) {
            if (!Schema::hasColumn('complementarios_ofertados', 'estado_id')) {
                $table->foreignId('estado_id')
                    ->nullable()
                    ->after('cupos')
                    ->constrained('parametros_temas')
                    ->onDelete('set null');
            }
        });

        // Migrar datos existentes si hay parámetros creados
        if (!empty($parametros) && Schema::hasColumn('complementarios_ofertados', 'estado_old')) {
            foreach ($parametros as $valorLegacy => $parametroTemaId) {
                DB::table('complementarios_ofertados')
                    ->where('estado_old', $valorLegacy)
                    ->update(['estado_id' => $parametroTemaId]);
            }
        }

        // Eliminar columna estado_old después de migración
        Schema::table('complementarios_ofertados', function (Blueprint $table) {
            if (Schema::hasColumn('complementarios_ofertados', 'estado_old')) {
                $table->dropColumn('estado_old');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->revertirCambiosTabla();
        $this->eliminarDatosParametrizados();
    }

    /**
     * Revierte los cambios en la tabla complementarios_ofertados
     */
    private function revertirCambiosTabla(): void
    {
        if (!Schema::hasTable('complementarios_ofertados')) {
            return;
        }

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

    /**
     * Elimina los datos parametrizados creados
     */
    private function eliminarDatosParametrizados(): void
    {
        $estados = [self::ESTADO_SIN_OFERTA, self::ESTADO_CON_OFERTA, self::ESTADO_CUPOS_LLENOS];

        // Eliminar datos de parametros_temas
        if (Schema::hasTable('parametros_temas')) {
            DB::table('parametros_temas')
                ->whereIn('parametro_id', function ($query) use ($estados) {
                    $query->select('id')
                        ->from('parametros')
                        ->whereIn('name', $estados);
                })
                ->delete();
        }

        // Eliminar datos de parametros
        if (Schema::hasTable('parametros')) {
            DB::table('parametros')
                ->whereIn('name', $estados)
                ->delete();
        }

        // Eliminar datos de tema
        if (Schema::hasTable('temas')) {
            DB::table('temas')
                ->where('name', self::TEMA_ESTADO)
                ->delete();
        }
    }
};
