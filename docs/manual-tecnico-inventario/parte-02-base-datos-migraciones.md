# Manual Técnico - Módulo de Inventario
## Parte 2: Base de Datos y Migraciones

---

## 1. Introducción a la Base de Datos

### 1.1. Estructura General

El módulo de Inventario utiliza un sistema de migraciones modulares organizado en batches. Las migraciones del inventario se encuentran en el **batch_16_inventario**, que contiene 22 archivos de migración que definen la estructura completa de las tablas del módulo.

### 1.2. Tablas Principales

El módulo de Inventario gestiona las siguientes tablas principales:

1. **productos** - Almacena información de productos del inventario
2. **ordenes** - Registra órdenes de salida/préstamo
3. **detalle_ordenes** - Detalles de productos en cada orden
4. **proveedores** - Información de proveedores
5. **contratos_convenios** - Contratos y convenios con proveedores
6. **devoluciones** - Registro de devoluciones de productos
7. **aprobaciones** - Sistema de aprobación de órdenes
8. **notificaciones** - Notificaciones del sistema de inventario

### 1.3. Tablas Relacionadas (Sistema Base)

El módulo también utiliza tablas del sistema base:

- **parametros** - Almacena categorías y marcas (mediante el sistema de temas)
- **parametros_temas** - Relación entre parámetros y temas (estados, tipos, unidades de medida)
- **users** - Usuarios del sistema
- **ambientes** - Ambientes físicos donde se ubican productos
- **programas_formacion** - Programas de formación asociados a órdenes
- **municipios** - Ubicación geográfica de proveedores

---

## 2. Migraciones del Módulo

### 2.1. Sistema de Migraciones Modulares

El proyecto utiliza un sistema de migraciones modulares que permite ejecutar migraciones por módulos específicos. Las migraciones del inventario están agrupadas en el batch 16.

**Comandos disponibles:**

```bash
# Ver módulos disponibles
php artisan migrate:module --list

# Migrar solo el módulo de inventario
php artisan migrate:module --module=inventario

# Migrar todos los módulos
php artisan migrate:module --all

# Resetear y migrar todos los módulos
php artisan migrate:module --all --fresh
```

### 2.2. Lista de Migraciones

Las migraciones del módulo de inventario se ejecutan en el siguiente orden:

#### Migraciones de Creación de Tablas

1. **2025_08_12_184612_create_productos_table.php**
   - Crea la tabla `productos` con campos básicos

2. **2025_08_15_202902_create_ordenes_table.php**
   - Crea la tabla `ordenes` para órdenes de compra/préstamo

3. **2025_08_22_232353_create_proveedores_table.php**
   - Crea la tabla `proveedores` con información básica

4. **2025_08_22_233125_create_contratos_convenios_table.php**
   - Crea la tabla `contratos_convenios` para contratos con proveedores

5. **2025_10_16_000001_create_detalle_ordenes_table.php**
   - Crea la tabla `detalle_ordenes` para detalles de productos en órdenes

6. **2025_10_16_000002_create_devoluciones_table.php**
   - Crea la tabla `devoluciones` para registro de devoluciones

7. **2025_11_01_000002_create_aprobaciones_table.php**
   - Crea la tabla `aprobaciones` para el sistema de aprobación

8. **2025_11_02_161704_create_notifications_table.php**
   - Crea la tabla `notificaciones` para notificaciones del sistema

#### Migraciones de Modificación de Tablas

9. **2025_08_13_225702_add_cantidad_and_codigo_barras_to_productos_table.php**
   - Agrega campos `cantidad` y `codigo_barras` a productos

10. **2025_08_14_181706_add_imagen_to_productos_table.php**
    - Agrega campo `imagen` a productos

11. **2025_08_15_095541_add_peso_to_productos_table.php**
    - Agrega campo `peso` a productos

12. **2025_08_15_120944_rename_estado_id_to_estado_producto_id_in_productos_table.php**
    - Renombra el campo `estado_id` a `estado_producto_id`

13. **2025_08_16_092603_add_fecha_devolucion_to_ordenes_table.php**
    - Agrega campo `fecha_devolucion` a órdenes

14. **2025_10_01_184449_add_codigo_to_contratos_convenios_table.php**
    - Agrega campo `codigo` a contratos_convenios

15. **2025_10_01_185055_add_campos_extra_to_productos_table.php**
    - Agrega campos: `categoria_id`, `marca_id`, `contrato_convenio_id`, `ambiente_id`

