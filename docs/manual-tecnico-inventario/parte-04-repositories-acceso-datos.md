# Manual Técnico - Módulo de Inventario
## Parte 4: Repositories y Acceso a Datos

---

## 1. Introducción a los Repositories

### 1.1. Patrón Repository

El módulo de Inventario utiliza el patrón Repository para abstraer el acceso a datos y separar la lógica de negocio de la lógica de acceso a datos. Todos los repositories implementan interfaces que definen los contratos de métodos disponibles.

### 1.2. Ubicación

Los repositories están ubicados en `app/Inventario/Repositories/` organizados por entidad:

- `Producto/ProductoRepository.php`
- `Orden/OrdenRepository.php`
- `Orden/DetalleOrdenRepository.php`
- `Proveedor/ProveedorRepository.php`
- `Categoria/CategoriaRepository.php`
- `Marca/MarcaRepository.php`
- `ContratoConvenio/ContratoConvenioRepository.php`
- `Devolucion/DevolucionRepository.php`
- `Aprobacion/AprobacionRepository.php`
- `Dashboard/DashboardRepository.php`
- `Notification/NotificationRepository.php`
- `User/UserRepository.php`

### 1.3. Interfaces

Cada repository implementa una interfaz correspondiente ubicada en `app/Inventario/Interfaces/Repositories/`. Esto permite:

- Desacoplamiento entre capas
- Facilidad para crear mocks en tests
- Inversión de dependencias
- Cambios de implementación sin afectar servicios

---

## 2. Repository: ProductoRepository

### 2.1. Ubicación

`app/Inventario/Repositories/Producto/ProductoRepository.php`

### 2.2. Interfaz

`App\Inventario\Interfaces\Repositories\Producto\ProductoRepositoryInterface`

### 2.3. Métodos Principales

#### obtenerConFiltros(array $filtros = []) : LengthAwarePaginator

Obtiene productos paginados con filtros y relaciones cargadas.

**Filtros soportados:**
- `search`: Búsqueda en nombre, código de barras o descripción
- `tipo_producto_id`: Filtrar por tipo de producto
- `categoria_id`: Filtrar por categoría
- `stock_minimo`: Filtrar por stock mínimo
- `solo_con_stock`: Solo productos con stock > 0
- `per_page`: Elementos por página (default: 10)

**Relaciones cargadas:**
- `tipoProducto.parametro`
- `unidadMedida.parametro`
- `estado.parametro`
- `contratoConvenio`
- `ambiente`
- `proveedor`

#### encontrarConRelaciones(int $id) : ?Producto

Encuentra un producto por ID con todas sus relaciones cargadas.

#### buscarPorCodigoBarras(string $codigo) : ?Producto

Busca un producto por su código de barras.

#### obtenerParaCatalogo(array $filtros = []) : LengthAwarePaginator

Obtiene productos para el catálogo e-commerce con filtros específicos.

**Características:**
- Solo productos con stock > 0
- Filtro por estado agotado
- Ordenamiento configurable (stock, fecha, nombre)
- Paginación de 12 elementos por defecto

#### buscarParaAjax(array $filtros = []) : Collection

Busca productos para respuestas AJAX (sin paginación).

#### obtenerTiposProductos() : Collection

Obtiene todos los tipos de productos activos ordenados alfabéticamente.

#### encontrar(int $id) : ?Producto

Encuentra un producto por ID sin relaciones.

#### crear(array $datos) : Producto

Crea un nuevo producto.

#### actualizar(Producto $producto, array $datos) : bool

Actualiza un producto existente.

#### eliminar(Producto $producto) : bool

Elimina un producto.

#### actualizarStock(Producto $producto, int $cantidad) : bool

Actualiza el stock de un producto.

#### obtenerMaxCodigoBarras() : ?string

Obtiene el código de barras máximo (útil para generar nuevos códigos).

#### existeCodigoBarras(string $codigo) : bool

Verifica si existe un código de barras en la base de datos.

---

## 3. Repository: OrdenRepository

### 3.1. Ubicación

`app/Inventario/Repositories/Orden/OrdenRepository.php`

### 3.2. Interfaz

`App\Inventario\Interfaces\Repositories\Orden\OrdenRepositoryInterface`

