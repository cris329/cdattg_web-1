<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Inventario\DashboardController;

// Grupo de rutas de inventario
Route::prefix('inventario')->group(function () {
    // Dashboard de inventario con Livewire
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('inventario.dashboard');
});
