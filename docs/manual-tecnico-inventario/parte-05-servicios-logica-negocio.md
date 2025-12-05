# Manual Técnico - Módulo de Inventario
## Parte 5: Servicios y Lógica de Negocio

---

## 1. Introducción a los Servicios

### 1.1. Patrón Service Layer

El módulo de Inventario utiliza el patrón Service Layer para centralizar la lógica de negocio y separarla de los controladores. Los servicios actúan como intermediarios entre los controladores y los repositories, implementando reglas de negocio, validaciones y orquestación de operaciones complejas.

### 1.2. Principios Aplicados

- **Single Responsibility Principle (SRP)**: Cada servicio tiene una responsabilidad única
- **Dependency Inversion Principle (DIP)**: Los servicios dependen de interfaces, no de implementaciones
- **Open/Closed Principle (OCP)**: Los servicios son extensibles mediante interfaces
- **Separation of Concerns**: La lógica de negocio está separada de la presentación y acceso a datos

### 1.3. Ubicación

Los servicios están ubicados en `app/Inventario/Services/` organizados por funcionalidad:

- **Servicios Principales**: Producto, Orden, Carrito, Proveedor, Categoria, Marca, ContratoConvenio, Devolucion, Aprobacion
- **Servicios Auxiliares**: StockValidator, Barcode, Image, FormOptions, FormData, ProductoEnrichment, Notification, UserNotification, Transaction

---

## 2. Servicio: ProductoService

### 2.1. Ubicación

`app/Inventario/Services/Producto/ProductoService.php`

### 2.2. Dependencias

- `ProductoRepositoryInterface`: Acceso a datos de productos
- `ImageServiceInterface`: Procesamiento de imágenes
- `BarcodeServiceInterface`: Generación y validación de códigos de barras
- `StockValidatorServiceInterface`: Validación de stock

### 2.3. Métodos Principales

#### crear(array $datos, int $userId) : Producto

Crea un nuevo producto con procesamiento de imagen y código de barras.

**Proceso:**
1. Resuelve código de barras (genera si no se proporciona)
2. Procesa imagen (usa imagen por defecto si no se proporciona)
3. Asigna usuarios de auditoría
4. Crea el producto en la base de datos

**Ejemplo:**
```php
$producto = $productoService->crear([
    'producto' => 'Laptop Dell',
    'tipo_producto_id' => 1,
    'descripcion' => 'Laptop para laboratorio',
    'cantidad' => 10,
    // ... más campos
], Auth::id());
```

#### actualizar(Producto $producto, array $datos, int $userId) : Producto

Actualiza un producto existente con validaciones y notificaciones.

**Proceso:**
1. Guarda cantidad anterior para comparación
2. Procesa nueva imagen si se proporciona
3. Normaliza o genera código de barras si se modifica
4. Actualiza el producto
5. Verifica cambios de stock y notifica si es necesario

**Características:**
- Mantiene imagen actual si no se envía nueva
- Valida y notifica cambios de stock automáticamente
- Actualiza usuario de auditoría

#### eliminar(Producto $producto) : bool

Elimina un producto y su imagen asociada.

**Proceso:**
1. Elimina la imagen del sistema de archivos (si existe)
2. Elimina el producto de la base de datos

---

## 3. Servicio: OrdenService

### 3.1. Ubicación

`app/Inventario/Services/Orden/OrdenService.php`

### 3.2. Dependencias

- `OrdenRepositoryInterface`
- `DetalleOrdenRepositoryInterface`
- `ProductoRepositoryInterface`
- `NotificationServiceInterface`
- `TransactionServiceInterface`
- `StockValidatorServiceInterface`

### 3.3. Métodos Principales

#### crear(array $datos, int $userId) : Orden

Crea una nueva orden con sus detalles de productos.

**Proceso:**
1. Inicia transacción
2. Crea la orden
3. Procesa cada producto del array `productos`:
   - Valida existencia del producto
   - Valida stock suficiente
   - Crea detalle de orden
   - Descuenta stock del producto
4. Confirma transacción

**Estructura de datos esperada:**
```php
[
    'descripcion_orden' => 'Solicitud de materiales',
    'tipo_orden_id' => 1,
    'fecha_devolucion' => '2025-12-31', // Opcional
    'productos' => [
        [
            'producto_id' => 1,
            'cantidad' => 5,
            'estado_orden_id' => 1
        ],
        // ... más productos
    ]
]
```