### 3.3. Métodos Principales

#### obtenerConFiltros(array $filtros = []) : LengthAwarePaginator

Obtiene órdenes paginadas con filtros avanzados.

**Filtros soportados:**
- `search`: Búsqueda en descripción, usuario creador, tipo de orden, productos
- `tipo_orden_id`: Filtrar por tipo de orden
- `estado_id`: Filtrar por estado de los detalles
- `per_page`: Elementos por página (default: 15)

**Búsqueda incluye:**
- Descripción de la orden
- Nombre del usuario creador
- Tipo de orden
- Nombre y código de barras de productos

#### obtenerPendientes(int $estadoEnEsperaId) : LengthAwarePaginator

Obtiene órdenes pendientes (con detalles en estado "EN ESPERA").

#### obtenerCompletadas(int $estadoAprobadaId) : LengthAwarePaginator

Obtiene órdenes completadas (con detalles en estado "APROBADA").

#### obtenerRechazadas(int $estadoRechazadaId) : LengthAwarePaginator

Obtiene órdenes rechazadas (con detalles en estado "RECHAZADA").

#### encontrarConRelaciones(int $id) : ?Orden

Encuentra una orden con todas sus relaciones (usado en show).

**Relaciones cargadas:**
- `tipoOrden.parametro`
- `userCreate`
- `detalles.producto`
- `detalles.estadoOrden.parametro`
- `detalles.aprobacion.aprobador`

#### encontrarConDetallesYDevoluciones(int $id) : ?Orden

Encuentra una orden con detalles y devoluciones (usado en update y destroy).

#### obtenerDetallesPendientes(int $estadoEnEsperaId) : Collection

Obtiene detalles de orden pendientes de aprobación (sin aprobación asociada).

#### crear(array $datos) : Orden

Crea una nueva orden.

#### actualizar(Orden $orden, array $datos) : bool

Actualiza una orden existente.

#### eliminar(Orden $orden) : bool

Elimina una orden.

---

## 4. Repository: DetalleOrdenRepository

### 4.1. Ubicación

`app/Inventario/Repositories/Orden/DetalleOrdenRepository.php`

### 4.2. Interfaz

`App\Inventario\Interfaces\Repositories\Orden\DetalleOrdenRepositoryInterface`

### 4.3. Métodos Principales

#### crear(array $datos) : DetalleOrden

Crea un nuevo detalle de orden.

#### actualizar(DetalleOrden $detalleOrden, array $datos) : bool

Actualiza un detalle de orden.

#### eliminar(DetalleOrden $detalleOrden) : bool

Elimina un detalle de orden.

#### eliminarPorOrden(int $ordenId) : bool

Elimina todos los detalles de una orden específica.

#### encontrar(int $id) : ?DetalleOrden

Encuentra un detalle de orden por ID.

#### encontrarConRelaciones(int $id) : ?DetalleOrden

Encuentra un detalle de orden con todas sus relaciones.

**Relaciones cargadas:**
- `orden.tipoOrden.parametro`
- `producto`
- `estadoOrden.parametro`
- `devoluciones`

---

## 5. Repository: ProveedorRepository

### 5.1. Ubicación

`app/Inventario/Repositories/Proveedor/ProveedorRepository.php`

### 5.2. Interfaz

`App\Inventario\Interfaces\Repositories\Proveedor\ProveedorRepositoryInterface`

### 5.3. Métodos Principales

#### obtenerTodos() : Collection

Obtiene todos los proveedores ordenados por nombre.

#### obtenerConFiltros(array $filtros = []) : LengthAwarePaginator

Obtiene proveedores paginados con filtros y relaciones.

**Filtros soportados:**
- `search`: Búsqueda en nombre, NIT, email, teléfono, contacto, departamento, municipio
- `per_page`: Elementos por página (default: 10)

**Relaciones cargadas:**
- `userCreate.persona`
- `userUpdate.persona`
- `estado.parametro`
- `departamento`
- `municipio`

**Conteo incluido:**
- `contratosConvenios_count`

#### encontrarConRelaciones(int $id) : ?Proveedor

Encuentra un proveedor con todas sus relaciones.

