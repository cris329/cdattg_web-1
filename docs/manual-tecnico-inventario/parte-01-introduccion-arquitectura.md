# Manual Técnico - Módulo de Inventario
## Parte 1: Introducción, Arquitectura y Requisitos

---

## 1. Introducción

### 1.1. Descripción General

El módulo de Inventario es un sistema integral de gestión de productos, órdenes de compra, proveedores y control de stock para el Centro Agroindustrial, Turístico y Tecnológico del Guaviare (CDATTG). Este módulo permite la administración completa del inventario institucional, incluyendo funcionalidades de catálogo e-commerce, gestión de préstamos, devoluciones y aprobaciones de órdenes.

### 1.2. Alcance del Módulo

El módulo de Inventario incluye las siguientes funcionalidades principales:

- **Gestión de Productos**: CRUD completo de productos con imágenes, códigos de barras, control de stock y catálogo e-commerce
- **Gestión de Órdenes**: Creación, aprobación y seguimiento de órdenes de compra/préstamo
- **Gestión de Proveedores**: Administración de proveedores y contratos/convenios
- **Gestión de Categorías y Marcas**: Organización de productos por categorías y marcas
- **Carrito de Compras**: Sistema de carrito para selección de productos
- **Devoluciones**: Proceso de devolución de productos prestados
- **Aprobaciones**: Sistema de aprobación de órdenes por roles autorizados
- **Notificaciones**: Sistema de notificaciones para eventos del inventario
- **Dashboard**: Panel de control con estadísticas y métricas del inventario

### 1.3. Tecnologías Utilizadas

- **Framework**: Laravel (PHP)
- **Frontend**: Blade Templates + AdminLTE 3
- **Componentes Reactivos**: Livewire
- **Base de Datos**: MySQL/MariaDB
- **Testing**: PHPUnit
- **Gestión de Permisos**: Spatie Laravel Permission
- **Generación de Códigos de Barras**: Biblioteca de códigos de barras PHP

---

## 2. Arquitectura del Módulo

### 2.1. Arquitectura en Capas

El módulo de Inventario sigue una arquitectura en capas que separa las responsabilidades de cada componente:

```
┌─────────────────────────────────────────┐
│   Capa de Presentación                  │
│   - Controllers                         │
│   - Livewire Components                 │
│   - Form Requests                       │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│   Capa de Lógica de Negocio            │
│   - Services                            │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│   Capa de Acceso a Datos                │
│   - Repositories                         │
│   - Models                               │
└─────────────────────────────────────────┘
```

### 2.2. Componentes Principales

#### 2.2.1. Controllers (11 controladores)

Ubicación: `app/Http/Controllers/Inventario/`

- **ProductoController**: Gestión CRUD de productos, catálogo e-commerce, búsqueda
- **CarritoController**: Gestión del carrito de compras
- **OrdenController**: Gestión de órdenes de compra
- **DashboardController**: Dashboard principal del módulo
- **CategoriaController**: Gestión de categorías
- **MarcaController**: Gestión de marcas
- **ProveedorController**: Gestión de proveedores
- **DevolucionController**: Gestión de devoluciones
- **AprobacionController**: Gestión de aprobaciones
- **ContratoConvenioController**: Gestión de contratos y convenios
- **NotificacionController**: Gestión de notificaciones

#### 2.2.2. Services (18 servicios)

Ubicación: `app/Inventario/Services/`

**Servicios Principales:**
- **ProductoService**: Lógica de negocio para productos
- **CarritoService**: Lógica del carrito de compras
- **OrdenService**: Lógica de órdenes
- **CategoriaService**: Lógica de categorías
- **MarcaService**: Lógica de marcas
- **ProveedorService**: Lógica de proveedores
- **DevolucionService**: Lógica de devoluciones
- **AprobacionService**: Lógica de aprobaciones
- **ContratoConvenioService**: Lógica de contratos
- **NotificationService**: Servicio de notificaciones
- **UserNotificationService**: Notificaciones de usuario

