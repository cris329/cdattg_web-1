# Manual Técnico - Módulo de Inventario
## Parte 6: Controladores, Form Requests y Rutas

---

## 1. Introducción

### 1.1. Arquitectura de Capa de Presentación

El módulo de Inventario sigue el patrón MVC (Model-View-Controller) de Laravel, donde:

- **Controladores**: Gestionan el flujo de peticiones HTTP, delegan lógica a servicios y retornan respuestas
- **Form Requests**: Centralizan la validación de datos de entrada
- **Rutas**: Definen los endpoints HTTP y asocian controladores y middlewares

### 1.2. Principios Aplicados

- **Single Responsibility**: Cada controlador maneja una entidad específica
- **Dependency Injection**: Los controladores reciben servicios y repositories por inyección
- **Authorization**: Uso de middlewares y policies para control de acceso
- **Validation**: Validación centralizada mediante Form Requests
- **RESTful**: Rutas organizadas siguiendo convenciones REST

---

## 2. Controladores

### 2.1. Estructura General

Todos los controladores del módulo de Inventario:

- Extienden `App\Http\Controllers\Controller`
- Están ubicados en `app/Http/Controllers/Inventario/`
- Utilizan `declare(strict_types=1)` para tipado estricto
- Implementan inyección de dependencias en el constructor
- Aplican middlewares de autenticación y autorización

### 2.2. ProductoController

**Ubicación:** `app/Http/Controllers/Inventario/ProductoController.php`

**Dependencias:**
- `ProductoRepositoryInterface`
- `ProductoService`
- `FormOptionsServiceInterface`
- `StockValidatorServiceInterface`
- `ProductoEnrichmentService`
- `FormDataService`

**Middlewares:**
- `auth`: Requiere autenticación
- `can:VER PRODUCTO`: Solo para `index`, `show`
- `can:VER CATALOGO PRODUCTO`: Solo para `catalogo`
- `can:BUSCAR PRODUCTO`: Solo para `buscar`
- `can:CREAR PRODUCTO`: Solo para `create`, `store`
- `can:EDITAR PRODUCTO`: Solo para `edit`, `update`
- `can:ELIMINAR PRODUCTO`: Solo para `destroy`

**Métodos:**

#### index(Request $request): View
Lista productos con filtros de búsqueda y paginación.

**Filtros:**
- `search`: Búsqueda por nombre o código
- `per_page`: 10 por defecto

**Retorna:** Vista `inventario.productos.index` con productos enriquecidos con marcas y categorías.

#### create(): View
Muestra formulario de creación de producto.

**Retorna:** Vista `inventario.productos.create` con:
- Opciones de formulario (tipos, estados, unidades, categorías, marcas)
- Datos de formulario (contratos, ambientes, proveedores)
- Productos para catálogo (12 por página)
- Tipos de productos

#### store(ProductoRequest $request): RedirectResponse
Crea un nuevo producto.

**Proceso:**
1. Valida datos con `ProductoRequest`
2. Procesa imagen si se proporciona
3. Llama a `ProductoService::crear()`
4. Redirige con mensaje de éxito

#### show(string $id): View
Muestra detalles de un producto.

**Retorna:** Vista `inventario.productos.show` con producto y relaciones.

#### edit(string $id): View
Muestra formulario de edición.

**Retorna:** Vista `inventario.productos.edit` con producto y opciones de formulario.

#### update(ProductoRequest $request, string $id): RedirectResponse
Actualiza un producto existente.

**Proceso:**
1. Valida datos con `ProductoRequest`
2. Procesa imagen si se proporciona nueva
3. Llama a `ProductoService::actualizar()`
4. Redirige a `show` con mensaje de éxito

#### destroy(string $id): RedirectResponse
Elimina un producto.

**Proceso:**
1. Encuentra el producto
2. Llama a `ProductoService::eliminar()`
3. Redirige con mensaje de éxito

#### catalogo(Request $request): View
Muestra catálogo estilo e-commerce.

**Filtros:**
- `search`: Búsqueda
- `tipo_producto_id`: Filtro por tipo
- `sort_by`: Ordenamiento (default: 'name')
- `per_page`: 12 por defecto

**Retorna:** Vista `inventario.productos.card` con productos enriquecidos.

#### buscar(Request $request): JsonResponse
Búsqueda AJAX de productos.

**Retorna:** JSON con array de productos (incluye `imagen_url`).

#### agregarAlCarrito(ProductoRequest $request): JsonResponse
Valida producto para agregar al carrito.

**Validaciones:**
- Producto existe
- Stock suficiente

**Retorna:** JSON con éxito/error y datos del producto.

#### detalles(string $id): View
Obtiene detalles de producto para modal.

**Retorna:** Vista parcial `inventario.productos._detalles-modal`.