#### crearDesdeCarrito(array $datos, int $userId) : Orden

Crea una orden desde el carrito de compras (e-commerce).

**Proceso:**
1. Decodifica JSON del carrito
2. Valida que el carrito no esté vacío
3. Obtiene tipo de orden (PRÉSTAMO o SALIDA)
4. Genera descripción detallada con información del usuario
5. Crea la orden
6. Procesa cada item del carrito:
   - Valida existencia del producto
   - Valida stock suficiente
   - Crea detalle de orden
7. Notifica a administradores
8. Limpia el carrito de la sesión
9. Confirma transacción

**Estructura de datos esperada:**
```php
[
    'carrito' => json_encode([...]),
    'tipo' => 'prestamo' | 'salida',
    'fecha_devolucion' => '2025-12-31', // Si es préstamo
    'descripcion' => 'Motivo de la solicitud',
    'rol' => 'Instructor',
    'programa_formacion' => 'Técnico en Sistemas'
]
```

#### actualizar(Orden $orden, array $datos, int $userId) : Orden

Actualiza una orden existente.

**Proceso:**
1. Inicia transacción
2. Devuelve stock de productos anteriores
3. Elimina detalles anteriores
4. Actualiza datos de la orden
5. Procesa nuevos productos (valida stock y descuenta)
6. Confirma transacción

**Nota:** Este método devuelve el stock antes de aplicar los nuevos detalles.

#### eliminar(Orden $orden) : bool

Elimina una orden y devuelve el stock de todos los productos.

**Proceso:**
1. Inicia transacción
2. Devuelve stock de todos los productos
3. Elimina detalles de orden
4. Elimina la orden
5. Confirma transacción

#### obtenerParametroTipoOrden(string $codigo) : Parametro

Obtiene el parámetro de tipo de orden por código (PRÉSTAMO, SALIDA).

#### obtenerEstadoEnEspera() : Parametro

Obtiene el estado "EN ESPERA" del sistema de parámetros.

#### obtenerEstadoAprobada() : Parametro

Obtiene el estado "APROBADA" del sistema de parámetros.

#### tieneDevoluciones(Orden $orden) : bool

Verifica si una orden tiene devoluciones registradas.

---

## 4. Servicio: CarritoService

### 4.1. Ubicación

`app/Inventario/Services/Carrito/CarritoService.php`

### 4.2. Dependencias

- `ProductoRepositoryInterface`

### 4.3. Métodos Principales

#### verificarDisponibilidad(array $items) : array

Verifica la disponibilidad de stock para todos los items del carrito.

**Retorna:** Array de errores si hay problemas de stock:
```php
[
    [
        'producto' => 'Nombre del producto',
        'solicitado' => 10,
        'disponible' => 5
    ],
    // ... más errores
]
```

**Lanza:** `CarritoException` si algún producto no existe.

#### validarItem(int $productoId, int $cantidad) : array

Valida un item individual del carrito.

**Retorna:**
```php
// Si es válido
[
    'success' => true,
    'message' => 'Cantidad válida',
    'producto' => [...]
]

// Si no hay stock
[
    'success' => false,
    'message' => 'Stock insuficiente',
    'stock_disponible' => 5
]
```

#### obtenerProductosParaCarrito(array $items) : Collection

Obtiene información completa de productos para el carrito.

**Retorna:** Colección con:
- `id`, `nombre`, `codigo`, `imagen`, `stock`
- `categoria`, `marca`, `descripcion`

---

## 5. Servicio: ProveedorService

### 5.1. Ubicación

`app/Inventario/Services/Proveedor/ProveedorService.php`

### 5.2. Dependencias

- `ProveedorRepositoryInterface`

### 5.3. Métodos Principales

#### crear(array $datos, int $userId) : Proveedor

Crea un nuevo proveedor con auditoría.

#### actualizar(Proveedor $proveedor, array $datos, int $userId) : bool

Actualiza un proveedor existente.

#### eliminar(Proveedor $proveedor) : bool

Elimina un proveedor si no está en uso.

**Validaciones:**
- Verifica que no tenga contratos asociados
- Verifica que no tenga productos asociados

**Lanza:** `ProveedorException` si el proveedor está en uso.

---

## 6. Servicio: CategoriaService

### 6.1. Ubicación