**Servicios Auxiliares:**
- **ProductoEnrichmentService**: Enriquecimiento de datos de productos
- **StockValidatorService**: Validación de stock
- **BarcodeService**: Generación de códigos de barras
- **ImageService**: Manejo de imágenes
- **FormOptionsService**: Opciones para formularios
- **FormDataService**: Datos para formularios
- **TransactionService**: Gestión de transacciones

#### 2.2.3. Repositories (12 repositorios)

Ubicación: `app/Inventario/Repositories/`

- **ProductoRepository**: Acceso a datos de productos
- **OrdenRepository**: Acceso a datos de órdenes
- **DetalleOrdenRepository**: Acceso a datos de detalles de orden
- **CategoriaRepository**: Acceso a datos de categorías
- **MarcaRepository**: Acceso a datos de marcas
- **ProveedorRepository**: Acceso a datos de proveedores
- **DevolucionRepository**: Acceso a datos de devoluciones
- **AprobacionRepository**: Acceso a datos de aprobaciones
- **ContratoConvenioRepository**: Acceso a datos de contratos
- **NotificationRepository**: Acceso a datos de notificaciones
- **DashboardRepository**: Datos para dashboard
- **UserRepository**: Acceso a datos de usuarios

#### 2.2.4. Models (10 modelos)

Ubicación: `app/Models/Inventario/`

- **Producto**: Modelo de producto
- **Orden**: Modelo de orden
- **DetalleOrden**: Modelo de detalle de orden
- **Categoria**: Modelo de categoría
- **Marca**: Modelo de marca
- **Proveedor**: Modelo de proveedor
- **Devolucion**: Modelo de devolución
- **Aprobacion**: Modelo de aprobación
- **ContratoConvenio**: Modelo de contrato/convenio
- **Notificacion**: Modelo de notificación

#### 2.2.5. Form Requests (8 requests)

Ubicación: `app/Http/Requests/Inventario/`

- **ProductoRequest**: Validación de productos
- **CarritoRequest**: Validación de carrito
- **OrdenRequest**: Validación de órdenes
- **DevolucionRequest**: Validación de devoluciones
- **AprobacionesRequest**: Validación de aprobaciones
- **ContratoConvenioRequest**: Validación de contratos
- **ProveedorRequest**: Validación de proveedores
- **MarcaCategoriaRequest**: Validación de marcas y categorías

#### 2.2.6. Livewire Components (1 componente)

- **DashboardInventario**: Componente Livewire para dashboard del inventario

#### 2.2.7. Rutas (10 archivos de rutas)

Ubicación: `routes/inventario/`

- **productos.php**: Rutas de productos
- **carrito.php**: Rutas de carrito
- **ordenes.php**: Rutas de órdenes
- **dashboard.php**: Rutas de dashboard
- **categoria.php**: Rutas de categorías
- **marca.php**: Rutas de marcas
- **proveedor.php**: Rutas de proveedores
- **devolucion.php**: Rutas de devoluciones
- **notificaciones.php**: Rutas de notificaciones
- **contratoConvenio.php**: Rutas de contratos

### 2.3. Principios de Diseño Aplicados

