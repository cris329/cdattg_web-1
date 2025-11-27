<?php

use App\Http\Controllers\Complementarios\InscripcionComplementarioController;
use Illuminate\Support\Facades\Route;

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
            ->where('programa', '[0-9]+');
        
        Route::post('{programa}', [InscripcionComplementarioController::class, 'procesarInscripcion'])
            ->name('procesar')
            ->where('programa', '[0-9]+');
    });