`app/Inventario/Services/Categoria/CategoriaService.php`

### 6.2. Dependencias

- `CategoriaRepositoryInterface`

### 6.3. Métodos Principales

#### crear(array $datos, int $userId) : Categoria

Crea una nueva categoría y la asocia al tema "CATEGORIAS".

**Proceso:**
1. Crea el parámetro (categoría)
2. Asocia al tema "CATEGORIAS" mediante `asociarATemaCategorias()`

**Lanza:** `CategoriaException` si hay error en la base de datos.

#### actualizar(Categoria $categoria, array $datos, int $userId) : bool

Actualiza una categoría (convierte nombre a mayúsculas automáticamente).

#### eliminar(Categoria $categoria) : bool

Elimina una categoría si no está en uso.

**Validaciones:**
- Verifica que no tenga productos asociados
- Verifica que exista el tema "CATEGORIAS"

**Lanza:** `CategoriaException` si la categoría está en uso o no existe el tema.

---

## 7. Servicio: MarcaService

### 7.1. Ubicación

`app/Inventario/Services/Marca/MarcaService.php`

### 7.2. Dependencias

- `MarcaRepositoryInterface`

### 7.3. Métodos Principales

#### crear(array $datos, int $userId) : Marca

Crea una nueva marca y la asocia al tema "MARCAS".

**Proceso:** Similar a `CategoriaService::crear()`

#### actualizar(Marca $marca, array $datos, int $userId) : bool

Actualiza una marca (convierte nombre a mayúsculas automáticamente).

#### eliminar(Marca $marca) : bool

Elimina una marca si no está en uso.

**Validaciones:**
- Verifica que no tenga productos asociados
- Verifica que exista el tema "MARCAS"

**Lanza:** `MarcaException` si la marca está en uso o no existe el tema.

---

## 8. Servicio: ContratoConvenioService

### 8.1. Ubicación

`app/Inventario/Services/ContratoConvenio/ContratoConvenioService.php`

### 8.2. Dependencias

- `ContratoConvenioRepositoryInterface`

### 8.3. Métodos Principales

#### crear(array $datos, int $userId) : ContratoConvenio

Crea un nuevo contrato o convenio.

#### actualizar(ContratoConvenio $contrato, array $datos, int $userId) : bool

Actualiza un contrato existente.

#### eliminar(ContratoConvenio $contrato) : bool

Elimina un contrato si no está en uso.

**Validaciones:**
- Verifica que no tenga productos asociados

**Lanza:** `ContratoConvenioException` si el contrato está en uso.

---

## 9. Servicio: DevolucionService

### 9.1. Ubicación

`app/Inventario/Services/Devolucion/DevolucionService.php`

### 9.2. Dependencias

- `TransactionServiceInterface`

### 9.3. Métodos Principales

#### registrarDevolucionConMensaje(int $detalleOrdenId, int $cantidadDevuelta, ?string $observaciones) : array

Registra una devolución y retorna un mensaje informativo.

**Proceso:**
1. Inicia transacción
2. Llama a `Devolucion::registrarDevolucion()` (método estático del modelo)
3. Confirma transacción
4. Construye mensaje con información de la devolución

**Retorna:**
```php
[
    'devolucion' => Devolucion,
    'mensaje' => 'Devolución registrada exitosamente...'
]
```

**Mensaje incluye:**
- Confirmación de registro
- Nota si fue cierre sin stock
- Días de retraso (si aplica)

#### construirMensajeDevolucion(Devolucion $devolucion) : string

Construye mensaje informativo sobre la devolución.

#### obtenerEstadoAprobada() : ParametroTema

Obtiene el estado "APROBADA" como ParametroTema (necesario para el repositorio).

---

## 10. Servicio: AprobacionService

### 10.1. Ubicación

`app/Inventario/Services/Aprobacion/AprobacionService.php`

### 10.2. Dependencias

- `AprobacionRepositoryInterface`
- `DetalleOrdenRepositoryInterface`
- `OrdenRepositoryInterface`
- `ProductoRepositoryInterface`
- `TransactionServiceInterface`
- `StockValidatorServiceInterface`
- `FormOptionsServiceInterface`

### 10.3. Métodos Principales

#### aprobarDetalle(DetalleOrden $detalleOrden) : void

Aprueba un detalle de orden individual.

