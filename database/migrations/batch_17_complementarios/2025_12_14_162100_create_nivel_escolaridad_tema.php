<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nombre del tema
     */
    private const TEMA_NAME = 'NIVEL-ESCOLARIDAD';

    /**
     * Lista de parámetros para nivel de escolaridad
     */
    private const PARAMETROS = [
        'NOVENO GRADO APROBADO',
        'MEDIA / BACHILLER (10° – 11°)',
        'EDUCACIÓN SUPERIOR – TÉCNICO PROFESIONAL',
        'EDUCACIÓN SUPERIOR – TECNOLÓGICO',
        'EDUCACIÓN SUPERIOR – PROFESIONAL UNIVERSITARIO',
        'POSGRADO (ESPECIALIZACIÓN / MAESTRÍA / DOCTORADO)'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar si el tema ya existe
        $temaExists = DB::table('temas')
            ->where('name', self::TEMA_NAME)
            ->exists();

        if (!$temaExists) {
            // Crear nuevo tema
            $temaId = DB::table('temas')->insertGetId([
                'name' => self::TEMA_NAME,
                'status' => 1,
                'user_create_id' => null,
                'user_edit_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            // Obtener ID del tema existente
            $temaId = DB::table('temas')
                ->where('name', self::TEMA_NAME)
                ->value('id');
        }

        // Crear parámetros y asociarlos al tema
        foreach (self::PARAMETROS as $nombre) {
            // Verificar si el parámetro ya existe
            $parametroExists = DB::table('parametros')
                ->where('name', $nombre)
                ->exists();

            if (!$parametroExists) {
                // Crear nuevo parámetro
                $parametroId = DB::table('parametros')->insertGetId([
                    'name' => $nombre,
                    'status' => 1,
                    'user_create_id' => null,
                    'user_edit_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // Obtener ID del parámetro existente
                $parametroId = DB::table('parametros')
                    ->where('name', $nombre)
                    ->value('id');
            }

            // Verificar si la asociación ya existe
            $asociacionExists = DB::table('parametros_temas')
                ->where('tema_id', $temaId)
                ->where('parametro_id', $parametroId)
                ->exists();

            if (!$asociacionExists) {
                // Crear asociación en parametros_temas
                DB::table('parametros_temas')->insert([
                    'tema_id' => $temaId,
                    'parametro_id' => $parametroId,
                    'status' => 1,
                    'user_create_id' => null,
                    'user_edit_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Obtener ID del tema
        $temaId = DB::table('temas')
            ->where('name', self::TEMA_NAME)
            ->value('id');

        if ($temaId) {
            // Obtener IDs de los parámetros asociados
            $parametroIds = DB::table('parametros_temas')
                ->where('tema_id', $temaId)
                ->pluck('parametro_id')
                ->toArray();

            // Eliminar asociaciones en parametros_temas
            DB::table('parametros_temas')
                ->where('tema_id', $temaId)
                ->delete();

            // Eliminar parámetros (solo si no están asociados a otros temas)
            foreach ($parametroIds as $parametroId) {
                $otrosTemas = DB::table('parametros_temas')
                    ->where('parametro_id', $parametroId)
                    ->exists();

                if (!$otrosTemas) {
                    DB::table('parametros')
                        ->where('id', $parametroId)
                        ->delete();
                }
            }

            // Eliminar tema
            DB::table('temas')
                ->where('id', $temaId)
                ->delete();
        }
    }
};
