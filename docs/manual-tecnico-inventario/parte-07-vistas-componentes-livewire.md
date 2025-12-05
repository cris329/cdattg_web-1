# Manual Técnico - Módulo de Inventario
## Parte 7: Vistas y Componentes Livewire

---

## 1. Introducción

### 1.1. Arquitectura de Vistas

El módulo de Inventario utiliza el sistema de plantillas Blade de Laravel, organizando las vistas de manera modular y reutilizable. Las vistas están estructuradas en:

- **Layouts**: Plantillas base que definen la estructura general
- **Vistas principales**: Páginas específicas de cada funcionalidad
- **Componentes**: Elementos reutilizables (Blade components y Livewire)
- **Partials**: Fragmentos de código reutilizables

### 1.2. Ubicación

- **Vistas principales**: `resources/views/inventario/`
- **Componentes reutilizables**: `resources/views/inventario/_components/`
- **Componentes Livewire**: `app/Livewire/Inventario/`
- **Vistas Livewire**: `resources/views/livewire/inventario/`

### 1.3. Tecnologías Utilizadas

- **Blade**: Motor de plantillas de Laravel
- **Livewire**: Framework para componentes interactivos
- **AdminLTE**: Framework CSS para el diseño
- **Vite**: Herramienta de build para assets
- **Chart.js**: Librería para gráficos (en dashboard)

---

## 2. Layout Base

### 2.1. Layout Principal

**Ubicación:** `resources/views/inventario/layouts/base.blade.php`

**Descripción:** Layout base que extiende de `adminlte::page` y proporciona la estructura común para todas las vistas del módulo.

**Características:**
- Extiende de `adminlte::page`
- Agrega clase `inventario-module` al body para aislar estilos
- Define secciones heredables: `title`, `css`, `js`
- Integra con AdminLTE para estilos y scripts

**Uso:**
```blade
@extends('inventario.layouts.base')

@section('title', 'Título de la Página')

@section('content')
    <!-- Contenido -->
@endsection
```

---

## 3. Componentes Reutilizables

### 3.1. Page Header

**Ubicación:** `resources/views/inventario/_components/page-header.blade.php`

**Descripción:** Componente para encabezados de página con breadcrumbs, búsqueda y acciones.

**Props:**
- `title` (string): Título de la página
- `subtitle` (string, opcional): Subtítulo
- `icon` (string, opcional): Clase del icono (default: 'fas fa-list')
- `breadcrumb` (array): Array de breadcrumbs
- `createRoute` (string, opcional): Ruta para crear nuevo elemento
- `createText` (string, opcional): Texto del botón crear (default: 'Nuevo')
- `showSearch` (bool, opcional): Mostrar barra de búsqueda (default: true)
- `showCart` (bool, opcional): Mostrar icono del carrito (default: false)
- `searchPlaceholder` (string, opcional): Placeholder de búsqueda

**Características:**
- Búsqueda con icono y botón de limpiar
- Botón de escanear código de barras (modal)
- Widget del carrito con contador
- Breadcrumbs navegables

**Ejemplo:**
```blade
<x-page-header
    icon="fas fa-boxes"
    title="Gestión de Productos"
    subtitle="Administra los productos del inventario"
    :breadcrumb="[
        ['label' => 'Inicio', 'url' => '/home'],
        ['label' => 'Inventario', 'active' => true],
        ['label' => 'Productos', 'active' => true]
    ]"
    :createRoute="route('inventario.productos.create')"
    createText="Crear Producto"
    :showCart="true"
/>
```

---

### 3.2. Common CSS

**Ubicación:** `resources/views/inventario/_components/common-css.blade.php`

**Descripción:** Incluye estilos CSS comunes del módulo.

**Incluye:**
- `parametros.css`: Estilos para parámetros
- `resources/css/inventario/shared/base.css`: Estilos base del módulo

**Uso:**
```blade
@include('inventario._components.common-css')
```

---

### 3.3. Common Footer

**Ubicación:** `resources/views/inventario/_components/common-footer.blade.php`

**Descripción:** Footer común del módulo (incluye footer SENA).

**Uso:**
```blade
@include('inventario._components.common-footer')
```

---

### 3.4. Cart Widget

**Ubicación:** `resources/views/inventario/_components/cart-widget.blade.php`

**Descripción:** Widget para agregar productos al carrito desde la vista de detalles.

**Props:**
- `producto`: Modelo del producto

