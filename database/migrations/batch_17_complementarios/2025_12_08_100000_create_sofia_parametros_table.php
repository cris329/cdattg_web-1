<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Tema;
use App\Models\Parametro;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear temas y parámetros para Sofía
        $this->createSofiaParametros();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar relaciones de parametros_temas
        DB::table('parametros_temas')
            ->whereIn('tema_id', function($query) {
                $query->select('id')
                    ->from('temas')
                    ->whereIn('name', [
                        'ESTADOS SOFIA',
                        'ACCIONES SOFIA',
                        'RESULTADOS VALIDACION SOFIA',
                        'ESTADOS PROGRESO SOFIA'
                    ]);
            })
            ->delete();

        // Eliminar parámetros
        Parametro::whereIn('name', [
            'NO REGISTRADO',
            'REGISTRADO',
            'REQUIERE CAMBIO',
            'VALIDAR',
            'EXITOSO',
            'ERROR',
            'ADVERTENCIA',
            'PENDING',
            'PROCESSING',
            'COMPLETED',
            'FAILED'
        ])->delete();

        // Eliminar temas
        Tema::whereIn('name', [
            'ESTADOS SOFIA',
            'ACCIONES SOFIA',
            'RESULTADOS VALIDACION SOFIA',
            'ESTADOS PROGRESO SOFIA'
        ])->delete();
    }

    private function createSofiaParametros(): void
    {
        // Tema: ESTADOS SOFIA
        $temaEstados = Tema::updateOrCreate(
            ['name' => 'ESTADOS SOFIA'],
            [
                'status' => 1,
                'user_create_id' => null,
                'user_edit_id' => null,
            ]
        );

        $parametrosEstados = [
            'NO REGISTRADO',
            'REGISTRADO',
            'REQUIERE CAMBIO'
        ];

        $parametrosIds = [];
        foreach ($parametrosEstados as $nombre) {
            $parametro = Parametro::updateOrCreate(
                ['name' => $nombre],
                [
                    'status' => 1,
                    'user_create_id' => null,
                    'user_edit_id' => null,
                ]
            );
            $parametrosIds[$parametro->id] = ['status' => 1];
        }
        $temaEstados->parametros()->sync($parametrosIds);

        // Tema: ACCIONES SOFIA
        $temaAcciones = Tema::updateOrCreate(
            ['name' => 'ACCIONES SOFIA'],
            [
                'status' => 1,
                'user_create_id' => null,
                'user_edit_id' => null,
            ]
        );

        $parametroAccion = Parametro::updateOrCreate(
            ['name' => 'VALIDAR'],
            [
                'status' => 1,
                'user_create_id' => null,
                'user_edit_id' => null,
            ]
        );
        $temaAcciones->parametros()->sync([$parametroAccion->id => ['status' => 1]]);

        // Tema: RESULTADOS VALIDACION SOFIA
        $temaResultados = Tema::updateOrCreate(
            ['name' => 'RESULTADOS VALIDACION SOFIA'],
            [
                'status' => 1,
                'user_create_id' => null,
                'user_edit_id' => null,
            ]
        );

        $parametrosResultados = [
            'EXITOSO',
            'ERROR',
            'ADVERTENCIA'
        ];

        $parametrosIds = [];
        foreach ($parametrosResultados as $nombre) {
            $parametro = Parametro::updateOrCreate(
                ['name' => $nombre],
                [
                    'status' => 1,
                    'user_create_id' => null,
                    'user_edit_id' => null,
                ]
            );
            $parametrosIds[$parametro->id] = ['status' => 1];
        }
        $temaResultados->parametros()->sync($parametrosIds);

        // Tema: ESTADOS PROGRESO SOFIA
        $temaProgreso = Tema::updateOrCreate(
            ['name' => 'ESTADOS PROGRESO SOFIA'],
            [
                'status' => 1,
                'user_create_id' => null,
                'user_edit_id' => null,
            ]
        );

        $parametrosProgreso = [
            'PENDING',
            'PROCESSING',
            'COMPLETED',
            'FAILED'
        ];

        $parametrosIds = [];
        foreach ($parametrosProgreso as $nombre) {
            $parametro = Parametro::updateOrCreate(
                ['name' => $nombre],
                [
                    'status' => 1,
                    'user_create_id' => null,
                    'user_edit_id' => null,
                ]
            );
            $parametrosIds[$parametro->id] = ['status' => 1];
        }
        $temaProgreso->parametros()->sync($parametrosIds);
    }
};