16. **2025_10_03_084350_add_nit_email_to_proveedores_table.php**
    - Agrega campos `nit` y `email` a proveedores

17. **2025_10_03_095032_add_fecha_vencimiento_to_productos_table.php**
    - Agrega campo `fecha_vencimiento` a productos

18. **2025_10_03_101454_add_proveedor_to_productos_table.php**
    - Agrega campo `proveedor_id` a productos

19. **2025_10_29_185352_add_campos_extra_to_proveedores_table.php**
    - Agrega campos: `telefono`, `direccion`, `municipio_id`, `contacto`, `estado_id`

20. **2025_11_01_132948_add_programa_formacion_to_ordenes_table.php**
    - Agrega campo `programa_formacion_id` a órdenes

21. **2025_11_01_180529_add_departamento_to_proveedores_table.php**
    - Agrega campo `departamento_id` a proveedores

22. **2025_11_11_000010_add_cierra_sin_stock_to_devoluciones_table.php**
    - Agrega campo `cierra_sin_stock` a devoluciones

---

## 3. Estructura de Tablas

### 3.1. Tabla: productos

**Descripción:** Almacena la información completa de los productos del inventario.

**Campos principales:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint (PK) | Identificador único |
| `producto` | string | Nombre del producto |
| `tipo_producto_id` | bigint (FK) | Tipo de producto (parametros_temas) |
| `descripcion` | text | Descripción detallada |
| `peso` | decimal(8,2) | Peso del producto |
| `unidad_medida_id` | bigint (FK) | Unidad de medida (parametros_temas) |
| `cantidad` | bigint | Cantidad disponible en stock |
| `codigo_barras` | string(13) | Código de barras único |
| `estado_producto_id` | bigint (FK) | Estado del producto (parametros_temas) |
| `imagen` | string (nullable) | Ruta de la imagen |
| `categoria_id` | bigint (FK, nullable) | Categoría (parametros) |
| `marca_id` | bigint (FK, nullable) | Marca (parametros) |
| `contrato_convenio_id` | bigint (FK, nullable) | Contrato/convenio asociado |
| `ambiente_id` | bigint (FK, nullable) | Ambiente donde se ubica |
| `proveedor_id` | bigint (FK, nullable) | Proveedor del producto |
| `fecha_vencimiento` | date (nullable) | Fecha de vencimiento |
| `user_create_id` | bigint (FK) | Usuario que creó |
| `user_update_id` | bigint (FK) | Usuario que actualizó |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

**Relaciones:**
- `tipo_producto_id` → `parametros_temas.id` (restrict)
- `unidad_medida_id` → `parametros_temas.id` (restrict)
- `estado_producto_id` → `parametros_temas.id` (restrict)
- `categoria_id` → `parametros.id` (restrict)
- `marca_id` → `parametros.id` (restrict)
- `contrato_convenio_id` → `contratos_convenios.id` (restrict)
- `ambiente_id` → `ambientes.id` (restrict)
- `proveedor_id` → `proveedores.id` (restrict)
- `user_create_id` → `users.id` (restrict)
- `user_update_id` → `users.id` (restrict)

**Índices:**
- Primary key: `id`
- Unique: `codigo_barras`

### 3.2. Tabla: ordenes

**Descripción:** Registra las órdenes de compra o préstamo de productos.

**Campos principales:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint (PK) | Identificador único |
| `descripcion_orden` | text | Descripción de la orden |
| `tipo_orden_id` | bigint (FK) | Tipo de orden (parametros_temas) |
| `fecha_devolucion` | date (nullable) | Fecha programada de devolución |
| `programa_formacion_id` | bigint (FK, nullable) | Programa de formación asociado |
| `user_create_id` | bigint (FK) | Usuario que creó |
| `user_update_id` | bigint (FK) | Usuario que actualizó |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

**Relaciones:**
- `tipo_orden_id` → `parametros_temas.id` (restrict)
- `programa_formacion_id` → `programas_formacion.id` (restrict)
- `user_create_id` → `users.id` (restrict)
- `user_update_id` → `users.id` (restrict)

### 3.3. Tabla: detalle_ordenes

**Descripción:** Almacena los detalles de productos incluidos en cada orden.