**Características:**
- Muestra stock disponible con badge de color
- Control de cantidad con botones +/- 
- Validación de stock máximo
- Integración con localStorage para el carrito
- Notificaciones con SweetAlert2

**Funcionalidades JavaScript:**
- Incrementar/decrementar cantidad
- Validación de límites
- Agregar al carrito (localStorage)
- Actualizar contador del carrito

---

### 3.5. Filtros

**Ubicación:** `resources/views/inventario/_components/filtros.blade.php`

**Descripción:** Componente para filtrar órdenes por búsqueda y fechas.

**Características:**
- Búsqueda por ID o descripción
- Filtro por rango de fechas (desde/hasta)
- Botón de filtrar que actualiza la URL
- Tabla de resultados con paginación

**Uso:**
```blade
@include('inventario._components.filtros', [
    'ordenes' => $ordenes
])
```

---

### 3.6. Empty State

**Ubicación:** `resources/views/inventario/_components/empty-state.blade.php`

**Descripción:** Componente para mostrar estados vacíos.

**Props:**
- `message` (string, opcional): Mensaje a mostrar
- `icon` (string, opcional): Clase del icono
- `actionRoute` (string, opcional): Ruta del botón de acción
- `actionText` (string, opcional): Texto del botón

**Ejemplo:**
```blade
<x-empty-state
    message="No hay productos registrados"
    icon="fas fa-box"
    :actionRoute="route('inventario.productos.create')"
    actionText="Crear Producto"
/>
```

---

### 3.7. Image Modal

**Ubicación:** `resources/views/inventario/_components/image-modal.blade.php`

**Descripción:** Modal para escanear códigos de barras.

**Características:**
- Input para código de barras
- Integración con lector de códigos de barras
- Búsqueda automática al escanear

---

### 3.8. Alerts

**Ubicación:** `resources/views/inventario/_components/alerts.blade.php`

**Descripción:** Componente para mostrar alertas del sistema.

---

## 4. Componentes Livewire

### 4.1. DashboardInventario

**Ubicación:** `app/Livewire/Inventario/DashboardInventario.php`

**Vista:** `resources/views/livewire/inventario/dashboard-inventario.blade.php`

**Descripción:** Componente Livewire para el dashboard de inventario con métricas y gráficos.

**Propiedades Públicas:**
```php
public int $totalProductos = 0;
public int $productosConsumibles = 0;
public int $productosNoConsumibles = 0;
public int $productosPorVencer = 0;
public int $productosStockBajo = 0;
public int $totalCategorias = 0;
public array $productosMasSolicitados = [];
public array $productosPorCategoria = [];
public array $productosRecientes = [];
```

**Métodos:**

#### boot(DashboardRepository $dashboardRepository): void
Inicializa el repositorio del dashboard.

#### mount(): void
Carga los datos al montar el componente.

#### cargarDatos(): void
Carga todos los datos del dashboard desde el repositorio:
- Total de productos
- Productos consumibles y no consumibles
- Productos por vencer
- Productos con stock bajo
- Total de categorías
- Productos más solicitados (top 5)
- Productos por categoría
- Productos recientes (top 5)

#### refrescar(): void
Refresca manualmente los datos y dispara evento `datos-actualizados`.

**Características de la Vista:**

**Tarjetas de Estadísticas:**
- Total Productos (info)
- Productos por Vencer (warning)
- Stock Bajo (danger)
- Categorías (success)

**Gráficos:**
- **Gráfico de Barras**: Productos Consumibles vs No Consumibles (Chart.js)
- **Gráfico de Dona**: Productos por Categoría (Chart.js)

**Tablas:**
- **Productos Más Solicitados**: Tabla con barras de progreso
- **Productos Recientes**: Tabla con información básica

**Funcionalidades:**
- Actualización automática cada 5 minutos (`wire:poll.5m`)
- Botón de refrescar manual
- Integración con Chart.js para gráficos
- Eventos Livewire para actualizar gráficos

**JavaScript:**
- Inicialización de gráficos Chart.js
- Actualización de gráficos cuando Livewire actualiza datos
- Escucha de evento `datos-actualizados`

---

## 5. Vistas Principales

### 5.1. Dashboard

**Ubicación:** `resources/views/inventario/dashboard/index.blade.php`

**Descripción:** Vista principal del dashboard que incluye el componente Livewire.