#### etiqueta(string $id): View
Vista imprimible de etiqueta con código de barras.

**Retorna:** Vista `inventario.productos.etiqueta`.

#### buscarPorCodigo(string $codigo): JsonResponse
Busca producto por código de barras.

**Retorna:** JSON con producto o 404.

---

### 2.3. OrdenController

**Ubicación:** `app/Http/Controllers/Inventario/OrdenController.php`

**Dependencias:**
- `OrdenRepositoryInterface`
- `OrdenService`

**Middlewares:**
- `can:VER ORDEN`: `index`, `show`, `prestamosSalidas`
- `can:CREAR ORDEN`: `store`, `storePrestamos`
- `can:EDITAR ORDEN`: `update`
- `can:ELIMINAR ORDEN`: `destroy`
- `can:APROBAR ORDEN`: `aprobar`
- `can:COMPLETAR ORDEN`: `completar`

**Métodos:**

#### index(Request $request): View
Lista órdenes con filtros.

**Filtros:**
- `search`: Búsqueda
- `per_page`: 15 por defecto

**Retorna:** Vista `inventario.ordenes.index`.

#### store(OrdenRequest $request): RedirectResponse
Crea una orden normal (no desde carrito).

**Proceso:**
1. Valida con `OrdenRequest`
2. Llama a `OrdenService::crear()`
3. Maneja excepciones `OrdenException`
4. Redirige con mensaje de éxito/error

#### prestamosSalidas(): View
Muestra formulario de solicitud de préstamo/salida.

**Retorna:** Vista `inventario.ordenes.prestamos_salidas` con programas de formación.

#### storePrestamos(OrdenRequest $request): RedirectResponse
Crea orden desde carrito (préstamos/salidas).

**Proceso:**
1. Valida con `OrdenRequest`
2. Llama a `OrdenService::crearDesdeCarrito()`
3. Limpia carrito de sesión
4. Redirige al catálogo con mensaje de éxito

#### pendientes(): View
Muestra órdenes pendientes (EN ESPERA).

**Retorna:** Vista `inventario.ordenes.pendientes`.

#### completadas(): View
Muestra órdenes completadas (APROBADA).

**Retorna:** Vista `inventario.ordenes.completadas`.

#### rechazadas(): View
Muestra órdenes rechazadas (RECHAZADA).

**Retorna:** Vista `inventario.ordenes.rechazadas`.

#### update(OrdenRequest $request, string $id): RedirectResponse
Actualiza una orden.

**Validaciones:**
- Orden no debe tener devoluciones

**Proceso:**
1. Valida que no tenga devoluciones
2. Valida con `OrdenRequest`
3. Llama a `OrdenService::actualizar()`
4. Redirige con mensaje

#### destroy(string $id): RedirectResponse
Elimina una orden.

**Validaciones:**
- Orden no debe tener devoluciones

**Proceso:**
1. Valida que no tenga devoluciones
2. Llama a `OrdenService::eliminar()`
3. Redirige con mensaje

#### show(Orden $orden): View
Muestra detalles de una orden.

**Retorna:** Vista `inventario.ordenes.show` con orden y relaciones.

---

### 2.4. CarritoController

**Ubicación:** `app/Http/Controllers/Inventario/CarritoController.php`

**Dependencias:**
- `CarritoService`
- `ProductoRepositoryInterface`

**Middlewares:**
- `can:VER CARRITO`: `index`
- `can:AGREGAR CARRITO`: `agregar`, `store`
- `can:ACTUALIZAR CARRITO`: `actualizar`, `update`
- `can:ELIMINAR CARRITO`: `eliminar`, `destroy`
- `can:VACIAR CARRITO`: `vaciar`

**Métodos:**

#### index(): View
Muestra vista del carrito.

**Retorna:** Vista `inventario.carrito.carrito`.

#### agregar(CarritoRequest $request): JsonResponse
Valida disponibilidad de productos para agregar al carrito.

**Proceso:**
1. Valida con `CarritoRequest`
2. Verifica disponibilidad con `CarritoService::verificarDisponibilidad()`
3. Retorna JSON con errores de stock o éxito

**Retorna:** JSON con `success`, `message`, `errores` (si hay problemas de stock).

#### actualizar(CarritoRequest $request, int $id): JsonResponse
Valida actualización de cantidad de un item.

**Proceso:**
1. Valida con `CarritoRequest`
2. Valida item con `CarritoService::validarItem()`
3. Retorna JSON con resultado

**Retorna:** JSON con `success`, `message`, `stock_disponible` (si hay error).

#### eliminar(int $id): JsonResponse
Valida eliminación de producto del carrito (operación del lado del cliente).

**Proceso:**
1. Verifica que el producto existe
2. Retorna JSON de confirmación

