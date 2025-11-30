<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Complementarios\AspiranteComplementarioController;

// Mantener compatibilidad con rutas existentes - funcionalidad refactorizada
Route::get('/gestion-aspirantes', [AspiranteComplementarioController::class, 'gestionAspirantes'])
    ->name('gestion-aspirantes')
    ->middleware('auth');

// Esta ruta debe estar antes de la ruta genérica {programa} en web.php
// Usar where para restringir el parámetro a strings que no sean numéricos puros
Route::get('/programas-complementarios/{curso}', [AspiranteComplementarioController::class, 'verAspirantes'])
    ->name('programas-complementarios.ver-aspirantes')
    ->where('curso', '[^0-9]+.*') // Acepta cualquier string que no sea solo números
    ->middleware('auth');

Route::post(
    '/programas-complementarios/{complementarioId}/agregar-aspirante',
    [AspiranteComplementarioController::class, 'agregarAspirante']
)
    ->name('programas-complementarios.agregar-aspirante')
    ->middleware('auth');

Route::delete(
    '/programas-complementarios/{complementarioId}/aspirante/{aspiranteId}',
    [AspiranteComplementarioController::class, 'eliminarAspirante']
)
    ->name('programas-complementarios.eliminar-aspirante')
    ->middleware('auth');

Route::get(
    '/programas-complementarios/{complementarioId}/exportar-excel',
    [AspiranteComplementarioController::class, 'exportarAspirantesExcel']
)
    ->name('programas-complementarios.exportar-excel')
    ->middleware('auth');

Route::get(
    '/programas-complementarios/{complementarioId}/descargar-cedulas',
    [AspiranteComplementarioController::class, 'descargarCedulas']
)
    ->name('programas-complementarios.descargar-cedulas')
    ->middleware('auth');

Route::post(
    '/programas-complementarios/{complementarioId}/validar-documentos',
    [AspiranteComplementarioController::class, 'validarDocumentos']
)
    ->name('programas-complementarios.validar-documentos')
    ->middleware('auth');

