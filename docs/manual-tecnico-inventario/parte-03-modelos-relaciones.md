# Manual Técnico - Módulo de Inventario
## Parte 3: Modelos y Relaciones

---

## 1. Introducción a los Modelos

### 1.1. Estructura General

Los modelos del módulo de Inventario están ubicados en `app/Models/Inventario/` y extienden de `Illuminate\Database\Eloquent\Model`. Todos los modelos utilizan el trait `Seguimiento` para la auditoría de usuarios y el trait `HasFactory` para la generación de factories de pruebas.

### 1.2. Modelos Principales

El módulo cuenta con 10 modelos principales:

1. **Producto** - Modelo principal de productos
2. **Orden** - Modelo de órdenes de préstamo/salida
3. **DetalleOrden** - Detalles de productos en órdenes
4. **Proveedor** - Modelo de proveedores
5. **ContratoConvenio** - Contratos y convenios
6. **Devolucion** - Devoluciones de productos
7. **Aprobacion** - Aprobaciones de órdenes
8. **Categoria** - Categorías de productos (extiende Parametro)
9. **Marca** - Marcas de productos (extiende Parametro)
10. **Notificacion** - Notificaciones del sistema

---

## 2. Trait Seguimiento

### 2.1. Descripción

El trait `Seguimiento` proporciona funcionalidad de auditoría para rastrear qué usuario creó y actualizó cada registro. Está ubicado en `app/Traits/Seguimiento.php`.

### 2.2. Métodos Proporcionados

```php
// Relación con el usuario que creó el registro
public function userCreate() : BelongsTo

// Relación con el usuario que actualizó el registro
public function userUpdate() : BelongsTo

// Alias de userCreate()
public function creador() : BelongsTo

// Alias de userUpdate()
public function actualizador() : BelongsTo
```

### 2.3. Uso

Todos los modelos principales del inventario utilizan este trait:

```php
use App\Traits\Seguimiento;

class Producto extends Model
{
    use HasFactory, Seguimiento;
    // ...
}
```

---

## 3. Modelo: Producto

### 3.1. Ubicación

`app/Models/Inventario/Producto.php`

### 3.2. Tabla

`productos`

### 3.3. Fillable

```php
protected $fillable = [
    'producto',
    'tipo_producto_id',
    'descripcion',
    'peso',
    'unidad_medida_id',
    'cantidad',
    'codigo_barras',
    'estado_producto_id',
    'categoria_id',
    'marca_id',
    'contrato_convenio_id',
    'ambiente_id',
    'proveedor_id',
    'fecha_vencimiento',
    'imagen',
    'user_create_id',
    'user_update_id'
];
```

### 3.4. Casts

```php
protected $casts = [
    'fecha_vencimiento' => 'datetime'
];
```

### 3.5. Eventos del Modelo

El modelo tiene eventos `creating` y `updating` que convierten automáticamente el nombre del producto a mayúsculas:

```php
protected static function booted() : void
{
    static::creating(function ($producto) {
        if (isset($producto->producto)) {
            $producto->producto = strtoupper($producto->producto);
        }
    });

    static::updating(function ($producto) {
        if (isset($producto->producto)) {
            $producto->producto = strtoupper($producto->producto);
        }
    });
}
```

### 3.6. Relaciones

#### Relaciones BelongsTo

- **tipoProducto()**: `belongsTo(ParametroTema::class, 'tipo_producto_id')`
- **unidadMedida()**: `belongsTo(ParametroTema::class, 'unidad_medida_id')`
- **estado()**: `belongsTo(ParametroTema::class, 'estado_producto_id')`
- **marca()**: `belongsTo(Parametro::class, 'marca_id')` con filtro por tema "MARCAS"
- **categoria()**: `belongsTo(Parametro::class, 'categoria_id')` con filtro por tema "CATEGORIAS"
- **contratoConvenio()**: `belongsTo(ContratoConvenio::class)`
- **ambiente()**: `belongsTo(Ambiente::class)`
- **proveedor()**: `belongsTo(Proveedor::class)`

#### Relaciones HasMany

- **detalleOrdenes()**: `hasMany(DetalleOrden::class, 'producto_id')`

### 3.7. Métodos de Negocio

#### tieneStockDisponible(int $cantidadRequerida) : bool