**Retorna:** JSON con `success`, `message`.

#### vaciar(): JsonResponse
Confirma vaciado del carrito (operación del lado del cliente).

**Retorna:** JSON con `success`, `message`.

#### contenido(Request $request): JsonResponse
Obtiene contenido del carrito con información completa de productos.

**Proceso:**
1. Recibe array de items del request
2. Obtiene productos con `CarritoService::obtenerProductosParaCarrito()`
3. Retorna JSON con productos

**Retorna:** JSON con `success`, `productos` (array).

---

### 2.5. DashboardController

**Ubicación:** `app/Http/Controllers/Inventario/DashboardController.php`

**Dependencias:** Ninguna (usa componente Livewire)

**Middlewares:**
- `can:VER DASHBOARD INVENTARIO`: Solo para `index`

**Métodos:**

#### index(): View
Muestra dashboard de inventario usando componente Livewire.

**Retorna:** Vista `inventario.dashboard.index` (contiene componente Livewire).

---

### 2.6. ProveedorController

**Ubicación:** `app/Http/Controllers/Inventario/ProveedorController.php`

**Dependencias:**
- `ProveedorRepositoryInterface`
- `ProveedorService`

**Middlewares:**
- `can:VER PROVEEDOR`: `index`, `show`
- `can:CREAR PROVEEDOR`: `create`, `store`
- `can:EDITAR PROVEEDOR`: `edit`, `update`
- `can:ELIMINAR PROVEEDOR`: `destroy`

**Métodos:**

#### index(Request $request): View
Lista proveedores con filtros.

**Filtros:**
- `search`: Búsqueda
- `per_page`: 10 por defecto

**Retorna:** Vista `inventario.proveedores.index`.

#### create(): View
Muestra formulario de creación.

**Retorna:** Vista `inventario.proveedores.create` con departamentos y municipios.

#### store(ProveedorRequest $request): RedirectResponse
Crea un nuevo proveedor.

**Proceso:**
1. Valida con `ProveedorRequest`
2. Llama a `ProveedorService::crear()`
3. Redirige con mensaje de éxito

#### show(Proveedor $proveedor): View
Muestra detalles de un proveedor.

**Retorna:** Vista `inventario.proveedores.show`.

#### edit(Proveedor $proveedor): View
Muestra formulario de edición.

**Retorna:** Vista `inventario.proveedores.edit` con departamentos y municipios.

#### update(ProveedorRequest $request, Proveedor $proveedor): RedirectResponse
Actualiza un proveedor.

**Proceso:**
1. Valida con `ProveedorRequest`
2. Llama a `ProveedorService::actualizar()`
3. Redirige con mensaje de éxito

#### destroy(Proveedor $proveedor): RedirectResponse
Elimina un proveedor.

**Proceso:**
1. Llama a `ProveedorService::eliminar()`
2. Maneja `ProveedorException`
3. Redirige con mensaje

#### getMunicipiosPorDepartamento(int $departamentoId): JsonResponse
Obtiene municipios por departamento (AJAX).

**Retorna:** JSON con array de municipios.

---

### 2.7. CategoriaController

**Ubicación:** `app/Http/Controllers/Inventario/CategoriaController.php`

**Dependencias:**
- `CategoriaRepositoryInterface`
- `CategoriaService`

**Middlewares:**
- `can:VER CATEGORIA`: `index`, `show`
- `can:CREAR CATEGORIA`: `create`, `store`
- `can:EDITAR CATEGORIA`: `edit`, `update`
- `can:ELIMINAR CATEGORIA`: `destroy`

**Métodos:**

#### index(Request $request): View|RedirectResponse
Lista categorías con filtros.

**Validaciones:**
- Verifica que exista el tema "CATEGORIAS"

**Filtros:**
- `search`: Búsqueda
- `per_page`: 10 por defecto

**Retorna:** Vista `inventario.categorias.index` o redirección con error.

#### create(): View
Muestra formulario de creación.

**Retorna:** Vista `inventario.categorias.create`.

#### store(MarcaCategoriaRequest $request): RedirectResponse
Crea una nueva categoría.

**Proceso:**
1. Valida con `MarcaCategoriaRequest`
2. Llama a `CategoriaService::crear()`
3. Maneja `CategoriaException`
4. Redirige con mensaje

#### edit(Parametro $categoria): View
Muestra formulario de edición.

**Retorna:** Vista `inventario.categorias.edit` con datos del formulario.

#### update(MarcaCategoriaRequest $request, Parametro $categoria): RedirectResponse
Actualiza una categoría.

**Proceso:**
1. Valida con `MarcaCategoriaRequest`
2. Llama a `CategoriaService::actualizar()`
3. Redirige con mensaje

