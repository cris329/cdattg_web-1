<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Complementarios\EstadisticaComplementarioController;

Route::middleware('auth')
    ->prefix('complementarios')
    ->name('complementarios.')
    ->group(function () {
        Route::get('/estadisticas', [EstadisticaComplementarioController::class, 'estadisticas'])
            ->name('estadisticas');

        Route::get('/estadisticas/api', [EstadisticaComplementarioController::class, 'apiEstadisticas'])
            ->name('estadisticas.api');

        Route::get('/estadisticas/exportar-excel', [EstadisticaComplementarioController::class, 'exportarProgramasDemandaExcel'])
            ->name('estadisticas.exportar-excel');
    });

