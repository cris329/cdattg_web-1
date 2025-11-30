<?php

use App\Http\Controllers\Complementarios\ProgramaComplementarioController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('complementarios-ofertados')
    ->name('complementarios-ofertados.')
    ->group(function () {
        $rutaPrograma = '/{programa}';

        Route::get('/', [ProgramaComplementarioController::class, 'index'])
            ->name('index');

        Route::get('/create', [ProgramaComplementarioController::class, 'create'])
            ->name('create');

        Route::get($rutaPrograma, [ProgramaComplementarioController::class, 'show'])
            ->name('show');

        Route::get($rutaPrograma . '/edit', [ProgramaComplementarioController::class, 'edit'])
            ->name('edit');

        Route::get($rutaPrograma . '/edit-api', [ProgramaComplementarioController::class, 'editApi'])
            ->name('edit-api');

        Route::post('/', [ProgramaComplementarioController::class, 'store'])
            ->name('store');

        Route::put($rutaPrograma, [ProgramaComplementarioController::class, 'update'])
            ->name('update');

        Route::delete($rutaPrograma, [ProgramaComplementarioController::class, 'destroy'])
            ->name('destroy');
    });