#### crear(array $datos) : Proveedor

Crea un nuevo proveedor.

#### actualizar(int $id, array $datos) : bool

Actualiza un proveedor existente.

#### eliminar(int $id) : bool

Elimina un proveedor.

#### tieneContratos(int $id) : bool

Verifica si un proveedor tiene contratos asociados.

#### tieneProductos(int $id) : bool

Verifica si un proveedor tiene productos asociados.

---

## 6. Repository: CategoriaRepository

### 6.1. Ubicación

`app/Inventario/Repositories/Categoria/CategoriaRepository.php`

### 6.2. Interfaz

`App\Inventario\Interfaces\Repositories\Categoria\CategoriaRepositoryInterface`

### 6.3. Características Especiales

Las categorías utilizan el sistema de parámetros y temas. El repository trabaja con la tabla `parametros` filtrada por el tema "CATEGORIAS".

### 6.4. Métodos Principales

#### obtenerTemaCategorias() : ?Tema

Obtiene el tema "CATEGORIAS" del sistema.

#### obtenerConFiltros(array $filtros = []) : LengthAwarePaginator

Obtiene categorías paginadas con filtros.

**Filtros soportados:**
- `search`: Búsqueda en nombre de categoría
- `per_page`: Elementos por página (default: 10)

**Características:**
- Filtra por tema "CATEGORIAS"
- Solo categorías activas (status = 1)
- Incluye conteo de productos por categoría

#### encontrar(int $id) : ?Categoria

Encuentra una categoría por ID.

#### encontrarMultiples(array $ids) : Collection

Encuentra múltiples categorías por IDs.

#### encontrarConRelaciones(int $id) : ?Parametro

Encuentra una categoría con relaciones de usuarios.

#### actualizar(int $id, array $datos) : bool

Actualiza una categoría.

#### eliminar(Parametro $categoria, int $temaId) : bool

Elimina una categoría desvinculándola del tema "CATEGORIAS".

**Proceso:**
1. Elimina la relación en `parametros_temas`
2. Elimina el parámetro

#### tieneProductos(int $id) : bool

Verifica si una categoría tiene productos asociados.

---

## 7. Repository: MarcaRepository

### 7.1. Ubicación

`app/Inventario/Repositories/Marca/MarcaRepository.php`

### 7.2. Interfaz

`App\Inventario\Interfaces\Repositories\Marca\MarcaRepositoryInterface`

### 7.3. Características Especiales

Las marcas utilizan el sistema de parámetros y temas. El repository trabaja con la tabla `parametros` filtrada por el tema "MARCAS".

### 7.4. Métodos Principales

#### obtenerTemaMarcas() : ?Tema

Obtiene el tema "MARCAS" del sistema.

#### obtenerConFiltros(array $filtros = []) : LengthAwarePaginator

Obtiene marcas paginadas con filtros.

**Filtros soportados:**
- `search`: Búsqueda en nombre de marca
- `per_page`: Elementos por página (default: 10)

**Características:**
- Filtra por tema "MARCAS"
- Solo marcas activas (status = 1)
- Incluye conteo de productos por marca

#### encontrar(int $id) : ?Marca

Encuentra una marca por ID.

#### encontrarMultiples(array $ids) : Collection

Encuentra múltiples marcas por IDs.

#### encontrarConRelaciones(int $id) : ?Parametro

Encuentra una marca con relaciones de usuarios.

#### actualizar(int $id, array $datos) : bool

Actualiza una marca.

#### eliminar(Parametro $marca, int $temaId) : bool

Elimina una marca desvinculándola del tema "MARCAS".

**Proceso:**
1. Elimina la relación en `parametros_temas`
2. Elimina el parámetro

#### tieneProductos(int $id) : bool

Verifica si una marca tiene productos asociados.

---

## 8. Repository: ContratoConvenioRepository

### 8.1. Ubicación

`app/Inventario/Repositories/ContratoConvenio/ContratoConvenioRepository.php`

### 8.2. Interfaz

`App\Inventario\Interfaces\Repositories\ContratoConvenio\ContratoConvenioRepositoryInterface`

### 8.3. Métodos Principales

#### obtenerTodos() : Collection