Verifica si hay stock suficiente para la cantidad requerida.

```php
public function tieneStockDisponible(int $cantidadRequerida) : bool
{
    return $this->cantidad >= $cantidadRequerida;
}
```

#### descontarStock(int $cantidad) : self

Descuenta stock del producto. Lanza `StockException` si no hay stock suficiente.

```php
public function descontarStock(int $cantidad) : self
{
    if (!$this->tieneStockDisponible($cantidad)) {
        throw new StockException("Stock insuficiente...");
    }
    $this->cantidad -= $cantidad;
    $this->save();
    return $this;
}
```

#### devolverStock(int $cantidad) : self

Devuelve stock al producto (aumenta la cantidad).

```php
public function devolverStock(int $cantidad) : self
{
    $this->cantidad += $cantidad;
    $this->save();
    return $this;
}
```

#### esConsumible() : bool

Verifica si el producto es de tipo "CONSUMIBLE".

```php
public function esConsumible(): bool
{
    $this->loadMissing(['tipoProducto.parametro']);
    $tipo = $this->tipoProducto;
    if ($tipo === null || $tipo->parametro === null) {
        return false;
    }
    return strtoupper($tipo->parametro->name) === 'CONSUMIBLE';
}
```

#### getPorcentajeStock(int $stockMaximo = 100) : float

Calcula el porcentaje de stock disponible.

#### getEstadoStock() : string

Retorna el estado del stock: 'critico', 'bajo', 'medio', 'normal'.

#### getBadgeStock() : string

Genera HTML con badge para mostrar el estado del stock.

---

## 4. Modelo: Orden

### 4.1. Ubicación

`app/Models/Inventario/Orden.php`

### 4.2. Tabla

`ordenes`

### 4.3. Fillable

```php
protected $fillable = [
    'descripcion_orden',
    'tipo_orden_id',
    'fecha_devolucion',
    'estado_id',
    'user_create_id',
    'user_update_id'
];
```

### 4.4. Casts

```php
protected $casts = [
    'fecha_devolucion' => 'datetime'
];
```

### 4.5. Relaciones

#### Relaciones BelongsTo

- **tipoOrden()**: `belongsTo(ParametroTema::class, 'tipo_orden_id')`

#### Relaciones HasMany

- **detalles()**: `hasMany(DetalleOrden::class, 'orden_id')`

### 4.6. Métodos de Negocio

#### Prestamo() : bool

Verifica si la orden es de tipo "PRÉSTAMO".

```php
public function Prestamo() : bool
{
    return $this->tipoOrden && 
           strtoupper($this->tipoOrden->parametro->name ?? '') === 'PRÉSTAMO';
}
```

#### Salida() : bool

Verifica si la orden es de tipo "SALIDA".

```php
public function Salida() : bool
{
    return $this->tipoOrden && 
           strtoupper($this->tipoOrden->parametro->name ?? '') === 'SALIDA';
}
```

---

## 5. Modelo: DetalleOrden

### 5.1. Ubicación

`app/Models/Inventario/DetalleOrden.php`

### 5.2. Tabla

`detalle_ordenes`

### 5.3. Fillable

```php
protected $fillable = [
    'orden_id',
    'producto_id',
    'cantidad',
    'estado_orden_id',
    'user_create_id',
    'user_update_id'
];
```

### 5.4. Relaciones

#### Relaciones BelongsTo

- **orden()**: `belongsTo(Orden::class, 'orden_id')`
- **producto()**: `belongsTo(Producto::class, 'producto_id')`
- **estadoOrden()**: `belongsTo(ParametroTema::class, 'estado_orden_id')`

#### Relaciones HasMany

- **devoluciones()**: `hasMany(Devolucion::class, 'detalle_orden_id')`

#### Relaciones HasOne

- **aprobacion()**: `hasOne(Aprobacion::class, 'detalle_orden_id', 'id')`

### 5.5. Métodos de Negocio

#### getCantidadDevuelta() : int

Obtiene la cantidad total devuelta sumando todas las devoluciones.

```php
public function getCantidadDevuelta() : int
{
    $suma = $this->devoluciones()->sum('cantidad_devuelta');
    return (int) ($suma ?? 0);
}
```

#### tieneCierreSinStock() : bool