#### destroy(Parametro $categoria): RedirectResponse
Elimina una categoría.

**Proceso:**
1. Llama a `CategoriaService::eliminar()`
2. Maneja `CategoriaException`
3. Redirige con mensaje

#### show(Parametro $categoria): View
Muestra detalles de una categoría.

**Retorna:** Vista `inventario.categorias.show`.

---

### 2.8. MarcaController

**Ubicación:** `app/Http/Controllers/Inventario/MarcaController.php`

**Dependencias:**
- `MarcaRepositoryInterface`
- `MarcaService`

**Middlewares:**
- `can:VER MARCA`: `index`, `show`
- `can:CREAR MARCA`: `create`, `store`
- `can:EDITAR MARCA`: `edit`, `update`
- `can:ELIMINAR MARCA`: `destroy`

**Métodos:** Similar a `CategoriaController` (CRUD completo).

---

### 2.9. DevolucionController

**Ubicación:** `app/Http/Controllers/Inventario/DevolucionController.php`

**Dependencias:**
- `DevolucionRepositoryInterface`
- `DetalleOrdenRepositoryInterface`
- `DevolucionService`

**Middlewares:**
- `can:DEVOLVER PRESTAMO`: `index`, `create`, `store`

**Métodos:**

#### index(): View
Lista préstamos pendientes de devolución.

**Proceso:**
1. Obtiene estado "APROBADA"
2. Obtiene préstamos pendientes
3. Retorna vista

**Retorna:** Vista `inventario.devoluciones.index`.

#### create(int $detalleOrdenId): View|RedirectResponse
Muestra formulario de devolución.

**Validaciones:**
- Detalle de orden existe
- No está completamente devuelto

**Retorna:** Vista `inventario.devoluciones.create` o redirección con error.

#### store(DevolucionRequest $request): RedirectResponse
Registra una devolución.

**Validaciones:**
- Si `cantidad_devuelta` es 0, `observaciones` es obligatorio

**Proceso:**
1. Valida con `DevolucionRequest`
2. Valida observaciones si cantidad es 0
3. Llama a `DevolucionService::registrarDevolucionConMensaje()`
4. Maneja `DevolucionException`
5. Redirige con mensaje

#### historial(): View
Muestra historial de devoluciones.

**Retorna:** Vista `inventario.devoluciones.historial`.

#### show(int $id): View
Muestra detalles de una devolución.

**Retorna:** Vista `inventario.devoluciones.show`.

#### misPrestamos(): View
Muestra préstamos activos del usuario actual.

**Retorna:** Vista `inventario.prestamos.usuariosPrestamos`.

#### historialPrestamos(): View
Muestra historial de préstamos del usuario.

**Retorna:** Vista `inventario.prestamos.historial`.

---

### 2.10. AprobacionController

**Ubicación:** `app/Http/Controllers/Inventario/AprobacionController.php`

**Dependencias:**
- `AprobacionService`

**Middlewares:**
- `can:APROBAR ORDEN`: Todos los métodos

**Métodos:**

#### pendientes(): View
Muestra detalles de orden pendientes de aprobación.

**Retorna:** Vista `inventario.aprobaciones.pendientes` con colección de detalles.

#### aprobar(int $detalleOrdenId): RedirectResponse
Aprueba un detalle de orden.

**Proceso:**
1. Encuentra detalle con relaciones
2. Llama a `AprobacionService::aprobarDetalle()`
3. Maneja `AprobacionException`
4. Redirige con mensaje de éxito

#### rechazar(AprobacionesRequest $request, int $detalleOrdenId): RedirectResponse
Rechaza un detalle de orden.

**Proceso:**
1. Valida con `AprobacionesRequest`
2. Encuentra detalle con relaciones
3. Llama a `AprobacionService::rechazarDetalle()`
4. Maneja `AprobacionException`
5. Redirige con mensaje

#### aprobarOrden(int $ordenId): RedirectResponse
Aprueba una orden completa.

**Proceso:**
1. Encuentra orden con detalles y devoluciones
2. Llama a `AprobacionService::aprobarOrdenCompleta()`
3. Maneja `AprobacionException`
4. Redirige con mensaje

#### rechazarOrden(AprobacionesRequest $request, int $ordenId): RedirectResponse
Rechaza una orden completa.

**Proceso:** Similar a `rechazar()` pero para orden completa.

**Método Auxiliar:**

#### handleAprobacion(callable $callback): RedirectResponse
Manejo centralizado de excepciones de aprobación.

---

### 2.11. ContratoConvenioController

**Ubicación:** `app/Http/Controllers/Inventario/ContratoConvenioController.php`

**Dependencias:**
- `ContratoConvenioRepositoryInterface`
- `ContratoConvenioService`
- `ProveedorRepositoryInterface`