Obtiene todos los contratos y convenios ordenados por nombre.

#### obtenerConFiltros(array $filtros = []) : LengthAwarePaginator

Obtiene contratos paginados con filtros y relaciones.

**Filtros soportados:**
- `search`: Búsqueda en nombre, código o proveedor
- `per_page`: Elementos por página (default: 10)

**Relaciones cargadas:**
- `proveedor`
- `estado.parametro`
- `userCreate.persona`
- `userUpdate.persona`

#### encontrarConRelaciones(int $id) : ?ContratoConvenio

Encuentra un contrato con todas sus relaciones.

**Relaciones cargadas:**
- `proveedor`
- `productos`
- `estado.parametro`
- `userCreate.persona`
- `userUpdate.persona`

#### crear(array $datos) : ContratoConvenio

Crea un nuevo contrato o convenio.

#### actualizar(int $id, array $datos) : bool

Actualiza un contrato existente.

#### eliminar(int $id) : bool

Elimina un contrato.

#### tieneProductos(int $id) : bool

Verifica si un contrato tiene productos asociados.

---

## 9. Repository: DevolucionRepository

### 9.1. Ubicación

`app/Inventario/Repositories/Devolucion/DevolucionRepository.php`

### 9.2. Interfaz

`App\Inventario\Interfaces\Repositories\Devolucion\DevolucionRepositoryInterface`

### 9.3. Métodos Principales

#### obtenerPrestamosPendientes(int $estadoAprobadaId) : LengthAwarePaginator

Obtiene préstamos pendientes de devolución.

**Características:**
- Solo órdenes con fecha de devolución
- Solo detalles en estado "APROBADA"
- Filtra detalles completamente devueltos
- Usa paginación manual para colecciones filtradas

#### obtenerHistorial() : LengthAwarePaginator

Obtiene historial completo de devoluciones ordenado por fecha descendente.

**Relaciones cargadas:**
- `detalleOrden.producto`
- `detalleOrden.orden`
- `userCreate`

#### encontrarConRelaciones(int $id) : ?Devolucion

Encuentra una devolución con todas sus relaciones.

#### obtenerPrestamosActivosUsuario(int $userId, int $estadoAprobadaId) : LengthAwarePaginator

Obtiene préstamos activos de un usuario específico.

#### obtenerHistorialPrestamosUsuario(int $userId) : LengthAwarePaginator

Obtiene historial de préstamos de un usuario específico.

#### paginacionManual(Collection $items, int $perPage) : LengthAwarePaginator

Método privado para crear paginación manual de colecciones filtradas.

---

## 10. Repository: AprobacionRepository

### 10.1. Ubicación

`app/Inventario/Repositories/Aprobacion/AprobacionRepository.php`

### 10.2. Interfaz

`App\Inventario\Interfaces\Repositories\Aprobacion\AprobacionRepositoryInterface`

### 10.3. Métodos Principales

#### crear(array $datos) : Aprobacion

Crea una nueva aprobación de orden.

**Nota:** Este repository es simple ya que la lógica de aprobación se maneja principalmente en los servicios.

---

## 11. Repository: DashboardRepository

### 11.1. Ubicación

`app/Inventario/Repositories/Dashboard/DashboardRepository.php`

### 11.2. Características Especiales

Este repository no implementa una interfaz y utiliza consultas directas con Query Builder para obtener estadísticas del dashboard.

### 11.3. Métodos Principales

#### obtenerTotalProductos() : int

Obtiene el total de productos en el inventario.

#### obtenerProductosConsumibles() : int

Obtiene el total de productos de tipo "CONSUMIBLE".

#### obtenerProductosNoConsumibles() : int

Obtiene el total de productos de tipo "NO CONSUMIBLE".

#### obtenerProductosPorVencer() : int

Obtiene productos que vencen en los próximos 30 días.

#### obtenerProductosStockBajo() : int

Obtiene productos con stock menor a 10 unidades.

#### obtenerTotalCategorias() : int

Obtiene el total de categorías activas.

#### obtenerProductosMasSolicitados(int $limite = 5) : array

Obtiene los productos más solicitados en órdenes.