Verifica si alguna devolución tiene el flag `cierra_sin_stock` activo.

```php
public function tieneCierreSinStock(): bool
{
    if ($this->relationLoaded('devoluciones')) {
        return $this->devoluciones->contains(static function (Devolucion $devolucion): bool {
            return $devolucion->cierra_sin_stock === true;
        });
    }
    return $this->devoluciones()
        ->where('cierra_sin_stock', true)
        ->exists();
}
```

#### estaCompletamenteDevuelto() : bool

Verifica si el detalle está completamente devuelto (ya sea por cantidad o por cierre sin stock).

```php
public function estaCompletamenteDevuelto() : bool
{
    if ($this->tieneCierreSinStock()) {
        return true;
    }
    return $this->getCantidadDevuelta() >= $this->cantidad;
}
```

#### getCantidadPendiente() : int

Calcula la cantidad pendiente de devolución.

```php
public function getCantidadPendiente() : int
{
    if ($this->tieneCierreSinStock()) {
        return 0;
    }
    $pendiente = $this->cantidad - $this->getCantidadDevuelta();
    return $pendiente > 0 ? $pendiente : 0;
}
```

---

## 6. Modelo: Proveedor

### 6.1. Ubicación

`app/Models/Inventario/Proveedor.php`

### 6.2. Tabla

`proveedores`

### 6.3. Fillable

```php
protected $fillable = [
    'proveedor',
    'nit',
    'email',
    'telefono',
    'direccion',
    'departamento_id',
    'municipio_id',
    'contacto',
    'estado_id',
    'user_create_id',
    'user_update_id'
];
```

### 6.4. Eventos del Modelo

Convierte automáticamente el nombre del proveedor a mayúsculas:

```php
protected static function booted()
{
    static::creating(function ($proveedor) {
        $proveedor->proveedor = strtoupper($proveedor->proveedor);
    });

    static::updating(function ($proveedor) {
        $proveedor->proveedor = strtoupper($proveedor->proveedor);
    });
}
```

### 6.5. Relaciones

#### Relaciones BelongsTo

- **estado()**: `belongsTo(ParametroTema::class, 'estado_id')`
- **departamento()**: `belongsTo(Departamento::class)`
- **municipio()**: `belongsTo(Municipio::class)`

#### Relaciones HasMany

- **contratosConvenios()**: `hasMany(ContratoConvenio::class)`
- **productos()**: `hasMany(Producto::class)`

---

## 7. Modelo: ContratoConvenio

### 7.1. Ubicación

`app/Models/Inventario/ContratoConvenio.php`

### 7.2. Tabla

`contratos_convenios`

### 7.3. Fillable

```php
protected $fillable = [
    'name',
    'codigo',
    'proveedor_id',
    'estado_id',
    'fecha_inicio',
    'fecha_fin',
    'user_create_id',
    'user_update_id'
];
```

### 7.4. Dates

```php
protected $dates = [
    'fecha_inicio',
    'fecha_fin'
];
```

### 7.5. Eventos del Modelo

Convierte automáticamente el nombre del contrato a mayúsculas.

### 7.6. Relaciones

#### Relaciones BelongsTo

- **proveedor()**: `belongsTo(Proveedor::class)`
- **estado()**: `belongsTo(ParametroTema::class, 'estado_id')`

#### Relaciones HasMany

- **productos()**: `hasMany(Producto::class)`

---

## 8. Modelo: Devolucion

### 8.1. Ubicación

`app/Models/Inventario/Devolucion.php`

### 8.2. Tabla

`devoluciones`

### 8.3. Fillable

```php
protected $fillable = [
    'detalle_orden_id',
    'cantidad_devuelta',
    'fecha_devolucion',
    'estado_id',
    'observaciones',
    'cierra_sin_stock',
    'user_create_id',
    'user_update_id'
];
```

### 8.4. Casts

```php
protected $casts = [
    'fecha_devolucion' => 'datetime',
    'cierra_sin_stock' => 'boolean',
];
```

### 8.5. Relaciones

#### Relaciones BelongsTo

- **detalleOrden()**: `belongsTo(DetalleOrden::class, 'detalle_orden_id')`

### 8.6. Métodos Estáticos

#### registrarDevolucion() : self

Método estático principal para registrar una devolución. Realiza validaciones, crea el registro y procesa el stock.