**Middlewares:**
- `can:VER CONTRATO`: `index`, `show`
- `can:CREAR CONTRATO`: `create`, `store`
- `can:EDITAR CONTRATO`: `edit`, `update`
- `can:ELIMINAR CONTRATO`: `destroy`

**Métodos:** CRUD completo similar a otros controladores.

---

### 2.12. NotificacionController

**Ubicación:** `app/Http/Controllers/Inventario/NotificacionController.php`

**Dependencias:**
- `UserNotificationService`

**Middlewares:**
- `can:VER NOTIFICACION`: `index`

**Métodos:**

#### index(): View
Muestra todas las notificaciones del usuario paginadas.

**Retorna:** Vista `inventario.notificaciones.index`.

#### getUnread(): JsonResponse
Obtiene notificaciones no leídas para dropdown.

**Retorna:** JSON con datos del dropdown (notificaciones, contador).

#### markAsRead(string $id): JsonResponse
Marca una notificación como leída.

**Retorna:** JSON con `success`, `message`.

#### markAllAsRead(): JsonResponse
Marca todas las notificaciones como leídas.

**Retorna:** JSON con `success`, `message`, contador.

#### destroy(string $id): RedirectResponse
Elimina una notificación.

**Retorna:** Redirección con mensaje de éxito.

#### destroyAll(): JsonResponse
Elimina todas las notificaciones del usuario.

**Retorna:** JSON con `success`, `message`, `deleted` (contador).

---

## 3. Form Requests

### 3.1. Introducción

Los Form Requests centralizan la validación de datos de entrada. Todos extienden `Illuminate\Foundation\Http\FormRequest` y están ubicados en `app/Http/Requests/Inventario/`.

### 3.2. ProductoRequest

**Ubicación:** `app/Http/Requests/Inventario/ProductoRequest.php`

**Autorización:** `authorize()` retorna `true` (autorización manejada por middlewares).

**Reglas de Validación:**

**Para agregar al carrito** (`routeIs('inventario.productos.agregar-carrito')`):
```php
[
    'producto_id' => 'required|exists:productos,id',
    'cantidad' => 'required|integer|min:1'
]
```

**Para crear producto** (`isMethod('POST')`):
```php
[
    'producto' => 'required|unique:productos',
    'cantidad' => 'required|integer|min:1',
    'proveedor_id' => 'required|exists:proveedores,id',
    // ... reglas base
]
```

**Para actualizar producto** (`isMethod('PUT')` o `PATCH`):
```php
[
    'producto' => 'required|unique:productos,producto,{id}',
    'cantidad' => 'required|integer|min:0',
    // ... reglas base
]
```

**Reglas Base Comunes:**
```php
[
    'tipo_producto_id' => 'required|exists:parametros_temas,id',
    'descripcion' => 'required|string',
    'peso' => 'required|numeric|min:0',
    'unidad_medida_id' => 'required|exists:parametros_temas,id',
    'codigo_barras' => 'nullable|string',
    'estado_producto_id' => 'required|exists:parametros_temas,id',
    'categoria_id' => 'required|exists:parametros,id',
    'marca_id' => 'required|exists:parametros,id',
    'contrato_convenio_id' => 'required|exists:contratos_convenios,id',
    'ambiente_id' => 'required|exists:ambientes,id',
    'fecha_vencimiento' => 'nullable|date',
    'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
]
```

---

### 3.3. OrdenRequest

**Ubicación:** `app/Http/Requests/Inventario/OrdenRequest.php`

**Reglas de Validación:**

**Para préstamos/salidas** (`routeIs('inventario.prestamos-salidas.store')`):
```php
[
    'rol' => 'required|string|max:100',
    'programa_formacion' => 'required|string|max:255',
    'tipo' => 'required|in:prestamo,salida',
    'fecha_devolucion' => 'required_if:tipo,prestamo|nullable|date|after:today',
    'descripcion' => 'required|string',
    'carrito' => 'required|json'
]
```

**Para órdenes normales** (store/update):
```php
[
    'descripcion_orden' => 'required|string',
    'tipo_orden_id' => 'required|exists:parametros_temas,id',
    'fecha_devolucion' => 'nullable|date|after:today',
    'productos' => 'required|array|min:1',
    'productos.*.producto_id' => 'required|exists:productos,id',
    'productos.*.cantidad' => 'required|integer|min:1',
    'productos.*.estado_orden_id' => 'required|exists:parametros_temas,id'
]
```

**Mensajes Personalizados:**
```php
[
    'fecha_devolucion.after' => 'La fecha de devolución debe ser posterior a hoy.',
    'fecha_devolucion.required_if' => 'La fecha de devolución es obligatoria para préstamos.',
    'fecha_devolucion.date' => 'La fecha de devolución debe ser una fecha válida.',
]
```

