<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Inventario\ProductoController;

// Rutas para productos del inventario
Route::prefix('inventario')
    ->name('inventario.')
    ->group(function () {
        
        Route::get('productos/catalogo', [ProductoController::class, 'catalogo'])
            ->name('productos.catalogo');

        Route::get('productos', [ProductoController::class, 'index'])
            ->name('productos.index');
        
        Route::get('productos/buscar', [ProductoController::class, 'buscar'])
            ->name('productos.buscar');
        
        Route::post('productos/agregar-carrito', [ProductoController::class, 'agregarAlCarrito'])
            ->name('productos.agregar-carrito');

        Route::get('productos/exportar-pdf', [ProductoController::class, 'exportarPdf'])
            ->name('productos.exportar-pdf');
        Route::get('productos/exportar-excel', [ProductoController::class, 'exportarExcel'])
            ->name('productos.exportar-excel');
        

        Route::resource('productos', ProductoController::class)->except(['index']);
        
        Route::get('productos/detalles/{id}', [ProductoController::class, 'detalles'])
            ->name('productos.detalles');
        
        Route::get('productos/buscar/{codigo}', [ProductoController::class, 'buscarPorCodigo'])
            ->name('productos.buscar-codigo');
        
        Route::get('productos/{id}/etiqueta', [ProductoController::class, 'etiqueta'])
            ->name('productos.etiqueta');
    });