**Proceso:**
1. Inicia transacción
2. Valida que el detalle esté pendiente
3. Valida stock suficiente
4. Actualiza estado del detalle a "APROBADA"
5. Crea registro de aprobación
6. Descuenta stock del producto
7. Notifica al solicitante
8. Confirma transacción

**Validaciones:**
- El detalle debe estar en estado "EN ESPERA"
- No debe tener aprobación previa
- Debe haber stock suficiente

**Lanza:** `AprobacionException` si alguna validación falla.

#### rechazarDetalle(DetalleOrden $detalleOrden, string $motivoRechazo) : void

Rechaza un detalle de orden individual.

**Proceso:**
1. Inicia transacción
2. Valida que el detalle esté pendiente
3. Actualiza estado del detalle a "RECHAZADA"
4. Crea registro de aprobación con estado rechazado
5. Actualiza descripción de la orden con motivo de rechazo
6. Notifica al solicitante
7. Confirma transacción

#### aprobarOrdenCompleta(Orden $orden) : void

Aprueba todos los detalles pendientes de una orden.

**Proceso:**
1. Inicia transacción
2. Obtiene detalles pendientes
3. Valida stock de todos los productos antes de procesar
4. Para cada detalle pendiente:
   - Actualiza estado a "APROBADA"
   - Crea registro de aprobación
   - Descuenta stock
5. Notifica al solicitante (una notificación por detalle)
6. Confirma transacción

#### rechazarOrdenCompleta(Orden $orden, string $motivoRechazo) : void

Rechaza todos los detalles pendientes de una orden.

**Proceso:** Similar a `rechazarDetalle()` pero para todos los detalles pendientes.

#### obtenerDetallesPendientes() : Collection

Obtiene todos los detalles de orden pendientes de aprobación.

#### encontrarDetalleConRelaciones(int $detalleOrdenId) : ?DetalleOrden

Encuentra un detalle de orden con todas sus relaciones.

#### encontrarOrdenConDetallesYDevoluciones(int $ordenId) : ?Orden

Encuentra una orden con detalles y devoluciones.

---

## 11. Servicios Auxiliares

### 11.1. StockValidatorService

**Ubicación:** `app/Inventario/Services/StockValidator/StockValidatorService.php`

**Responsabilidad:** Validación de stock y notificaciones de stock bajo.

#### Métodos Principales

- **estaBajoUmbralMinimo(Producto $producto) : bool**: Verifica si el stock está bajo el umbral mínimo
- **estaNivelCritico(Producto $producto) : bool**: Verifica si el stock está en nivel crítico
- **hayStockSuficiente(Producto $producto, int $cantidadRequerida) : bool**: Verifica disponibilidad
- **validarStockSuficiente(Producto $producto, int $cantidadRequerida) : void**: Valida y lanza excepción si no hay stock
- **verificarYNotificarCambioStock(Producto $producto, int $cantidadAnterior) : void**: Verifica cambios y notifica si es necesario
- **obtenerNivelStock(Producto $producto) : string**: Retorna nivel: 'critico', 'bajo', 'normal', 'alto'

**Configuración:**
- Umbral mínimo: `config('inventario.stock.umbral_minimo', 10)`
- Umbral crítico: `config('inventario.stock.umbral_critico', 5)`

### 11.2. BarcodeService

**Ubicación:** `app/Inventario/Services/Barcode/BarcodeService.php`

**Responsabilidad:** Generación y normalización de códigos de barras.

#### Métodos Principales

- **resolverCodigoBarras(?string $codigo) : string**: Resuelve o genera código de barras
- **generarSiguienteCodigoBarras() : string**: Genera el siguiente código disponible
- **normalizarCodigoBarras(?string $codigo) : ?string**: Normaliza código a formato válido

**Configuración:**
- Longitud: `config('inventario.codigo_barras.longitud_auto', 11)`

### 11.3. ImageService

**Ubicación:** `app/Inventario/Services/Image/ImageService.php`

**Responsabilidad:** Procesamiento y gestión de imágenes de productos.

#### Métodos Principales

- **procesarImagen(?UploadedFile $imagen) : string**: Procesa imagen nueva (retorna ruta o imagen por defecto)
- **procesarImagenParaActualizacion(?UploadedFile $imagen, Producto $producto) : string**: Procesa imagen para actualización (mantiene actual si no se envía nueva)
- **eliminarImagenSiExiste(Producto $producto) : void**: Elimina imagen del sistema de archivos