---

### 3.4. CarritoRequest

**Ubicación:** `app/Http/Requests/Inventario/CarritoRequest.php`

**Reglas de Validación:**

**Para actualizar** (`routeIs('inventario.carrito.actualizar')`):
```php
[
    'cantidad' => 'required|integer|min:1',
]
```

**Para agregar** (default):
```php
[
    'items' => 'required|array',
    'items.*.producto_id' => 'required|integer|exists:productos,id',
    'items.*.cantidad' => 'required|integer|min:1',
]
```

---

### 3.5. ProveedorRequest

**Ubicación:** `app/Http/Requests/Inventario/ProveedorRequest.php`

**Reglas de Validación:**

**Para crear:**
```php
[
    'proveedor' => 'required|unique:proveedores,proveedor',
    'nit' => 'nullable|string|max:50|unique:proveedores,nit',
    'email' => 'nullable|email|max:255|unique:proveedores,email',
    'telefono' => 'nullable|string|max:10',
    'direccion' => 'nullable|string|max:255',
    'departamento_id' => 'nullable|exists:departamentos,id',
    'municipio_id' => 'nullable|exists:municipios,id',
    'contacto' => 'nullable|string|max:100',
    'estado_id' => 'nullable|exists:parametros_temas,id'
]
```

**Para actualizar:**
- Similar pero con `Rule::unique()->ignore($proveedorId)` para `proveedor`, `nit`, `email`.

---

### 3.6. MarcaCategoriaRequest

**Ubicación:** `app/Http/Requests/Inventario/MarcaCategoriaRequest.php`

**Uso:** Compartido para marcas y categorías.

**Reglas de Validación:**

**Para crear:**
```php
[
    'name' => 'required|string|unique:parametros,name',
]
```

**Para actualizar:**
```php
[
    'name' => 'required|string|unique:parametros,name,{id}',
]
```

**Nota:** Obtiene el ID del parámetro desde `route('categoria')` o `route('marca')`.

---

### 3.7. DevolucionRequest

**Ubicación:** `app/Http/Requests/Inventario/DevolucionRequest.php`

**Reglas de Validación:**
```php
[
    'detalle_orden_id' => 'required|integer|exists:detalle_ordenes,id',
    'cantidad_devuelta' => 'required|integer|min:0',
    'observaciones' => 'nullable|string|max:500'
]
```

**Nota:** La validación de que `observaciones` sea obligatorio si `cantidad_devuelta` es 0 se hace en el controlador.

---

### 3.8. AprobacionesRequest

**Ubicación:** `app/Http/Requests/Inventario/AprobacionesRequest.php`

**Reglas de Validación:**
```php
[
    'motivo_rechazo' => 'required|string|max:1000'
]
```

**Uso:** Solo para rechazar detalles u órdenes.

---

### 3.9. ContratoConvenioRequest

**Ubicación:** `app/Http/Requests/Inventario/ContratoConvenioRequest.php`

**Reglas de Validación:**

**Para crear:**
```php
[
    'name' => 'required|string|max:255|unique:contratos_convenios,name',
    'codigo' => 'nullable|string|max:100|unique:contratos_convenios,codigo',
    'proveedor_id' => 'nullable|exists:proveedores,id',
    'fecha_inicio' => 'nullable|date',
    'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
    'estado_id' => 'required|exists:parametros_temas,id',
]
```

**Para actualizar:**
- Similar pero con `unique()->ignore($contratoId)` para `name` y `codigo`.

---

## 4. Rutas

### 4.1. Organización Modular

Las rutas del módulo de Inventario están organizadas en archivos separados dentro de `routes/inventario/`:

- `dashboard.php`: Dashboard
- `productos.php`: Productos
- `ordenes.php`: Órdenes y aprobaciones
- `carrito.php`: Carrito de compras
- `proveedor.php`: Proveedores
- `categoria.php`: Categorías
- `marca.php`: Marcas
- `devolucion.php`: Devoluciones
- `notificaciones.php`: Notificaciones
- `contratoConvenio.php`: Contratos y convenios

### 4.2. Registro de Rutas

Las rutas se registran en `routes/web.php` mediante:

```php
Route::middleware(['auth', 'verified'])->group(function () {
    // ... otras rutas
    require __DIR__ . '/inventario/dashboard.php';
    require __DIR__ . '/inventario/productos.php';
    // ... más archivos
});
```

### 4.3. Prefijo y Nombres

Todas las rutas del módulo usan:

- **Prefijo:** `inventario`
- **Nombre base:** `inventario.` (excepto carrito que usa `inventario.carrito.`)

### 4.4. Rutas de Dashboard

**Archivo:** `routes/inventario/dashboard.php`

```php
Route::prefix('inventario')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('inventario.dashboard');
});
```