**Retorna:**
```php
[
    ['nombre' => 'Producto', 'solicitudes' => 10],
    // ...
]
```

#### obtenerProductosPorCategoria() : array

Obtiene productos agrupados por categoría.

**Retorna:**
```php
[
    ['categoria' => 'Categoría', 'total' => 5],
    // ...
]
```

#### obtenerProductosRecientes(int $limite = 5) : array

Obtiene productos recientes con su estado.

**Retorna:**
```php
[
    [
        'producto' => 'Nombre',
        'cantidad' => 10,
        'estado' => [...],
        'created_at' => '2025-01-01'
    ],
    // ...
]
```

---

## 12. Repository: NotificationRepository

### 12.1. Ubicación

`app/Inventario/Repositories/Notification/NotificationRepository.php`

### 12.2. Interfaz

`App\Inventario\Interfaces\Repositories\Notification\NotificationRepositoryInterface`

### 12.3. Métodos Principales

#### obtenerPorUsuarioPaginadas(int $userId, int $perPage) : LengthAwarePaginator

Obtiene notificaciones paginadas de un usuario.

#### obtenerNoLeidasLimitadas(int $userId, int $limit) : Collection

Obtiene notificaciones no leídas limitadas (usado en dropdown).

#### contarNoLeidas(int $userId) : int

Cuenta las notificaciones no leídas de un usuario.

#### marcarComoLeida(int $userId, string $notificationId) : bool

Marca una notificación específica como leída.

#### marcarTodasComoLeidas(int $userId) : int

Marca todas las notificaciones de un usuario como leídas.

**Retorna:** Número de notificaciones marcadas.

#### eliminar(int $userId, string $notificationId) : bool

Elimina una notificación específica.

---

## 13. Repository: UserRepository

### 13.1. Ubicación

`app/Inventario/Repositories/User/UserRepository.php`

### 13.2. Interfaz

`App\Inventario\Interfaces\Services\UserRepositoryInterface`

### 13.3. Métodos Principales

#### obtenerSuperAdministradores() : Collection

Obtiene todos los usuarios con rol "SUPER ADMINISTRADOR".

**Uso:** Principalmente para notificaciones a administradores.

---

## 14. Buenas Prácticas

### 14.1. Eager Loading

Todos los repositories utilizan eager loading para evitar problemas N+1:

```php
// ✅ Correcto
$productos = Producto::with(['categoria', 'marca'])->get();

// ❌ Incorrecto
$productos = Producto::all();
foreach ($productos as $producto) {
    echo $producto->categoria->name; // N+1 query
}
```

### 14.2. Uso de Interfaces

Siempre inyectar interfaces en servicios, no implementaciones:

```php
// ✅ Correcto
public function __construct(
    private ProductoRepositoryInterface $productoRepository
) {}

// ❌ Incorrecto
public function __construct(
    private ProductoRepository $productoRepository
) {}
```

### 14.3. Paginación

Usar paginación para listados grandes:

```php
return $query->paginate($perPage ?? 10);
```

### 14.4. Filtros Flexibles

Los métodos `obtenerConFiltros()` aceptan arrays de filtros para máxima flexibilidad:

```php
$filtros = [
    'search' => 'producto',
    'categoria_id' => 1,
    'per_page' => 20
];
$productos = $repository->obtenerConFiltros($filtros);
```

### 14.5. Validación de Relaciones

Verificar relaciones antes de eliminar:

```php
if ($repository->tieneProductos($id)) {
    throw new Exception('No se puede eliminar: tiene productos asociados');
}
```

---

## 15. Inyección de Dependencias

### 15.1. Registro en Service Provider

Los repositories deben estar registrados en el contenedor de servicios de Laravel para la inyección de dependencias. Normalmente se hace en `AppServiceProvider` o en un Service Provider específico del módulo.

### 15.2. Ejemplo de Uso en Servicios

```php
class ProductoService
{
    public function __construct(
        private ProductoRepositoryInterface $repository
    ) {}

    public function listar(array $filtros = []): LengthAwarePaginator
    {
        return $this->repository->obtenerConFiltros($filtros);
    }
}
```

---

**Fin de la Parte 4**

*Continúa en la Parte 5: Servicios y Lógica de Negocio*

