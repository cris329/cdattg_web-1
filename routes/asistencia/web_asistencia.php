<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AsistenciaAprendicesController;
use App\Http\Controllers\AsistenceQrController;
use App\Http\Controllers\AsistenciaConsultaController;

Route::middleware(['permission:VER ASISTENCIA'])->group(function () {
    Route::get('/asistencia/consulta', function () {
        return view('asistencias.consulta');
    })->name('asistencia.consulta');

    Route::get('/asistencia/consulta/{asistencia}', [AsistenciaConsultaController::class, 'show'])
        ->name('asistencia.consulta.show');

    Route::get('/asistencia/consulta/{asistencia}/pdf', [AsistenciaConsultaController::class, 'pdf'])
        ->name('asistencia.consulta.pdf');
});

Route::resource('asistencia', AsistenciaAprendicesController::class)
    ->except(['index'])
    ->middleware(['permission:VER PROGRAMA DE CARACTERIZACION|TOMAR ASISTENCIA']);

Route::middleware(['permission:VER PROGRAMA DE CARACTERIZACION|TOMAR ASISTENCIA'])->group(function () {
    Route::get('/asistencia/index', [AsistenciaAprendicesController::class, 'index'])->name('asistencia.index');
    Route::post('/asistencia/ficha', [AsistenciaAprendicesController::class, 'getAttendanceByFicha'])->name('asistencia.getAttendanceByFicha');
    Route::post('/asistencia/ficha/fecha', [AsistenciaAprendicesController::class, 'getAttendanceByDateAndFicha'])->name('asistencia.getAttendanceByDateAndFicha');
    Route::post('/asistencia/ficha/documentos', [AsistenciaAprendicesController::class, 'getDocumentsByFicha'])->name('asistencia.getDocumentsByFicha');
    Route::post('/asistencia/documento', [AsistenciaAprendicesController::class, 'getAttendanceByDocument'])->name('asistencia.getAttendanceByDocument');
});

// Toma de asistencia con QR web (solo usuarios con permiso TOMAR ASISTENCIA)
Route::middleware(['permission:TOMAR ASISTENCIA'])->group(function () {
    Route::get('asistence/web', [AsistenceQrController::class, 'index'])->name('asistence.web');
    Route::post('/asistence/store', [AsistenceQrController::class, 'store'])->name('asistence.store');
    Route::get('asistence/caracterSelected/{caracterizacion}', [AsistenceQrController::class, 'caracterSelected'])->name('asistence.caracterSelected');
    Route::get('asistence/caracterSelected/{caracterizacion}/{evidencia_id}', [AsistenceQrController::class, 'caracterSelected'])->name('asistence.caracterSelected.withEvidencia');
    Route::get('/asistence/web/list/{ficha}/{jornada}', [AsistenceQrController::class, 'getAsistenceWebList'])->name('asistence.weblist');
    Route::get('/asistence/exit/{identificacion}/{ingreso}/{fecha}', [AsistenceQrController::class, 'redirectAprenticeExit'])->name('asistence.webexit');
    Route::get('/asistence/entrance/{identificacion}/{ingreso}/{fecha}', [AsistenceQrController::class, 'redirectAprenticeEntrance'])->name('asistence.webentrance');
    Route::get('/asistence/exitFormation/{caracterizacion_id}', [AsistenceQrController::class, 'exitFormationAsistenceWeb'])->name('asistence.exitFormation');
    Route::post('/asistence/setNewExit', [AsistenceQrController::class, 'setNewExitAsistenceWeb'])->name('asistence.setNewExit');
    Route::post('/asistence/setNewEntrance', [AsistenceQrController::class, 'setNewEntranceAsistenceWeb'])->name('asistence.setNewEntrance');
    Route::post('/asistence/finalizar-asistencia', [AsistenceQrController::class, 'finalizar_asistencia'])->name('asistence.finalizarAsistencia');
    Route::post('/asistence/registrar-seleccionados', [AsistenceQrController::class, 'registrarAsistenciaSeleccionados'])->name('asistence.registrarSeleccionados');
    Route::post('/asistencia/set-session-alert', [AsistenceQrController::class, 'setSessionAlert'])->name('asistencia.setSessionAlert');
    Route::post('/asistence/agregar-actividad', [AsistenceQrController::class, 'agregar_actividad'])->name('asistence.agregarActividad');
    Route::put('/asistence/terminar-actividad', [AsistenceQrController::class, 'terminar_actividad'])->name('asistence.terminarActividad');
    
    // Rutas para gestión de evidencias (asistencia QR)
    Route::post('/evidencias/store-simple', [AsistenceQrController::class, 'storeEvidencia'])->name('evidencias.store.simple');
});