**Rutas:**
- `GET /inventario/dashboard` → `inventario.dashboard`

---

### 4.5. Rutas de Productos

**Archivo:** `routes/inventario/productos.php`

**Rutas Especiales:**
- `GET /inventario/productos/catalogo` → `inventario.productos.catalogo`
- `GET /inventario/productos/index` → `inventario.productos.index`
- `GET /inventario/productos/buscar` → `inventario.productos.buscar`
- `POST /inventario/productos/agregar-carrito` → `inventario.productos.agregar-carrito`
- `GET /inventario/productos/detalles/{id}` → `inventario.productos.detalles`
- `GET /inventario/productos/buscar/{codigo}` → `inventario.productos.buscar-codigo`
- `GET /inventario/productos/{id}/etiqueta` → `inventario.productos.etiqueta`

**Rutas Resource:**
- `GET /inventario/productos` → `inventario.productos.index` (duplicado con ruta especial)
- `GET /inventario/productos/create` → `inventario.productos.create`
- `POST /inventario/productos` → `inventario.productos.store`
- `GET /inventario/productos/{producto}` → `inventario.productos.show`
- `GET /inventario/productos/{producto}/edit` → `inventario.productos.edit`
- `PUT/PATCH /inventario/productos/{producto}` → `inventario.productos.update`
- `DELETE /inventario/productos/{producto}` → `inventario.productos.destroy`

---

### 4.6. Rutas de Órdenes

**Archivo:** `routes/inventario/ordenes.php`

**Rutas Especiales:**
- `GET /inventario/ordenes/prestamos-salidas` → `inventario.prestamos-salidas`
- `POST /inventario/ordenes/prestamos-salidas` → `inventario.prestamos-salidas.store`
- `GET /inventario/ordenes/pendientes` → `inventario.ordenes.pendientes`
- `GET /inventario/ordenes/completadas` → `inventario.ordenes.completadas`
- `GET /inventario/ordenes/rechazadas` → `inventario.ordenes.rechazadas`

**Rutas Resource:**
- `GET /inventario/ordenes` → `inventario.ordenes.index`
- `GET /inventario/ordenes/{orden}` → `inventario.ordenes.show`
- `PUT /inventario/ordenes/{orden}` → `inventario.ordenes.update`
- `DELETE /inventario/ordenes/{orden}` → `inventario.ordenes.destroy`

**Rutas de Aprobaciones** (con middleware `can:APROBAR ORDEN`):
- `GET /inventario/aprobaciones/pendientes` → `inventario.aprobaciones.pendientes`
- `POST /inventario/aprobaciones/{detalleOrden}/aprobar` → `inventario.aprobaciones.aprobar`
- `POST /inventario/aprobaciones/{detalleOrden}/rechazar` → `inventario.aprobaciones.rechazar`
- `POST /inventario/aprobaciones/orden/{orden}/aprobar` → `inventario.aprobaciones.aprobar-orden`
- `POST /inventario/aprobaciones/orden/{orden}/rechazar` → `inventario.aprobaciones.rechazar-orden`

---

### 4.7. Rutas de Carrito

**Archivo:** `routes/inventario/carrito.php`

**Rutas:**
- `GET /inventario/carrito-sena` → `inventario.carrito.ecommerce`
- `POST /inventario/carrito/agregar` → `inventario.carrito.agregar`
- `PUT /inventario/carrito/actualizar/{id}` → `inventario.carrito.actualizar`
- `DELETE /inventario/carrito/eliminar/{id}` → `inventario.carrito.eliminar`
- `POST /inventario/carrito/vaciar` → `inventario.carrito.vaciar`
- `GET /inventario/carrito/contenido` → `inventario.carrito.contenido`

---

### 4.8. Rutas de Proveedores

**Archivo:** `routes/inventario/proveedor.php`

**Rutas Especiales:**
- `GET /inventario/proveedores/municipios/{departamentoId}` → `inventario.proveedores.municipios`

**Rutas Resource:**
- `GET /inventario/proveedores` → `inventario.proveedores.index`
- `GET /inventario/proveedores/create` → `inventario.proveedores.create`
- `POST /inventario/proveedores` → `inventario.proveedores.store`
- `GET /inventario/proveedores/{proveedor}` → `inventario.proveedores.show`
- `GET /inventario/proveedores/{proveedor}/edit` → `inventario.proveedores.edit`
- `PUT/PATCH /inventario/proveedores/{proveedor}` → `inventario.proveedores.update`
- `DELETE /inventario/proveedores/{proveedor}` → `inventario.proveedores.destroy`

**Nota:** El parámetro de ruta se llama `proveedor` (no `proveedores`).

---

### 4.9. Rutas de Categorías

