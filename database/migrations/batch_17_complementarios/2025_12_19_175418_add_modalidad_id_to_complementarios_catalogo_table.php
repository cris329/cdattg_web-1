<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mapeo de valores de modalidad string a parametro_id
     */
    private const MAPEO_MODALIDAD = [
        'PRESENCIAL' => 18,
        'VIRTUAL' => 19,
        'MIXTA' => 20,
        'Presencial' => 18,
        'Virtual' => 19,
        'Mixta' => 20,
        'presencial' => 18,
        'virtual' => 19,
        'mixta' => 20,
    ];

    /**
     * Tema ID para modalidades de formación
     */
    private const TEMA_MODALIDADES = 5;

    /**
     * Run the migrations.
     * 
     * Agrega modalidad_id a complementarios_catalogo y migra datos del campo string modalidad.
     * Luego elimina el campo string y su índice.
     */
    public function up(): void
    {
        if (!Schema::hasTable('complementarios_catalogo')) {
            return;
        }

        // 1. Agregar columna modalidad_id
        Schema::table('complementarios_catalogo', function (Blueprint $table) {
            if (!Schema::hasColumn('complementarios_catalogo', 'modalidad_id')) {
                $table->foreignId('modalidad_id')
                    ->nullable()
                    ->after('red_conocimiento')
                    ->constrained('parametros_temas')
                    ->onDelete('set null');
            }
        });

        // 2. Migrar datos del string modalidad a modalidad_id
        $this->migrarDatosModalidad();

        // 3. Eliminar campo string modalidad y su índice
        Schema::table('complementarios_catalogo', function (Blueprint $table) {
            if (Schema::hasColumn('complementarios_catalogo', 'modalidad')) {
                // Eliminar índice primero si existe
                $table->dropIndex(['modalidad']);
                // Eliminar columna
                $table->dropColumn('modalidad');
            }
        });
    }

    /**
     * Migra los datos del campo string modalidad a modalidad_id
     */
    private function migrarDatosModalidad(): void
    {
        if (!Schema::hasTable('parametros_temas') || !Schema::hasColumn('complementarios_catalogo', 'modalidad')) {
            return;
        }

        // Obtener todos los catálogos con modalidad
        $catalogos = DB::table('complementarios_catalogo')
            ->whereNotNull('modalidad')
            ->where('modalidad', '!=', '')
            ->get(['id', 'modalidad']);

        foreach ($catalogos as $catalogo) {
            $modalidadString = trim($catalogo->modalidad);
            
            // Buscar el parametro_id correspondiente
            $parametroId = self::MAPEO_MODALIDAD[$modalidadString] ?? null;
            
            if ($parametroId) {
                // Buscar el ParametroTema correspondiente
                $parametroTema = DB::table('parametros_temas')
                    ->where('tema_id', self::TEMA_MODALIDADES)
                    ->where('parametro_id', $parametroId)
                    ->first();
                
                if ($parametroTema) {
                    // Actualizar el catálogo con el modalidad_id
                    DB::table('complementarios_catalogo')
                        ->where('id', $catalogo->id)
                        ->update(['modalidad_id' => $parametroTema->id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('complementarios_catalogo')) {
            return;
        }

        // 1. Restaurar campo string modalidad
        Schema::table('complementarios_catalogo', function (Blueprint $table) {
            if (!Schema::hasColumn('complementarios_catalogo', 'modalidad')) {
                $table->string('modalidad', 100)->nullable()->after('red_conocimiento');
                $table->index('modalidad');
            }
        });

        // 2. Migrar datos de modalidad_id a modalidad (string)
        $this->revertirDatosModalidad();

        // 3. Eliminar columna modalidad_id
        Schema::table('complementarios_catalogo', function (Blueprint $table) {
            if (Schema::hasColumn('complementarios_catalogo', 'modalidad_id')) {
                $table->dropForeign(['modalidad_id']);
                $table->dropColumn('modalidad_id');
            }
        });
    }

    /**
     * Revierte los datos de modalidad_id a modalidad (string)
     */
    private function revertirDatosModalidad(): void
    {
        if (!Schema::hasTable('parametros_temas') || !Schema::hasColumn('complementarios_catalogo', 'modalidad_id')) {
            return;
        }

        // Mapeo inverso: parametro_id -> string
        $mapeoInverso = [
            18 => 'Presencial',
            19 => 'Virtual',
            20 => 'Mixta',
        ];

        $catalogos = DB::table('complementarios_catalogo')
            ->whereNotNull('modalidad_id')
            ->get(['id', 'modalidad_id']);

        foreach ($catalogos as $catalogo) {
            $parametroTema = DB::table('parametros_temas')
                ->where('id', $catalogo->modalidad_id)
                ->first();
            
            if ($parametroTema && isset($mapeoInverso[$parametroTema->parametro_id])) {
                DB::table('complementarios_catalogo')
                    ->where('id', $catalogo->id)
                    ->update(['modalidad' => $mapeoInverso[$parametroTema->parametro_id]]);
            }
        }
    }
};