**Campos principales:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint (PK) | Identificador único |
| `orden_id` | bigint (FK) | Orden a la que pertenece |
| `producto_id` | bigint (FK) | Producto solicitado |
| `estado_orden_id` | bigint (FK) | Estado del detalle (parametros_temas) |
| `cantidad` | integer | Cantidad solicitada/prestada |
| `user_create_id` | bigint (FK) | Usuario que creó |
| `user_update_id` | bigint (FK) | Usuario que actualizó |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

**Relaciones:**
- `orden_id` → `ordenes.id` (cascade)
- `producto_id` → `productos.id` (restrict)
- `estado_orden_id` → `parametros_temas.id` (restrict)
- `user_create_id` → `users.id` (restrict)
- `user_update_id` → `users.id` (restrict)

**Nota:** La relación con `ordenes` usa `cascade` para eliminar automáticamente los detalles cuando se elimina una orden.

### 3.4. Tabla: proveedores

**Descripción:** Almacena información de proveedores de productos.

**Campos principales:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint (PK) | Identificador único |
| `proveedor` | string (unique) | Nombre del proveedor |
| `nit` | string (nullable) | NIT del proveedor |
| `email` | string (nullable) | Email de contacto |
| `telefono` | string(10, nullable) | Teléfono de contacto |
| `direccion` | string (nullable) | Dirección física |
| `municipio_id` | bigint (FK, nullable) | Municipio |
| `departamento_id` | bigint (FK, nullable) | Departamento |
| `contacto` | string(100, nullable) | Nombre del contacto |
| `estado_id` | bigint (FK, nullable) | Estado del proveedor |
| `user_create_id` | bigint (FK) | Usuario que creó |
| `user_update_id` | bigint (FK) | Usuario que actualizó |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

**Relaciones:**
- `municipio_id` → `municipios.id` (restrict)
- `departamento_id` → `departamentos.id` (restrict)
- `estado_id` → `parametros_temas.id` (restrict)
- `user_create_id` → `users.id` (restrict)
- `user_update_id` → `users.id` (restrict)

**Índices:**
- Primary key: `id`
- Unique: `proveedor`

### 3.5. Tabla: contratos_convenios

**Descripción:** Registra contratos y convenios con proveedores.

**Campos principales:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint (PK) | Identificador único |
| `name` | string (unique) | Nombre del contrato/convenio |
| `codigo` | string (nullable) | Código del contrato |
| `proveedor_id` | bigint (FK) | Proveedor asociado |
| `fecha_inicio` | date | Fecha de inicio |
| `fecha_fin` | date | Fecha de finalización |
| `estado_id` | bigint (FK) | Estado del contrato |
| `user_create_id` | bigint (FK) | Usuario que creó |
| `user_update_id` | bigint (FK) | Usuario que actualizó |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

**Relaciones:**
- `proveedor_id` → `proveedores.id` (restrict)
- `estado_id` → `parametros_temas.id` (restrict)
- `user_create_id` → `users.id` (restrict)
- `user_update_id` → `users.id` (restrict)

**Índices:**
- Primary key: `id`
- Unique: `name`

### 3.6. Tabla: devoluciones

**Descripción:** Registra las devoluciones de productos prestados.

**Campos principales:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint (PK) | Identificador único |
| `detalle_orden_id` | bigint (FK) | Detalle de orden devuelto |
| `cantidad_devuelta` | integer | Cantidad devuelta (puede ser parcial) |
| `fecha_devolucion` | date | Fecha real de devolución |
| `estado_id` | bigint (FK) | Estado de la devolución |
| `observaciones` | text (nullable) | Observaciones sobre la devolución |
| `cierra_sin_stock` | boolean (default: false) | Indica si cierra sin stock |
| `user_create_id` | bigint (FK) | Usuario que creó |
| `user_update_id` | bigint (FK) | Usuario que actualizó |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

**Relaciones:**
- `detalle_orden_id` → `detalle_ordenes.id` (cascade)
- `estado_id` → `parametros_temas.id` (restrict)
- `user_create_id` → `users.id` (restrict)
- `user_update_id` → `users.id` (restrict)

**Nota:** La relación con `detalle_ordenes` usa `cascade` para eliminar automáticamente las devoluciones cuando se elimina un detalle de orden.

### 3.7. Tabla: aprobaciones

**Descripción:** Sistema de aprobación de órdenes por usuarios autorizados.