**Características:**
- Extiende de `inventario.layouts.base`
- Incluye CSS común
- Usa componente `x-page-header` para encabezado
- Renderiza componente Livewire `DashboardInventario`
- Habilita plugin Chart.js

**Estructura:**
```blade
@extends('inventario.layouts.base')

@section('plugins.Chartjs', true)
@include('inventario._components.common-css')

@section('content_header')
    <x-page-header ... />
@endsection

@section('content')
    @livewire(\App\Livewire\Inventario\DashboardInventario::class)
@endsection
```

---

### 5.2. Productos - Index

**Ubicación:** `resources/views/inventario/productos/index.blade.php`

**Descripción:** Lista de productos con tabla, búsqueda y acciones CRUD.

**Características:**
- Tabla con paginación
- Búsqueda con escáner de código de barras
- Botón para crear producto
- Columnas: #, Producto, Código, Categoría, Marca, Cantidad, Peso, Estado, Contrato, Proveedor, Opciones
- Badges de colores según stock y estado
- Acciones: Ver, Editar, Eliminar
- Modal de confirmación de eliminación
- Modal para escanear código de barras

**Componentes Utilizados:**
- `x-create-card`: Botón para crear
- `x-data-table`: Tabla de datos
- `x-action-buttons`: Botones de acción
- `x-table-empty`: Estado vacío
- `x-confirm-delete-modal`: Modal de confirmación

**JavaScript:**
- `resources/js/inventario/escaner.js`: Funcionalidad del escáner
- `resources/js/pages/formularios-generico.js`: Funcionalidad genérica

---

### 5.3. Productos - Catálogo (Card)

**Ubicación:** `resources/views/inventario/productos/card.blade.php`

**Descripción:** Vista estilo e-commerce con productos en formato de tarjetas.

**Características:**
- Grid responsivo de productos (col-lg-3, col-md-4, col-sm-6)
- Filtros: búsqueda, tipo de producto, ordenamiento
- Tarjetas de producto con:
  - Imagen del producto (o placeholder)
  - Badge de stock (success/warning/danger)
  - Tipo, categoría y marca
  - Nombre y descripción
  - Código de barras
  - Stock disponible
  - Botones: Ver Detalles, Agregar al Carrito
- Paginación
- Modal de detalles del producto (dialog HTML5)
- Mensaje cuando no hay resultados

**Filtros:**
- Búsqueda por nombre
- Filtro por tipo de producto (Select2)
- Ordenamiento: Nombre, Stock Menor, Stock Mayor, Más Recientes

**JavaScript:**
- `resources/js/inventario/card.js`: Funcionalidad del catálogo
  - Búsqueda en tiempo real
  - Filtrado por tipo
  - Ordenamiento
  - Agregar al carrito
  - Modal de detalles

**Accesibilidad:**
- Captions en tablas
- Labels descriptivos
- Atributos ARIA en modales

---

### 5.4. Productos - Create

**Ubicación:** `resources/views/inventario/productos/create.blade.php`

**Descripción:** Formulario para crear un nuevo producto.

**Características:**
- Layout de dos columnas:
  - Columna izquierda: Vista previa de imagen
  - Columna derecha: Formulario
- Secciones del formulario:
  1. **Información Básica**: Nombre, código de barras, descripción
  2. **Clasificación y Tipo**: Tipo, categoría, marca, estado
  3. **Cantidad y Medidas**: Cantidad, peso, unidad de medida
  4. **Ubicación y Proveedor**: Ambiente, proveedor, contrato/convenio, fecha de vencimiento
- Vista previa de imagen con botón de selección
- Botón para escanear código de barras
- Validación en tiempo real
- Botones: Cancelar, Guardar

**Componentes:**
- `x-page-header`: Encabezado con breadcrumbs
- `@include('inventario._components.image-modal')`: Modal de escáner

**JavaScript:**
- `resources/js/inventario/imagen.js`: Manejo de imagen
- HTML5 QR Code Scanner: Para escanear códigos
- Preview de imagen al seleccionar archivo

**CSS:**
- `resources/css/inventario/shared/base.css`
- `resources/css/inventario/inventario.css`
- `resources/css/inventario/imagen.css`

---

### 5.5. Productos - Edit

**Ubicación:** `resources/views/inventario/productos/edit.blade.php`

**Descripción:** Similar a `create.blade.php` pero para editar producto existente.

**Diferencias con Create:**
- Pre-llena campos con datos del producto
- Mantiene imagen actual si no se envía nueva
- Ruta de actualización (`PUT`)

