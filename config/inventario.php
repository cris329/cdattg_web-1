<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Stock
    |--------------------------------------------------------------------------
    */
    'stock' => [
        'umbral_minimo' => env('INVENTARIO_STOCK_MINIMO', 10),
        'umbral_critico' => env('INVENTARIO_STOCK_CRITICO', 5),
        'notificar_stock_bajo' => env('INVENTARIO_NOTIFICAR_STOCK_BAJO', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Imágenes
    |--------------------------------------------------------------------------
    */
    'imagenes' => [
        'disco' => env('INVENTARIO_IMAGENES_DISCO', 'public'),
        'directorio' => env('INVENTARIO_IMAGENES_DIR', 'imagenes_productos'),
        'tamaño_maximo_kb' => env('INVENTARIO_IMAGEN_MAX_KB', 2048),
        'formatos_permitidos' => ['jpg', 'jpeg', 'png', 'webp'],
        'calidad_compresion' => env('INVENTARIO_IMAGEN_CALIDAD', 85),
        'default' => env('INVENTARIO_IMAGEN_DEFAULT', 'img/inventario/producto-default.png'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Códigos de Barras
    |--------------------------------------------------------------------------
    */
    'codigo_barras' => [
        'formato' => env('INVENTARIO_BARCODE_FORMATO', 'code128'),
        'ancho' => env('INVENTARIO_BARCODE_ANCHO', 2),
        'alto' => env('INVENTARIO_BARCODE_ALTO', 100),
        'prefijo_auto' => env('INVENTARIO_BARCODE_PREFIJO', 'SENA'),
        'longitud_auto' => env('INVENTARIO_BARCODE_LONGITUD', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Órdenes
    |--------------------------------------------------------------------------
    */
    'ordenes' => [
        'dias_devolucion_max' => env('INVENTARIO_DEVOLUCION_DIAS_MAX', 30),
        'notificar_nuevas' => env('INVENTARIO_NOTIFICAR_ORDENES', true),
        'roles_aprobadores' => ['Administrador', 'Coordinador de Inventario'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Temas y Parámetros
    |--------------------------------------------------------------------------
    */
    'temas' => [
        'estados_producto' => 'ESTADOS DE PRODUCTO',
        'estados_orden' => 'ESTADOS DE ORDEN',
        'tipos_producto' => 'TIPOS DE PRODUCTO',
        'tipos_orden' => 'TIPOS DE ORDEN',
        'unidades_medida' => 'UNIDADES DE MEDIDA',
        'categorias' => 'CATEGORIAS',
        'marcas' => 'MARCAS',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Notificaciones
    |--------------------------------------------------------------------------
    */
    'notificaciones' => [
        'per_page' => env('INVENTARIO_NOTIFICACIONES_PER_PAGE', 10),
        'dropdown_limit' => env('INVENTARIO_NOTIFICACIONES_DROPDOWN', 5),
    ],
];