**Campos principales:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint (PK) | Identificador único |
| `detalle_orden_id` | bigint (FK) | Detalle de orden a aprobar |
| `estado_aprobacion_id` | bigint (FK) | Estado de la aprobación |
| `user_create_id` | bigint (FK) | Usuario que aprobó/rechazó |
| `user_update_id` | bigint (FK) | Usuario que actualizó |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

**Relaciones:**
- `detalle_orden_id` → `detalle_ordenes.id` (restrict)
- `estado_aprobacion_id` → `parametros_temas.id` (restrict)
- `user_create_id` → `users.id` (restrict)
- `user_update_id` → `users.id` (restrict)

### 3.8. Tabla: notificaciones

**Descripción:** Sistema de notificaciones del módulo de inventario.

**Campos principales:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | uuid (PK) | Identificador único (UUID) |
| `tipo` | string | Tipo de notificación |
| `notificable_type` | string | Tipo de modelo notificable (polimórfico) |
| `notificable_id` | bigint | ID del modelo notificable |
| `datos` | text | Datos JSON de la notificación |
| `leida_en` | timestamp (nullable) | Fecha de lectura |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

**Índices:**
- Primary key: `id`
- Index compuesto: `(notificable_type, notificable_id, leida_en)`

**Nota:** Esta tabla utiliza relaciones polimórficas para poder notificar a diferentes tipos de modelos (principalmente usuarios).

---

## 4. Relaciones con Tablas del Sistema Base

### 4.1. Sistema de Parámetros y Temas

El módulo de inventario utiliza el sistema de parámetros y temas del sistema base para gestionar:

- **Categorías**: Almacenadas en `parametros` con tema "CATEGORIAS"
- **Marcas**: Almacenadas en `parametros` con tema "MARCAS"
- **Estados de Producto**: En `parametros_temas` con tema "ESTADOS DE PRODUCTO"
- **Tipos de Producto**: En `parametros_temas` con tema "TIPOS DE PRODUCTO"
- **Unidades de Medida**: En `parametros_temas` con tema "UNIDADES DE MEDIDA"
- **Estados de Orden**: En `parametros_temas` con tema "ESTADOS DE ORDEN"
- **Tipos de Orden**: En `parametros_temas` con tema "TIPOS DE ORDEN"

### 4.2. Tabla: ambientes

Los productos pueden estar asociados a ambientes físicos (aulas, laboratorios, etc.) mediante la relación `ambiente_id` en la tabla `productos`.

### 4.3. Tabla: programas_formacion

Las órdenes pueden estar asociadas a programas de formación mediante la relación `programa_formacion_id` en la tabla `ordenes`.

---

## 5. Comandos de Migración

### 5.1. Ejecutar Migraciones

```bash
# Migrar solo el módulo de inventario
php artisan migrate:module --module=inventario

# Migrar todos los módulos
php artisan migrate:module --all

# Ver estado de migraciones
php artisan migrate:status
```

### 5.2. Rollback de Migraciones

```bash
# Revertir última migración del inventario
php artisan migrate:rollback

# Revertir todas las migraciones
php artisan migrate:reset

# Revertir y volver a migrar (fresh)
php artisan migrate:module --module=inventario --fresh
```

### 5.3. Verificar Migraciones

```bash
# Ver migraciones pendientes
php artisan migrate:status

# Ver estructura de tablas
php artisan db:show

# Ver estructura de una tabla específica
php artisan db:table productos
```

---

## 6. Consideraciones Importantes

### 6.1. Integridad Referencial

- Todas las relaciones utilizan `onDelete('restrict')` excepto:
  - `detalle_ordenes.orden_id` → `cascade` (eliminar detalles al eliminar orden)
  - `devoluciones.detalle_orden_id` → `cascade` (eliminar devoluciones al eliminar detalle)

### 6.2. Auditoría

Todas las tablas principales incluyen campos de auditoría:
- `user_create_id`: Usuario que creó el registro
- `user_update_id`: Usuario que actualizó el registro
- `created_at`: Fecha de creación
- `updated_at`: Fecha de última actualización

### 6.3. Valores Únicos

- `productos.codigo_barras`: Debe ser único
- `proveedores.proveedor`: Debe ser único
- `contratos_convenios.name`: Debe ser único

### 6.4. Campos Nullable

Muchos campos son nullable para permitir flexibilidad:
- Imágenes de productos
- Fechas de vencimiento
- Relaciones opcionales (categoría, marca, proveedor, etc.)

---

**Fin de la Parte 2**

*Continúa en la Parte 3: Modelos y Relaciones*