---

### 5.6. Productos - Show

**Ubicación:** `resources/views/inventario/productos/show.blade.php`

**Descripción:** Vista de detalles de un producto.

**Características:**
- Muestra toda la información del producto
- Relaciones: categoría, marca, proveedor, contrato, ambiente
- Imagen del producto
- Código de barras con opción de imprimir etiqueta
- Botones: Editar, Eliminar, Volver

---

### 5.7. Productos - Etiqueta

**Ubicación:** `resources/views/inventario/productos/etiqueta.blade.php`

**Descripción:** Vista imprimible de etiqueta con código de barras.

**Características:**
- Diseño optimizado para impresión
- Código de barras generado con JavaScript (JsBarcode)
- Información del producto
- Estilos para impresión (`@media print`)

---

### 5.8. Carrito

**Ubicación:** `resources/views/inventario/carrito/carrito.blade.php`

**Descripción:** Vista del carrito de compras con resumen y formulario de solicitud.

**Estructura:**
- **Columna izquierda (col-lg-8)**: Lista de productos en el carrito
  - Tabla con productos (imagen, nombre, stock, cantidad, acciones)
  - Botón para vaciar carrito
  - Mensaje cuando el carrito está vacío
- **Columna derecha (col-lg-4)**: Resumen de la solicitud
  - Total de productos
  - Total de items
  - Información del solicitante (nombre, correo)
  - Botones: Confirmar Solicitud, Guardar Borrador

**Modales:**
- **Modal de Confirmación**: Resumen de la orden antes de confirmar
- **Modal de Advertencia de Stock**: Muestra productos con stock insuficiente

**JavaScript:**
- `resources/js/inventario/carrito.js`: Funcionalidad del carrito
  - Cargar productos desde localStorage
  - Actualizar cantidades
  - Eliminar productos
  - Validar stock
  - Confirmar solicitud
  - Guardar borrador

**Características:**
- Carrito persistente en localStorage
- Validación de stock en tiempo real
- Resumen dinámico
- Integración con formulario de préstamos/salidas

---

### 5.9. Órdenes - Préstamos y Salidas

**Ubicación:** `resources/views/inventario/ordenes/prestamos_salidas.blade.php`

**Descripción:** Formulario para crear solicitud de préstamo o salida desde el carrito.

**Estructura:**
- **Resumen del Carrito**: Estadísticas (productos, items)
- **Tabla de Productos**: Lista de productos del carrito
- **Datos del Solicitante**: Nombre, correo, rol (readonly)
- **Programa de Formación**: Select con programas activos
- **Tipo**: Select (Préstamo/Salida)
- **Fecha de Devolución**: Aparece solo si es préstamo
- **Motivo**: Textarea para descripción

**Características:**
- Carga productos desde sessionStorage
- Muestra/oculta fecha de devolución según tipo
- Validación de formulario
- Envío de carrito como JSON en campo hidden

**JavaScript:**
- `resources/js/inventario/solicitud.js`: Funcionalidad del formulario
  - Cargar carrito desde sessionStorage
  - Mostrar/ocultar fecha de devolución
  - Validar antes de enviar

---

### 5.10. Órdenes - Index

**Ubicación:** `resources/views/inventario/ordenes/index.blade.php`

**Descripción:** Lista de órdenes con filtros.

**Características:**
- Usa componente `filtros.blade.php`
- Tabla con paginación
- Columnas: #, ID Orden, Usuario, Tipo, Estado, Fecha, Cantidad Items, Acciones
- Badges de colores según tipo y estado
- Acciones: Ver detalles, Editar (si está en espera)

---

### 5.11. Órdenes - Show

**Ubicación:** `resources/views/inventario/ordenes/show.blade.php`

**Descripción:** Vista de detalles de una orden.

**Características:**
- Información de la orden
- Lista de detalles (productos)
- Estado de cada detalle
- Devoluciones asociadas
- Acciones según estado

---

### 5.12. Devoluciones - Index

**Ubicación:** `resources/views/inventario/devoluciones/index.blade.php`

**Descripción:** Lista de préstamos pendientes de devolución.

**Características:**
- Tabla con préstamos aprobados pendientes
- Información: Producto, cantidad prestada, cantidad devuelta, pendiente
- Botón para registrar devolución

---

### 5.13. Devoluciones - Create

**Ubicación:** `resources/views/inventario/devoluciones/create.blade.php`