**Archivo:** `routes/inventario/categoria.php`

**Rutas Resource:**
- `GET /inventario/categorias` → `inventario.categorias.index`
- `GET /inventario/categorias/create` → `inventario.categorias.create`
- `POST /inventario/categorias` → `inventario.categorias.store`
- `GET /inventario/categorias/{categoria}` → `inventario.categorias.show`
- `GET /inventario/categorias/{categoria}/edit` → `inventario.categorias.edit`
- `PUT/PATCH /inventario/categorias/{categoria}` → `inventario.categorias.update`
- `DELETE /inventario/categorias/{categoria}` → `inventario.categorias.destroy`

---

### 4.10. Rutas de Marcas

**Archivo:** `routes/inventario/marca.php`

**Rutas Resource:** Similar a categorías (reemplazar `categorias` por `marcas`).

---

### 4.11. Rutas de Devoluciones

**Archivo:** `routes/inventario/devolucion.php`

**Rutas:**
- `GET /inventario/devoluciones` → `inventario.devoluciones.index`
- `GET /inventario/devoluciones/create/{detalleOrden}` → `inventario.devoluciones.create`
- `POST /inventario/devoluciones` → `inventario.devoluciones.store`
- `GET /inventario/devoluciones/{devolucion}` → `inventario.devoluciones.show`
- `GET /inventario/devoluciones-historial` → `inventario.devoluciones.historial`
- `GET /inventario/mis-prestamos` → `inventario.prestamos.mis`
- `GET /inventario/historial-prestamos` → `inventario.prestamos.historial`

---

### 4.12. Rutas de Notificaciones

**Archivo:** `routes/inventario/notificaciones.php`

**Rutas:**
- `GET /inventario/notificaciones` → `inventario.notificaciones.index`
- `GET /inventario/notificaciones/unread` → `inventario.notificaciones.unread`
- `POST /inventario/notificaciones/{id}/read` → `inventario.notificaciones.read`
- `POST /inventario/notificaciones/read-all` → `inventario.notificaciones.read-all`
- `DELETE /inventario/notificaciones/vaciar-todas` → `inventario.notificaciones.destroy-all`
- `DELETE /inventario/notificaciones/{id}` → `inventario.notificaciones.destroy`

---

### 4.13. Rutas de Contratos y Convenios

**Archivo:** `routes/inventario/contratoConvenio.php`

**Rutas Resource:**
- `GET /inventario/contratos-convenios` → `inventario.contratos-convenios.index`
- `GET /inventario/contratos-convenios/create` → `inventario.contratos-convenios.create`
- `POST /inventario/contratos-convenios` → `inventario.contratos-convenios.store`
- `GET /inventario/contratos-convenios/{contratoConvenio}` → `inventario.contratos-convenios.show`
- `GET /inventario/contratos-convenios/{contratoConvenio}/edit` → `inventario.contratos-convenios.edit`
- `PUT/PATCH /inventario/contratos-convenios/{contratoConvenio}` → `inventario.contratos-convenios.update`
- `DELETE /inventario/contratos-convenios/{contratoConvenio}` → `inventario.contratos-convenios.destroy`

**Nota:** El parámetro de ruta se llama `contratoConvenio` (no `contratos-convenios`).

---

## 5. Buenas Prácticas

### 5.1. Controladores

- **Mantener delgados**: Los controladores solo orquestan, no contienen lógica de negocio
- **Inyección de dependencias**: Siempre inyectar interfaces, no implementaciones
- **Manejo de excepciones**: Capturar excepciones específicas y retornar mensajes claros
- **Validación de existencia**: Verificar que los recursos existan antes de operar
- **Redirecciones consistentes**: Usar `route()` para generar URLs
- **Mensajes de sesión**: Usar `with('success')` o `with('error')` para feedback

### 5.2. Form Requests

- **Validación centralizada**: Toda validación debe estar en Form Requests
- **Reglas condicionales**: Usar `isMethod()` o `routeIs()` para reglas diferentes
- **Mensajes personalizados**: Proporcionar mensajes claros con `messages()`
- **Autorización**: Usar `authorize()` si es necesario (aunque en este módulo se usa middleware)

### 5.3. Rutas

- **Organización modular**: Separar rutas por funcionalidad en archivos distintos
- **Nombres descriptivos**: Usar nombres claros y consistentes
- **Rutas Resource**: Usar `Route::resource()` cuando aplique
- **Rutas especiales antes de Resource**: Definir rutas especiales antes de `resource()` para evitar conflictos
- **Middlewares**: Aplicar middlewares a nivel de grupo cuando sea posible
- **Parámetros de ruta**: Usar nombres consistentes (singular para modelos)

---

**Fin de la Parte 6**

*Continúa en la Parte 7: Vistas y Componentes Livewire*