**Configuración:**
- Directorio: `config('inventario.imagenes.directorio', 'imagenes_productos')`
- Imagen por defecto: `config('inventario.imagenes.default', 'img/inventario/producto-default.png')`

### 11.4. FormOptionsService

**Ubicación:** `app/Inventario/Services/FormOptions/FormOptionsService.php`

**Responsabilidad:** Obtención de opciones para formularios.

#### Métodos Principales

- **obtenerOpcionesProducto(?string $temaEstados = null) : array**: Opciones completas para formularios de productos
- **obtenerOpcionesOrden() : array**: Opciones para formularios de órdenes
- **obtenerTiposProducto() : Collection**: Tipos de productos
- **obtenerUnidadesMedida() : Collection**: Unidades de medida
- **obtenerEstados(string $tema) : Collection**: Estados por tema
- **obtenerCategorias() : Collection**: Categorías
- **obtenerMarcas() : Collection**: Marcas
- **obtenerTiposOrden() : Collection**: Tipos de orden
- **obtenerEstadosOrden() : Collection**: Estados de orden
- **obtenerEstadoAgotado(?string $temaEstados = null) : ?ParametroTema**: Estado "AGOTADO"
- **obtenerEstadoOrdenPorNombre(string $nombreEstado, ?string $temaEstados = null) : ?ParametroTema**: Estado por nombre

### 11.5. FormDataService

**Ubicación:** `app/Inventario/Services/FormData/FormDataService.php`

**Responsabilidad:** Obtención de datos para formularios (contratos, ambientes, proveedores).

#### Métodos Principales

- **obtenerDatosFormulario() : array**: Retorna contratos, ambientes y proveedores
- **obtenerContratosConvenios() : Collection**: Contratos y convenios
- **obtenerAmbientes() : Collection**: Ambientes disponibles
- **obtenerProveedores() : Collection**: Proveedores disponibles

### 11.6. ProductoEnrichmentService

**Ubicación:** `app/Inventario/Services/ProductoEnrichment/ProductoEnrichmentService.php`

**Responsabilidad:** Enriquecimiento de productos con relaciones (marcas y categorías).

#### Métodos Principales

- **enriquecerConMarcasYCategorias(iterable|LengthAwarePaginator $productos) : void**: Enriquece colección de productos
- **enriquecerProducto(Producto $producto) : void**: Enriquece un producto individual

**Optimización:** Carga todas las marcas y categorías en una sola consulta para evitar N+1.

### 11.7. NotificationService

**Ubicación:** `app/Inventario/Services/Notification/NotificationService.php`

**Responsabilidad:** Notificaciones del sistema (nuevas órdenes, stock bajo).

#### Métodos Principales

- **notificarNuevaOrden(Orden $orden) : void**: Notifica a super administradores sobre nueva orden
- **notificarStockBajo(Producto $producto, int $cantidad, int $umbral) : void**: Notifica stock bajo a administradores

### 11.8. UserNotificationService

**Ubicación:** `app/Inventario/Services/Notification/UserNotificationService.php`

**Responsabilidad:** Gestión de notificaciones de usuario.

#### Métodos Principales

- **obtenerNotificacionesPaginadas(int $userId, int $perPage = null) : LengthAwarePaginator**: Notificaciones paginadas
- **obtenerNoLeidas(int $userId, int $limit = null) : Collection**: Notificaciones no leídas
- **contarNoLeidas(int $userId) : int**: Contador de no leídas
- **marcarComoLeida(int $userId, string $notificationId) : bool**: Marca una como leída
- **marcarTodasComoLeidas(int $userId) : int**: Marca todas como leídas
- **eliminar(int $userId, string $notificationId) : bool**: Elimina una notificación
- **obtenerDatosDropdown(int $userId) : array**: Datos para dropdown de notificaciones

**Configuración:**
- Por página: `config('inventario.notificaciones.per_page', 10)`
- Límite dropdown: `config('inventario.notificaciones.dropdown_limit', 5)`

### 11.9. TransactionService

**Ubicación:** `app/Inventario/Services/Transaction/TransactionService.php`

**Responsabilidad:** Gestión de transacciones de base de datos.

#### Métodos Principales

- **beginTransaction() : void**: Inicia transacción
- **commit() : void**: Confirma transacción
- **rollBack() : void**: Revierte transacción
- **transaction(callable $callback)**: Ejecuta callback dentro de transacción

