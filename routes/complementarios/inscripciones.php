<?php

use App\Http\Controllers\Complementarios\InscripcionComplementarioController;
use Illuminate\Support\Facades\Route;

if (!defined('ROUTE_PATTERN_NUMERIC')) {
    define('ROUTE_PATTERN_NUMERIC', '[0-9]+');
}

// Rutas para inscripciones de complementarios
Route::prefix('inscripciones')
    ->name('inscripciones.')
    ->group(function () {
        
        // Inscripción general (sin programa específico)
        Route::get('general', [InscripcionComplementarioController::class, 'inscripcionGeneral'])
            ->name('general');
        
        Route::post('general', [InscripcionComplementarioController::class, 'procesarInscripcionGeneral'])
            ->name('general.store');
        
        // Inscripción a programa específico
        Route::get('{programa}', [InscripcionComplementarioController::class, 'formularioInscripcion'])
            ->name('formulario')
            ->where('programa', ROUTE_PATTERN_NUMERIC);
        
        Route::post('{programa}', [InscripcionComplementarioController::class, 'procesarInscripcion'])
            ->name('procesar')
            ->where('programa', ROUTE_PATTERN_NUMERIC);
    });