```php
public static function registrarDevolucion(
    int $detalleOrdenId, 
    int $cantidadDevuelta, 
    ?string $observaciones = null
): self
```

**Funcionalidad:**
- Valida que no haya cierre sin stock previo
- Valida cantidad pendiente
- Permite cierre sin stock si cantidad es 0 (solo para consumibles)
- Crea el registro de devolución
- Procesa el stock (devuelve stock si no es cierre sin stock)

### 8.7. Métodos de Negocio

#### fueATiempo() : ?bool

Verifica si la devolución fue realizada a tiempo comparando con `fecha_devolucion` de la orden.

#### getDiasRetraso() : int

Calcula los días de retraso en la devolución. Retorna 0 si fue a tiempo o antes.

```php
public function getDiasRetraso() : int
{
    $fechaEsperada = $this->detalleOrden->orden->fecha_devolucion;
    if (!$fechaEsperada || $this->fueATiempo()) {
        return 0;
    }
    if ($this->fecha_devolucion->lt($fechaEsperada)) {
        return 0;
    }
    $dias = $this->fecha_devolucion->diffInDays($fechaEsperada, false);
    return (int) max(0, $dias);
}
```

---

## 9. Modelo: Aprobacion

### 9.1. Ubicación

`app/Models/Inventario/Aprobacion.php`

### 9.2. Tabla

`aprobaciones`

### 9.3. Fillable

```php
protected $fillable = [
    'detalle_orden_id',
    'estado_aprobacion_id',
    'user_create_id',
    'user_update_id'
];
```

### 9.4. Relaciones

#### Relaciones BelongsTo

- **detalleOrden()**: `belongsTo(DetalleOrden::class, 'detalle_orden_id')`
- **estado()**: `belongsTo(ParametroTema::class, 'estado_aprobacion_id')`
- **aprobador()**: `belongsTo(User::class, 'user_update_id')`

---

## 10. Modelo: Categoria

### 10.1. Ubicación

`app/Models/Inventario/Categoria.php`

### 10.2. Tabla

`parametros` (compartida con otros parámetros)

### 10.3. Características Especiales

- Extiende de `App\Models\Parametro`
- Utiliza la tabla `parametros` del sistema base
- Se diferencia por el tema "CATEGORIAS"

### 10.4. Eventos del Modelo

Convierte automáticamente el nombre a mayúsculas.

### 10.5. Métodos Estáticos

#### tema() : ?Tema

Obtiene el tema "CATEGORIAS" del sistema.

### 10.6. Métodos de Instancia

#### asociarATemaCategorias() : void

Asocia la categoría al tema "CATEGORIAS" creando un registro en `parametros_temas`.

### 10.7. Relaciones

#### Relaciones HasMany

- **productos()**: `hasMany(Producto::class, 'categoria_id')`

---

## 11. Modelo: Marca

### 11.1. Ubicación

`app/Models/Inventario/Marca.php`

### 11.2. Tabla

`parametros` (compartida con otros parámetros)

### 11.3. Características Especiales

- Extiende de `App\Models\Parametro`
- Utiliza la tabla `parametros` del sistema base
- Se diferencia por el tema "MARCAS"

### 11.4. Eventos del Modelo

Convierte automáticamente el nombre a mayúsculas.

### 11.5. Métodos Estáticos

#### tema() : ?Tema

Obtiene el tema "MARCAS" del sistema.

### 11.6. Métodos de Instancia

#### asociarATemaMarcas() : void

Asocia la marca al tema "MARCAS" creando un registro en `parametros_temas`.

### 11.7. Relaciones

#### Relaciones HasMany

- **productos()**: `hasMany(Producto::class, 'marca_id')`

---

## 12. Modelo: Notificacion

### 12.1. Ubicación

`app/Models/Inventario/Notificacion.php`

### 12.2. Tabla

`notificaciones`

### 12.3. Características Especiales

- Extiende de `Illuminate\Notifications\DatabaseNotification`
- Utiliza UUID como clave primaria (no auto-incremental)
- Utiliza nombres de columnas personalizados en español

### 12.4. Fillable

```php
protected $fillable = [
    'id',
    'tipo',
    'datos',
    'leida_en',
    'notificable_type',
    'notificable_id',
    'created_at',
    'updated_at',
];
```