**Descripción:** Formulario para registrar una devolución.

**Características:**
- Información del préstamo
- Campo de cantidad a devolver
- Campo de observaciones (obligatorio si cantidad es 0)
- Validación de cantidad máxima

---

### 5.14. Aprobaciones - Pendientes

**Ubicación:** `resources/views/inventario/aprobaciones/pendientes.blade.php`

**Descripción:** Lista de detalles de orden pendientes de aprobación.

**Características:**
- Tabla con detalles pendientes
- Información: Orden, Producto, Cantidad, Solicitante, Fecha
- Botones: Aprobar, Rechazar
- Modal para motivo de rechazo

---

## 6. Estilos CSS

### 6.1. Estructura de CSS

Los estilos están organizados en:

- `resources/css/inventario/shared/base.css`: Estilos base del módulo
- `resources/css/inventario/inventario.css`: Estilos específicos de inventario
- `resources/css/inventario/imagen.css`: Estilos para manejo de imágenes
- `public/css/parametros.css`: Estilos para parámetros

### 6.2. Clases CSS Personalizadas

**Formularios:**
- `.form-group-modern`: Grupo de formulario moderno
- `.form-control-modern`: Input moderno
- `.form-section`: Sección del formulario
- `.form-section-title`: Título de sección

**Productos:**
- `.product-card`: Tarjeta de producto
- `.product-image-container`: Contenedor de imagen
- `.stock-badge`: Badge de stock

**Carrito:**
- `.cart-widget`: Widget del carrito
- `.cart-form`: Formulario del carrito

---

## 7. JavaScript

### 7.1. Archivos JavaScript

**Ubicación:** `resources/js/inventario/`

**Archivos principales:**
- `card.js`: Funcionalidad del catálogo de productos
- `carrito.js`: Funcionalidad del carrito de compras
- `solicitud.js`: Funcionalidad del formulario de préstamos/salidas
- `escaner.js`: Funcionalidad del escáner de códigos de barras
- `imagen.js`: Manejo de imágenes de productos

### 7.2. Funcionalidades JavaScript

**Catálogo (card.js):**
- Búsqueda en tiempo real
- Filtrado por tipo
- Ordenamiento
- Agregar al carrito (localStorage)
- Modal de detalles (AJAX)

**Carrito (carrito.js):**
- Cargar productos desde localStorage
- Actualizar cantidades
- Eliminar productos
- Validar stock
- Confirmar solicitud
- Guardar borrador

**Solicitud (solicitud.js):**
- Cargar carrito desde sessionStorage
- Mostrar/ocultar fecha de devolución
- Validar formulario

**Escáner (escaner.js):**
- Integración con lector de códigos de barras
- Búsqueda automática al escanear

---

## 8. Buenas Prácticas

### 8.1. Organización de Vistas

- **Separación de responsabilidades**: Cada vista tiene un propósito específico
- **Reutilización**: Componentes y partials para código común
- **Consistencia**: Uso de layouts y componentes estándar

### 8.2. Accesibilidad

- **Captions en tablas**: Para lectores de pantalla
- **Labels descriptivos**: En todos los inputs
- **Atributos ARIA**: En modales y elementos interactivos
- **Contraste de colores**: En badges y estados

### 8.3. Performance

- **Lazy loading**: Para imágenes grandes
- **Paginación**: Para listas grandes
- **Caché de assets**: Con Vite
- **Actualización selectiva**: Con Livewire

### 8.4. Responsive Design

- **Grid responsivo**: Bootstrap para layouts
- **Tablas responsivas**: Con `.table-responsive`
- **Modales adaptativos**: Para móviles

### 8.5. Seguridad

- **CSRF tokens**: En todos los formularios
- **Validación**: En cliente y servidor
- **Sanitización**: De datos de entrada
- **Autorización**: Con middlewares y policies

---

## 9. Integración con Assets

### 9.1. Vite

El módulo utiliza Vite para compilar y servir assets:

```blade
@vite(['resources/css/inventario/shared/base.css'])
@vite(['resources/js/inventario/card.js'])
```

### 9.2. Librerías Externas

- **Chart.js**: Para gráficos en dashboard
- **SweetAlert2**: Para notificaciones
- **Select2**: Para selects mejorados
- **HTML5 QR Code Scanner**: Para escanear códigos
- **JsBarcode**: Para generar códigos de barras

---

**Fin de la Parte 7**

*Continúa en la Parte 8: Testing y Comandos*

