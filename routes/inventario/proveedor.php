<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Inventario\ProveedorController;

// Rutas para proveedores del inventario
Route::prefix('inventario')
    ->name('inventario.')
    ->group(function () {
        // Rutas para obtener ubicaciones (DEBEN IR ANTES que resource)
        Route::get('proveedores/departamentos/{paisId}', [ProveedorController::class, 'getDepartamentosPorPais'])
            ->name('proveedores.departamentos');
        Route::get('proveedores/municipios/{departamentoId}', [ProveedorController::class, 'getMunicipiosPorDepartamento'])
            ->name('proveedores.municipios');

        // Rutas completas para proveedores con vistas CRUD
        Route::resource('proveedores', ProveedorController::class)->except(['catalogo'])->parameters([
            'proveedores' => 'proveedor'
        ]);
    });