### 12.5. Casts

```php
protected $casts = [
    'datos' => 'array',
    'leida_en' => 'datetime',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
];
```

### 12.6. Propiedades Especiales

```php
public $timestamps = true;
public $incrementing = false;
protected $keyType = 'string';
```

### 12.7. Accessors Personalizados

El modelo sobrescribe varios accessors para mapear nombres de columnas en español a los nombres esperados por Laravel:

- **getDataAttribute()**: Mapea `datos` → `data`
- **getTypeAttribute()**: Mapea `tipo` → `type`
- **getReadAtAttribute()**: Mapea `leida_en` → `read_at`
- **getNotifiableTypeAttribute()**: Mapea `notificable_type`
- **getNotifiableIdAttribute()**: Mapea `notificable_id`

### 12.8. Métodos de Negocio

#### markAsRead() : void

Marca la notificación como leída estableciendo `leida_en` a la fecha actual.

#### markAsUnread() : void

Marca la notificación como no leída estableciendo `leida_en` a null.

#### read() : bool

Verifica si la notificación ha sido leída.

#### unread() : bool

Verifica si la notificación no ha sido leída.

---

## 13. Diagrama de Relaciones

### 13.1. Relaciones Principales

```
Producto
├── belongsTo → TipoProducto (ParametroTema)
├── belongsTo → UnidadMedida (ParametroTema)
├── belongsTo → Estado (ParametroTema)
├── belongsTo → Categoria (Parametro)
├── belongsTo → Marca (Parametro)
├── belongsTo → ContratoConvenio
├── belongsTo → Ambiente
├── belongsTo → Proveedor
└── hasMany → DetalleOrden

Orden
├── belongsTo → TipoOrden (ParametroTema)
└── hasMany → DetalleOrden

DetalleOrden
├── belongsTo → Orden
├── belongsTo → Producto
├── belongsTo → EstadoOrden (ParametroTema)
├── hasMany → Devolucion
└── hasOne → Aprobacion

Proveedor
├── belongsTo → Estado (ParametroTema)
├── belongsTo → Departamento
├── belongsTo → Municipio
├── hasMany → ContratoConvenio
└── hasMany → Producto

ContratoConvenio
├── belongsTo → Proveedor
├── belongsTo → Estado (ParametroTema)
└── hasMany → Producto

Devolucion
└── belongsTo → DetalleOrden

Aprobacion
├── belongsTo → DetalleOrden
├── belongsTo → EstadoAprobacion (ParametroTema)
└── belongsTo → Aprobador (User)
```

### 13.2. Relaciones con Sistema Base

- **Producto** → `ParametroTema` (tipo, unidad, estado)
- **Producto** → `Parametro` (categoría, marca)
- **Orden** → `ParametroTema` (tipo)
- **DetalleOrden** → `ParametroTema` (estado)
- **Proveedor** → `ParametroTema` (estado)
- **ContratoConvenio** → `ParametroTema` (estado)
- **Aprobacion** → `ParametroTema` (estado)
- **Categoria** → `Parametro` (extiende)
- **Marca** → `Parametro` (extiende)

---

## 14. Buenas Prácticas

### 14.1. Uso de Eager Loading

Siempre usar eager loading para evitar problemas N+1:

```php
// ✅ Correcto
$productos = Producto::with(['categoria', 'marca', 'proveedor'])->get();

// ❌ Incorrecto
$productos = Producto::all();
foreach ($productos as $producto) {
    echo $producto->categoria->name; // N+1 query
}
```

### 14.2. Transacciones para Operaciones Críticas

Usar transacciones para operaciones que modifican múltiples registros:

```php
DB::transaction(function () {
    $producto->descontarStock($cantidad);
    $detalleOrden->save();
});
```

### 14.3. Validación de Stock

Siempre validar stock antes de descontar:

```php
if (!$producto->tieneStockDisponible($cantidad)) {
    throw new StockException('Stock insuficiente');
}
$producto->descontarStock($cantidad);
```

### 14.4. Uso de Eventos del Modelo

Los eventos `creating` y `updating` se utilizan para normalizar datos (mayúsculas) automáticamente.

---

**Fin de la Parte 3**

*Continúa en la Parte 4: Repositories y Acceso a Datos*

