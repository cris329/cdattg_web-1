<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuiaAprendizajeController;

/*
|--------------------------------------------------------------------------
| Guías de Aprendizaje Routes
|--------------------------------------------------------------------------
|
| Aquí se registran las rutas para la gestión de guías de aprendizaje.
| Todas las rutas están protegidas por middleware de autenticación y permisos.
|
*/

// Rutas principales con nueva arquitectura Livewire
Route::get('/guias-aprendizaje', function () {
    return view('guias_aprendizaje.index');
})->name('guias-aprendizaje.index')->middleware('can:VER GUIA APRENDIZAJE');

// Rutas para crear y editar (nueva arquitectura)
Route::get('/guias-aprendizaje/create', function () {
    return view('guias_aprendizaje.create_new');
})->name('guias-aprendizaje.create')->middleware('can:CREAR GUIA APRENDIZAJE');

Route::get('/guias-aprendizaje/{guiaAprendizaje}/edit', function ($guiaAprendizaje) {
    return view('guias_aprendizaje.edit_new', ['guiaAprendizaje' => $guiaAprendizaje]);
})->name('guias-aprendizaje.edit')->middleware('can:EDITAR GUIA APRENDIZAJE');

// Ruta para gestionar relaciones (nueva arquitectura)
Route::get('/guias-aprendizaje/{guiaAprendizaje}/gestionar-relaciones', function ($guiaAprendizaje) {
    return view('guias_aprendizaje.gestionar_relaciones', ['guiaAprendizaje' => $guiaAprendizaje]);
})->name('guias-aprendizaje.gestionarRelaciones')->middleware('can:EDITAR GUIA APRENDIZAJE');

// Rutas resource para CRUD completo (mantener compatibilidad)
Route::resource('guias-aprendizaje', GuiaAprendizajeController::class)
    ->parameters(['guias-aprendizaje' => 'guia_aprendizaje']);

// Búsqueda AJAX
Route::get('/guias-aprendizaje-search', [GuiaAprendizajeController::class, 'search'])->name('guias-aprendizaje.search');

// Rutas adicionales para funcionalidades específicas
Route::middleware('can:EDITAR GUIA APRENDIZAJE')->group(function () {
    // Cambiar estado de la guía de aprendizaje
    Route::put('/guias-aprendizaje/{guiaAprendizaje}/cambiar-estado', [GuiaAprendizajeController::class, 'cambiarEstado'])
         ->name('guias-aprendizaje.cambiarEstado');
});

Route::middleware('can:EDITAR GUIA APRENDIZAJE')->group(function () {
    // Gestionar resultados de aprendizaje asociados (legacy)
    Route::get('/guias-aprendizaje/{guiaAprendizaje}/gestionar-resultados', [GuiaAprendizajeController::class, 'gestionarResultados'])
         ->name('guias-aprendizaje.gestionarResultados');
    
    Route::post('/guias-aprendizaje/{guiaAprendizaje}/asociar-resultado', [GuiaAprendizajeController::class, 'asociarResultado'])
         ->name('guias-aprendizaje.asociarResultado');
    
    Route::delete('/guias-aprendizaje/{guiaAprendizaje}/desasociar-resultado/{resultado}', [GuiaAprendizajeController::class, 'desasociarResultado'])
         ->name('guias-aprendizaje.desasociarResultado');
});

Route::middleware('can:EDITAR GUIA APRENDIZAJE')->group(function () {
    // Gestionar evidencias/actividades asociadas
    Route::get('/guias-aprendizaje/{guiaAprendizaje}/gestionar-evidencias', [GuiaAprendizajeController::class, 'gestionarEvidencias'])
         ->name('guias-aprendizaje.gestionarEvidencias');
    
    Route::post('/guias-aprendizaje/{guiaAprendizaje}/asociar-evidencia', [GuiaAprendizajeController::class, 'asociarEvidencia'])
         ->name('guias-aprendizaje.asociarEvidencia');
    
    Route::delete('/guias-aprendizaje/{guiaAprendizaje}/desasociar-evidencia/{evidencia}', [GuiaAprendizajeController::class, 'desasociarEvidencia'])
         ->name('guias-aprendizaje.desasociarEvidencia');
});

// Rutas para API (si se necesita)
Route::middleware('can:VER GUIA APRENDIZAJE')->group(function () {
    // API endpoint para obtener guías de aprendizaje
    Route::get('/api/guias-aprendizaje', [GuiaAprendizajeController::class, 'apiIndex'])
         ->name('api.guias-aprendizaje.index');
    
    // API endpoint para obtener una guía específica
    Route::get('/api/guias-aprendizaje/{guiaAprendizaje}', [GuiaAprendizajeController::class, 'apiShow'])
         ->name('api.guias-aprendizaje.show');
});

// Rutas para reportes y estadísticas
Route::middleware('can:VER GUIA APRENDIZAJE')->group(function () {
    // Reporte de progreso de guías
    Route::get('/guias-aprendizaje/{guiaAprendizaje}/reporte-progreso', [GuiaAprendizajeController::class, 'reporteProgreso'])
         ->name('guias-aprendizaje.reporteProgreso');
    
    // Estadísticas generales
    Route::get('/guias-aprendizaje-estadisticas', [GuiaAprendizajeController::class, 'estadisticas'])
         ->name('guias-aprendizaje.estadisticas');
});

// Rutas para exportación
Route::middleware('can:VER GUIA APRENDIZAJE')->group(function () {
    // Exportar guía a PDF
    Route::get('/guias-aprendizaje/{guiaAprendizaje}/exportar-pdf', [GuiaAprendizajeController::class, 'exportarPdf'])
         ->name('guias-aprendizaje.exportarPdf');
    
    // Exportar lista a Excel
    Route::get('/guias-aprendizaje/exportar-excel', [GuiaAprendizajeController::class, 'exportarExcel'])
         ->name('guias-aprendizaje.exportarExcel');
});

// Rutas para duplicación y plantillas
Route::middleware('can:CREAR GUIA APRENDIZAJE')->group(function () {
    // Duplicar guía existente
    Route::post('/guias-aprendizaje/{guiaAprendizaje}/duplicar', [GuiaAprendizajeController::class, 'duplicar'])
         ->name('guias-aprendizaje.duplicar');
    
    // Crear guía desde plantilla
    Route::get('/guias-aprendizaje/crear-desde-plantilla', [GuiaAprendizajeController::class, 'crearDesdePlantilla'])
         ->name('guias-aprendizaje.crearDesdePlantilla');
    
    Route::post('/guias-aprendizaje/crear-desde-plantilla', [GuiaAprendizajeController::class, 'storeDesdePlantilla'])
         ->name('guias-aprendizaje.storeDesdePlantilla');
});
