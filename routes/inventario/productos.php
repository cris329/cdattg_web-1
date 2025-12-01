<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Inventario\ProductoController;

// Rutas para productos del inventario
Route::prefix('inventario')
    ->name('inventario.')
    ->group(function () {
        // Rutas específicas ANTES del resource (sin parámetros dinámicos que puedan entrar en conflicto)
        Route::get('productos/catalogo', [ProductoController::class, 'catalogo'])
            ->name('productos.catalogo');
        
        Route::get('productos/buscar', [ProductoController::class, 'buscar'])
            ->name('productos.buscar');
        
        // Rutas AJAX para funcionalidades e-commerce
        Route::post('productos/agregar-carrito', [ProductoController::class, 'agregarAlCarrito'])
            ->name('productos.agregar-carrito');
        
        // Rutas administrativas - resource (debe ir antes de rutas con parámetros dinámicos)
        Route::resource('productos', ProductoController::class)->names([
            'index' => 'productos.index',
            'create' => 'productos.create',
            'store' => 'productos.store',
            'show' => 'productos.show',
            'edit' => 'productos.edit',
            'update' => 'productos.update',
            'destroy' => 'productos.destroy',
        ]);
        
        // Rutas con parámetros dinámicos DESPUÉS del resource
        Route::get('productos/detalles/{id}', [ProductoController::class, 'detalles'])
            ->name('productos.detalles');
        
        Route::get('productos/buscar/{codigo}', [ProductoController::class, 'buscarPorCodigo'])
            ->name('productos.buscar-codigo');
        
        Route::get('productos/{id}/etiqueta', [ProductoController::class, 'etiqueta'])
            ->name('productos.etiqueta');
    });