**Uso:** Abstrae el manejo de transacciones para facilitar testing y cambios de implementación.

---

## 12. Flujos de Negocio Principales

### 12.1. Flujo: Crear Orden desde Carrito

```
1. Usuario agrega productos al carrito
2. Usuario completa formulario de solicitud
3. CarritoService valida disponibilidad
4. OrdenService::crearDesdeCarrito():
   - Crea orden
   - Valida stock de cada producto
   - Crea detalles de orden
   - Notifica a administradores
   - Limpia carrito
5. Administrador recibe notificación
```

### 12.2. Flujo: Aprobar Orden

```
1. Administrador revisa orden pendiente
2. AprobacionService::aprobarDetalle():
   - Valida que esté pendiente
   - Valida stock suficiente
   - Actualiza estado a "APROBADA"
   - Descuenta stock del producto
   - Crea registro de aprobación
   - Notifica al solicitante
3. Solicitante recibe notificación de aprobación
```

### 12.3. Flujo: Registrar Devolución

```
1. Usuario solicita devolver producto
2. DevolucionService::registrarDevolucionConMensaje():
   - Valida cantidad pendiente
   - Valida cierre sin stock (si cantidad = 0)
   - Crea registro de devolución
   - Restaura stock (si no es cierre sin stock)
   - Calcula días de retraso
3. Sistema actualiza estado del préstamo
```

### 12.4. Flujo: Actualizar Producto con Cambio de Stock

```
1. Usuario actualiza producto
2. ProductoService::actualizar():
   - Procesa imagen si se envía nueva
   - Actualiza datos del producto
   - StockValidatorService::verificarYNotificarCambioStock():
     - Compara cantidad anterior vs nueva
     - Si bajó de umbral mínimo, notifica
3. Administradores reciben notificación de stock bajo
```

---

## 13. Manejo de Excepciones

### 13.1. Excepciones Personalizadas

El módulo utiliza excepciones personalizadas para errores específicos:

- **OrdenException**: Errores en creación/actualización de órdenes
- **CarritoException**: Errores en validación del carrito
- **ProveedorException**: Errores en gestión de proveedores
- **CategoriaException**: Errores en gestión de categorías
- **MarcaException**: Errores en gestión de marcas
- **ContratoConvenioException**: Errores en gestión de contratos
- **DevolucionException**: Errores en devoluciones
- **AprobacionException**: Errores en aprobaciones
- **StockException**: Errores de stock insuficiente

### 13.2. Uso de Transacciones

Todos los servicios que modifican múltiples registros utilizan transacciones:

```php
try {
    $this->transactionService->beginTransaction();
    // Operaciones...
    $this->transactionService->commit();
} catch (\Exception $e) {
    $this->transactionService->rollBack();
    throw new CustomException($e->getMessage());
}
```

---

## 14. Buenas Prácticas

### 14.1. Inyección de Dependencias

Siempre inyectar interfaces, no implementaciones:

```php
// ✅ Correcto
public function __construct(
    private ProductoRepositoryInterface $repository
) {}

// ❌ Incorrecto
public function __construct(
    private ProductoRepository $repository
) {}
```

### 14.2. Validaciones en Servicios

Las validaciones de negocio deben estar en servicios, no en controladores:

```php
// ✅ Correcto
if ($this->repository->tieneProductos($id)) {
    throw new Exception('No se puede eliminar');
}

// ❌ Incorrecto (en controlador)
if ($proveedor->productos()->count() > 0) {
    return redirect()->back()->withErrors(...);
}
```

### 14.3. Uso de Transacciones

Usar transacciones para operaciones que modifican múltiples registros:

```php
$this->transactionService->beginTransaction();
try {
    // Múltiples operaciones
    $this->transactionService->commit();
} catch (\Exception $e) {
    $this->transactionService->rollBack();
    throw $e;
}
```

### 14.4. Delegación de Responsabilidades

Los servicios deben delegar responsabilidades específicas a otros servicios:

```php
// ProductoService delega validación de stock
$this->stockValidator->verificarYNotificarCambioStock($producto, $cantidadAnterior);

// OrdenService delega notificaciones
$this->notificationService->notificarNuevaOrden($orden);
```

---

**Fin de la Parte 5**

*Continúa en la Parte 6: Controladores, Form Requests y Rutas*