- **Separación de Responsabilidades**: Cada capa tiene una responsabilidad específica
- **Inversión de Dependencias**: Uso de interfaces para desacoplar componentes
- **Repository Pattern**: Abstracción del acceso a datos
- **Service Layer**: Lógica de negocio centralizada en servicios
- **Form Requests**: Validación centralizada en requests
- **DRY (Don't Repeat Yourself)**: Reutilización de código
- **KISS (Keep It Simple, Stupid)**: Simplicidad en el diseño
- **YAGNI (You Aren't Gonna Need It)**: Solo implementar lo necesario

---

## 3. Requisitos Previos

### 3.1. Requisitos del Sistema

#### 3.1.1. Servidor Web
- PHP >= 8.1
- Apache/Nginx con mod_rewrite habilitado
- Extensiones PHP requeridas:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML
  - GD o Imagick (para procesamiento de imágenes)
  - Zip (para generación de códigos de barras)

#### 3.1.2. Base de Datos
- MySQL >= 5.7 o MariaDB >= 10.3
- Usuario con permisos de CREATE, ALTER, DROP, INSERT, UPDATE, DELETE, SELECT

#### 3.1.3. Composer
- Composer >= 2.0 para gestión de dependencias PHP

#### 3.1.4. Node.js y NPM
- Node.js >= 16.x
- NPM >= 8.x (para compilación de assets frontend)

### 3.2. Dependencias del Proyecto

#### 3.2.1. Dependencias PHP (Composer)

Las dependencias principales están definidas en `composer.json`:

```json
{
    "require": {
        "php": "^8.1",
        "laravel/framework": "^10.0",
        "spatie/laravel-permission": "^5.0",
        "livewire/livewire": "^2.0"
    }
}
```

#### 3.2.2. Dependencias JavaScript (NPM)

Las dependencias frontend están definidas en `package.json`:

```json
{
    "dependencies": {
        "admin-lte": "^3.2",
        "sweetalert2": "^11.0",
        "bootstrap": "^5.0"
    }
}
```

### 3.3. Configuración del Entorno

#### 3.3.1. Variables de Entorno (Opcionales)

El módulo de Inventario utiliza variables de entorno opcionales que permiten personalizar el comportamiento del sistema sin modificar el código fuente. **Todas estas variables tienen valores por defecto** definidos en `config/inventario.php`, por lo que el módulo funciona correctamente sin necesidad de configurarlas.

**Importancia y Funcionalidad:**

Las variables de entorno del módulo de inventario permiten ajustar aspectos críticos del sistema según las necesidades específicas de cada instalación:

- **Configuración de Stock**: Controla los umbrales mínimos y críticos de inventario, así como la activación de notificaciones automáticas cuando el stock está bajo. Esto es fundamental para mantener un control proactivo del inventario y evitar desabastecimientos.

- **Configuración de Imágenes**: Define dónde se almacenan las imágenes de productos, el tamaño máximo permitido, la calidad de compresión y la imagen por defecto. Esto influye directamente en el rendimiento del servidor, el uso de almacenamiento y la experiencia del usuario al visualizar productos.

- **Configuración de Códigos de Barras**: Establece el formato, dimensiones, prefijo y longitud de los códigos de barras generados automáticamente. Esto es esencial para la estandarización y compatibilidad con sistemas de lectura de códigos de barras externos.

- **Configuración de Órdenes**: Define el período máximo permitido para devoluciones y si se deben enviar notificaciones automáticas cuando se crean nuevas órdenes. Esto impacta en los procesos de préstamo y devolución de productos.

- **Configuración de Notificaciones**: Controla la paginación y el límite de notificaciones mostradas en la interfaz. Esto afecta la experiencia del usuario y el rendimiento de las consultas a la base de datos.

**Ventajas de Usar Variables de Entorno:**

1. **Flexibilidad**: Permite adaptar el comportamiento del módulo sin modificar código, facilitando la personalización por entorno (desarrollo, producción, testing).

2. **Mantenibilidad**: Centraliza la configuración en un solo lugar (`config/inventario.php`), facilitando el mantenimiento y la actualización.

3. **Seguridad**: Permite mantener configuraciones sensibles fuera del código fuente, siguiendo las mejores prácticas de seguridad.

4. **Escalabilidad**: Facilita la configuración de diferentes instancias del sistema con parámetros específicos según el volumen de datos o las necesidades operativas.

**Nota:** Estas variables no están incluidas en el archivo `.env.example` del proyecto porque son opcionales y específicas del módulo de inventario. El sistema funcionará correctamente con los valores por defecto, pero pueden ser personalizadas agregándolas al archivo `.env` cuando sea necesario.

#### 3.3.2. Archivo de Configuración

El módulo tiene un archivo de configuración específico en `config/inventario.php` que centraliza todas las configuraciones del módulo. Este archivo utiliza la función `env()` de Laravel para leer las variables de entorno, con valores por defecto en caso de que no estén definidas.

**Estructura del archivo de configuración:**

```php
return [
    'stock' => [
        'umbral_minimo' => env('INVENTARIO_STOCK_MINIMO', 10),
        'umbral_critico' => env('INVENTARIO_STOCK_CRITICO', 5),
        'notificar_stock_bajo' => env('INVENTARIO_NOTIFICAR_STOCK_BAJO', true),
    ],
    'imagenes' => [
        'disco' => env('INVENTARIO_IMAGENES_DISCO', 'public'),
        'directorio' => env('INVENTARIO_IMAGENES_DIR', 'imagenes_productos'),
        // ... más configuraciones
    ],
    // ... más secciones
];
```

**Acceso a la configuración en el código:**

Para acceder a estas configuraciones en el código, se utiliza el helper `config()` de Laravel:

```php
$stockMinimo = config('inventario.stock.umbral_minimo');
$directorioImagenes = config('inventario.imagenes.directorio');
```

### 3.4. Permisos Requeridos

El módulo utiliza el sistema de permisos de Spatie Laravel Permission. Los siguientes permisos deben estar creados y asignados a los usuarios correspondientes:

**Permisos de Productos:**
- `VER DASHBOARD INVENTARIO`
- `VER PRODUCTO`
- `VER PRODUCTOS`
- `CREAR PRODUCTO`
- `EDITAR PRODUCTO`
- `ELIMINAR PRODUCTO`
- `BUSCAR PRODUCTO`
- `VER CATALOGO PRODUCTO`

**Permisos de Órdenes:**
- `VER ORDEN`
- `CREAR ORDEN`
- `EDITAR ORDEN`
- `ELIMINAR ORDEN`
- `APROBAR ORDEN`
- `COMPLETAR ORDEN`

**Permisos de Proveedores:**
- `VER PROVEEDOR`
- `CREAR PROVEEDOR`
- `EDITAR PROVEEDOR`
- `ELIMINAR PROVEEDOR`

**Permisos de Categorías:**
- `VER CATEGORIA`
- `CREAR CATEGORIA`
- `EDITAR CATEGORIA`
- `ELIMINAR CATEGORIA`

**Permisos de Marcas:**
- `VER MARCA`
- `CREAR MARCA`
- `EDITAR MARCA`
- `ELIMINAR MARCA`

**Permisos de Devoluciones:**
- `VER DEVOLUCION`
- `CREAR DEVOLUCION`
- `PROCESAR DEVOLUCION`

**Permisos de Notificaciones:**
- `VER NOTIFICACION`

**Permisos de Contratos:**
- `VER CONTRATO`
- `CREAR CONTRATO`
- `EDITAR CONTRATO`
- `ELIMINAR CONTRATO`

**Permisos de Carrito:**
- `VER CARRITO`
- `AGREGAR CARRITO`
- `ACTUALIZAR CARRITO`
- `ELIMINAR CARRITO`
- `VACIAR CARRITO`

---

## 4. Estructura de Directorios

### 4.1. Estructura Completa del Módulo

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Inventario/
│   │       ├── ProductoController.php
│   │       ├── CarritoController.php
│   │       ├── OrdenController.php
│   │       ├── DashboardController.php
│   │       ├── CategoriaController.php
│   │       ├── MarcaController.php
│   │       ├── ProveedorController.php
│   │       ├── DevolucionController.php
│   │       ├── AprobacionController.php
│   │       ├── ContratoConvenioController.php
│   │       └── NotificacionController.php
│   └── Requests/
│       └── Inventario/
│           ├── ProductoRequest.php
│           ├── CarritoRequest.php
│           ├── OrdenRequest.php
│           ├── DevolucionRequest.php
│           ├── AprobacionesRequest.php
│           ├── ContratoConvenioRequest.php
│           ├── ProveedorRequest.php
│           └── MarcaCategoriaRequest.php
├── Inventario/
│   ├── Services/
│   │   ├── Producto/
│   │   ├── Carrito/
│   │   ├── Orden/
│   │   ├── Categoria/
│   │   ├── Marca/
│   │   ├── Proveedor/
│   │   ├── Devolucion/
│   │   ├── Aprobacion/
│   │   ├── ContratoConvenio/
│   │   ├── Notification/
│   │   ├── ProductoEnrichment/
│   │   ├── StockValidator/
│   │   ├── Barcode/
│   │   ├── Image/
│   │   ├── FormOptions/
│   │   ├── FormData/
│   │   └── Transaction/
│   └── Repositories/
│       ├── Producto/
│       ├── Orden/
│       ├── DetalleOrden/
│       ├── Categoria/
│       ├── Marca/
│       ├── Proveedor/
│       ├── Devolucion/
│       ├── Aprobacion/
│       ├── ContratoConvenio/
│       ├── Notification/
│       ├── Dashboard/
│       └── User/
└── Models/
    └── Inventario/
        ├── Producto.php
        ├── Orden.php
        ├── DetalleOrden.php
        ├── Categoria.php
        ├── Marca.php
        ├── Proveedor.php
        ├── Devolucion.php
        ├── Aprobacion.php
        ├── ContratoConvenio.php
        └── Notificacion.php

routes/
└── inventario/
    ├── productos.php
    ├── carrito.php
    ├── ordenes.php
    ├── dashboard.php
    ├── categoria.php
    ├── marca.php
    ├── proveedor.php
    ├── devolucion.php
    ├── notificaciones.php
    └── contratoConvenio.php

resources/
└── views/
    └── inventario/
        ├── dashboard/
        ├── productos/
        ├── carrito/
        ├── ordenes/
        ├── categorias/
        ├── marcas/
        ├── proveedores/
        ├── devoluciones/
        ├── aprobaciones/
        ├── contratos-convenios/
        └── notificaciones/

database/
└── migrations/
    └── batch_16_inventario/
        ├── 2025_08_12_184612_create_productos_table.php
        ├── 2025_08_13_225702_add_cantidad_and_codigo_barras_to_productos_table.php
        ├── 2025_08_14_181706_add_imagen_to_productos_table.php
        ├── 2025_08_15_095541_add_peso_to_productos_table.php
        ├── 2025_08_15_120944_rename_estado_id_to_estado_producto_id_in_productos_table.php
        ├── 2025_08_15_202902_create_ordenes_table.php
        ├── 2025_08_16_092603_add_fecha_devolucion_to_ordenes_table.php
        ├── 2025_08_22_232353_create_proveedores_table.php
        ├── 2025_08_22_233125_create_contratos_convenios_table.php
        ├── 2025_10_01_184449_add_codigo_to_contratos_convenios_table.php
        ├── 2025_10_01_185055_add_campos_extra_to_productos_table.php
        ├── 2025_10_03_084350_add_nit_email_to_proveedores_table.php
        ├── 2025_10_03_095032_add_fecha_vencimiento_to_productos_table.php
        ├── 2025_10_03_101454_add_proveedor_to_productos_table.php
        ├── 2025_10_16_000001_create_detalle_ordenes_table.php
        ├── 2025_10_16_000002_create_devoluciones_table.php
        ├── 2025_10_29_185352_add_campos_extra_to_proveedores_table.php
        ├── 2025_11_01_000002_create_aprobaciones_table.php
        ├── 2025_11_01_132948_add_programa_formacion_to_ordenes_table.php
        ├── 2025_11_01_180529_add_departamento_to_proveedores_table.php
        ├── 2025_11_02_161704_create_notifications_table.php
        └── 2025_11_11_000010_add_cierra_sin_stock_to_devoluciones_table.php

tests/
└── Modulos/
    └── Inventario/
        ├── Feature/
        │   ├── Controllers/
        │   ├── Requests/
        │   └── Routes/
        └── Unit/
            ├── Models/
            ├── Repositories/
            └── Services/

config/
└── inventario.php
```

---

**Fin de la Parte 1**

*Continúa en la Parte 2: Base de Datos y Migraciones*

