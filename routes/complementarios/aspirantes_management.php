<?php

use App\Http\Controllers\Complementarios\AspiranteComplementarioController;
use Illuminate\Support\Facades\Route;

// Rutas para gestión de aspirantes (requieren autenticación)
Route::middleware('auth')
    ->prefix('aspirantes')
    ->name('aspirantes.')
    ->group(function () {
        
        // Vista principal de gestión
        Route::get('gestion', [AspiranteComplementarioController::class, 'gestionAspirantes'])
            ->name('gestion');
        
        // Ver aspirantes de un programa específico
        Route::get('programa/{curso}', [AspiranteComplementarioController::class, 'verAspirantes'])
            ->name('ver-por-programa');
        
        // Gestión de aspirantes individuales
        Route::post('programa/{complementarioId}/agregar', [AspiranteComplementarioController::class, 'agregarAspirante'])
            ->name('agregar')
            ->where('complementarioId', '[0-9]+');
        
        Route::delete('programa/{complementarioId}/aspirante/{aspiranteId}', [AspiranteComplementarioController::class, 'eliminarAspirante'])
            ->name('eliminar')
            ->where(['complementarioId' => '[0-9]+', 'aspiranteId' => '[0-9]+']);
        
        // Exportaciones
        Route::get('programa/{complementarioId}/exportar-excel', [AspiranteComplementarioController::class, 'exportarAspirantesExcel'])
            ->name('exportar-excel')
            ->where('complementarioId', '[0-9]+');
        
        Route::get('programa/{complementarioId}/descargar-cedulas', [AspiranteComplementarioController::class, 'descargarCedulas'])
            ->name('descargar-cedulas')
            ->where('complementarioId', '[0-9]+');
        
        // Validaciones
        Route::post('programa/{complementarioId}/validar-documentos', [AspiranteComplementarioController::class, 'validarDocumentos'])
            ->name('validar-documentos')
            ->where('complementarioId', '[0-9]+');
    });
