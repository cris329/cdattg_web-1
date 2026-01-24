<?php

use App\Http\Controllers\Complementarios\CatalogoComplementarioController;
use App\Http\Controllers\Complementarios\ProgramaComplementarioController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('complementarios-ofertados')
    ->name('complementarios-ofertados.')
    ->group(function (): void {
        // Resource routes para CRUD estándar
        Route::resource('', ProgramaComplementarioController::class)
            ->parameters(['' => 'programa'])
            ->names([
                'index' => 'index',
                'create' => 'create',
                'store' => 'store',
                'show' => 'show',
                'edit' => 'edit',
                'update' => 'update',
                'destroy' => 'destroy',
            ]);

        // Ruta adicional para API de edición (AJAX)
        Route::get('{programa}/edit-api', [ProgramaComplementarioController::class, 'editApi'])
            ->name('edit-api');

        // Importación del catálogo oficial de programas (CURSO ESPECIAL)
        Route::get('catalogo/importar', [CatalogoComplementarioController::class, 'create'])
            ->name('catalogo.import.create');

        Route::post('catalogo/importar', [CatalogoComplementarioController::class, 'store'])
            ->name('catalogo.import.store');
    });

