# Manual Técnico - Módulo de Inventario
## Parte 8: Testing y Comandos

---

## 1. Introducción al Testing

### 1.1. Estrategia de Testing

El módulo de Inventario utiliza una estrategia de testing completa que incluye:

- **Feature Tests**: Pruebas de integración que verifican el comportamiento completo de funcionalidades
- **Unit Tests**: Pruebas unitarias que verifican componentes individuales de forma aislada

### 1.2. Framework y Herramientas

- **PHPUnit 11.0**: Framework de testing para PHP
- **RefreshDatabase**: Trait para resetear la base de datos en cada test
- **Mockery**: Librería para crear mocks y stubs
- **Laravel Factories**: Para generar datos de prueba

### 1.3. Estructura de Tests

Los tests están organizados en:

```
tests/Modulos/Inventario/
├── Feature/              # Tests de integración
│   ├── Controllers/      # Tests de controladores
│   ├── Requests/         # Tests de validación
│   └── Routes/           # Tests de rutas
└── Unit/                 # Tests unitarios
    ├── Models/           # Tests de modelos
    ├── Repositories/     # Tests de repositories
    └── Services/         # Tests de servicios
```

---

## 2. Tests Feature (Integración)

### 2.1. Tests de Rutas

**Ubicación:** `tests/Modulos/Inventario/Feature/Routes/InventarioRoutesTest.php`

**Descripción:** Verifica que todas las rutas del módulo existan y respondan correctamente.

**Tests Incluidos:**

#### dashboard_routes_exist_and_respond()
Verifica que la ruta del dashboard exista y responda con código 200.

#### productos_routes_exist_and_respond()
Verifica todas las rutas de productos:
- `inventario.productos.catalogo`
- `inventario.productos.buscar`
- `inventario.productos.agregar-carrito`
- Rutas resource: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`

#### ordenes_routes_exist_and_respond()
Verifica rutas de órdenes:
- `inventario.prestamos-salidas`
- `inventario.prestamos-salidas.store`
- `inventario.ordenes.index`
- `inventario.ordenes.pendientes`
- `inventario.ordenes.completadas`
- `inventario.ordenes.rechazadas`

#### aprobaciones_routes_exist_and_respond()
Verifica rutas de aprobaciones:
- `inventario.aprobaciones.pendientes`
- `inventario.aprobaciones.aprobar`
- `inventario.aprobaciones.rechazar`
- `inventario.aprobaciones.aprobar-orden`
- `inventario.aprobaciones.rechazar-orden`

#### carrito_routes_exist_and_respond()
Verifica rutas del carrito:
- `inventario.carrito.ecommerce`
- `inventario.carrito.agregar`
- `inventario.carrito.actualizar`
- `inventario.carrito.eliminar`
- `inventario.carrito.vaciar`
- `inventario.carrito.contenido`

#### proveedores_routes_exist_and_respond()
Verifica rutas de proveedores (CRUD completo + municipios).

#### categorias_routes_exist_and_respond()
Verifica rutas de categorías (CRUD completo).

#### marcas_routes_exist_and_respond()
Verifica rutas de marcas (CRUD completo).

#### devoluciones_routes_exist_and_respond()
Verifica rutas de devoluciones:
- `inventario.devoluciones.index`
- `inventario.devoluciones.create`
- `inventario.devoluciones.store`
- `inventario.devoluciones.show`
- `inventario.devoluciones.historial`
- `inventario.prestamos.mis`
- `inventario.prestamos.historial`

#### notificaciones_routes_exist_and_respond()
Verifica rutas de notificaciones:
- `inventario.notificaciones.index`
- `inventario.notificaciones.unread`
- `inventario.notificaciones.read`
- `inventario.notificaciones.read-all`
- `inventario.notificaciones.destroy-all`
- `inventario.notificaciones.destroy`

#### contratos_convenios_routes_exist_and_respond()
Verifica rutas de contratos y convenios (CRUD completo).

#### todas_las_rutas_requieren_autenticacion()
Verifica que todas las rutas principales requieran autenticación (redirigen a login o retornan 403).

**Configuración:**
- Usa `RefreshDatabase` para resetear BD
- Desactiva CSRF para tests
- Crea usuario con todos los permisos de inventario
- Ejecuta seeders necesarios

---

### 2.2. Tests de Controladores

**Ubicación:** `tests/Modulos/Inventario/Feature/Controllers/`

**Controladores Testeados:**
- `ProductoControllerTest.php`
- `OrdenControllerTest.php`
- `CarritoControllerTest.php`
- `DashboardControllerTest.php`
- `ProveedorControllerTest.php`
- `CategoriaControllerTest.php`
- `MarcaControllerTest.php`
- `DevolucionControllerTest.php`
- `AprobacionControllerTest.php`
- `ContratoConvenioControllerTest.php`
- `NotificacionControllerTest.php`

**Ejemplo: ProductoControllerTest**

**Tests Típicos:**
- Verificar que usuarios sin permisos no puedan acceder
- Verificar que usuarios con permisos puedan acceder
- Verificar creación de productos
- Verificar actualización de productos
- Verificar eliminación de productos
- Verificar validaciones
- Verificar redirecciones

**Configuración:**
```php
protected function setUp(): void
{
    parent::setUp();
    $this->ejecutarSeedersNecesarios();
    $this->crearPermisos();
    $this->crearUsuarioConPermisos();
}
```

---

### 2.3. Tests de Form Requests

**Ubicación:** `tests/Modulos/Inventario/Feature/Requests/`

**Form Requests Testeados:**
- `ProductoRequestTest.php`
- `OrdenRequestTest.php`
- `CarritoRequestTest.php`
- `ProveedorRequestTest.php`
- `MarcaCategoriaRequestTest.php`
- `DevolucionRequestTest.php`
- `AprobacionesRequestTest.php`
- `ContratoConvenioRequestTest.php`

**Ejemplo: ProductoRequestTest**

**Tests Típicos:**
- Validar campos requeridos
- Validar unicidad de campos
- Validar tipos de datos
- Validar reglas condicionales (store vs update)
- Validar mensajes de error personalizados

**Ejemplo:**
```php
#[Test]
public function valida_campos_requeridos_para_store(): void
{
    $rules = $this->obtenerRules();
    $validator = Validator::make([], $rules);
    
    $this->assertTrue($validator->fails());
    $camposRequeridos = [
        'producto', 'tipo_producto_id', 'descripcion', 'peso',
        'unidad_medida_id', 'cantidad', 'estado_producto_id',
        'categoria_id', 'marca_id', 'contrato_convenio_id',
        'ambiente_id', 'proveedor_id'
    ];
    $this->assertCamposTienenError($validator, $camposRequeridos);
}
```

---

## 3. Tests Unit (Unitarios)

### 3.1. Tests de Modelos

**Ubicación:** `tests/Modulos/Inventario/Unit/Models/`

**Modelos Testeados:**
- `ProductoModelTest.php`
- `OrdenModelTest.php`
- `DetalleOrdenModelTest.php`
- `ProveedorModelTest.php`
- `CategoriaModelTest.php`
- `MarcaModelTest.php`
- `ContratoConvenioModelTest.php`
- `DevolucionModelTest.php`
- `AprobacionModelTest.php`
- `NotificacionModelTest.php`

**Ejemplo: ProductoModelTest**

**Tests Típicos:**
- Verificar creación de modelo
- Verificar actualización de modelo
- Verificar conversión automática a mayúsculas
- Verificar relaciones
- Verificar eventos del modelo
- Verificar scopes

**Ejemplo:**
```php
#[Test]
public function puede_crear_producto(): void
{
    $this->assertInstanceOf(Producto::class, $this->producto);
    $this->assertDatabaseHas('productos', [
        'id' => $this->producto->id,
    ]);
}

#[Test]
public function puede_actualizar_producto(): void
{
    $this->producto->update(['producto' => 'Producto Actualizado']);
    
    // El modelo convierte automáticamente a mayúsculas
    $this->assertEquals('PRODUCTO ACTUALIZADO', $this->producto->producto);
}
```

---

### 3.2. Tests de Repositories

**Ubicación:** `tests/Modulos/Inventario/Unit/Repositories/`

**Repositories Testeados:**
- `ProductoRepositoryTest.php`
- `OrdenRepositoryTest.php`
- `DetalleOrdenRepositoryTest.php`
- `ProveedorRepositoryTest.php`
- `CategoriaRepositoryTest.php`
- `MarcaRepositoryTest.php`
- `ContratoConvenioRepositoryTest.php`
- `DevolucionRepositoryTest.php`
- `AprobacionRepositoryTest.php`
- `DashboardRepositoryTest.php`
- `NotificationRepositoryTest.php`
- `UserRepositoryTest.php`

**Tests Típicos:**
- Verificar métodos de búsqueda
- Verificar filtros
- Verificar paginación
- Verificar eager loading
- Verificar creación
- Verificar actualización
- Verificar eliminación

---

### 3.3. Tests de Services

**Ubicación:** `tests/Modulos/Inventario/Unit/Services/`

**Services Testeados:**
- `ProductoServiceTest.php`
- `OrdenServiceTest.php`
- `CarritoServiceTest.php`
- `ProveedorServiceTest.php`
- `CategoriaServiceTest.php`
- `MarcaServiceTest.php`
- `ContratoConvenioServiceTest.php`
- `DevolucionServiceTest.php`
- `AprobacionServiceTest.php`
- `BarcodeServiceTest.php`
- `ImageServiceTest.php`
- `StockValidatorServiceTest.php`
- `FormOptionsServiceTest.php`
- `FormDataServiceTest.php`
- `ProductoEnrichmentServiceTest.php`
- `NotificationServiceTest.php`
- `UserNotificationServiceTest.php`
- `TransactionServiceTest.php`

**Ejemplo: ProductoServiceTest**

**Características:**
- Usa **Mockery** para mockear dependencias
- Prueba métodos del servicio de forma aislada
- Verifica interacciones con dependencias

**Ejemplo:**
```php
#[Test]
public function puede_crear_producto(): void
{
    $datos = [
        'producto' => 'Producto Test',
        'descripcion' => 'Descripción del producto',
        'cantidad' => 10,
    ];

    $productoMock = Mockery::mock(Producto::class)->makePartial();
    $productoMock->id = 1;
    $productoMock->producto = 'Producto Test';

    $this->mockBarcodeService->shouldReceive('resolverCodigoBarras')
        ->once()
        ->with(null)
        ->andReturn('12345678901');

    $this->mockImageService->shouldReceive('procesarImagen')
        ->once()
        ->with(null)
        ->andReturn('img/default.png');

    $this->mockRepository->shouldReceive('crear')
        ->once()
        ->andReturn($productoMock);

    $resultado = $this->service->crear($datos, 1);

    $this->assertInstanceOf(Producto::class, $resultado);
}
```

**Configuración:**
```php
protected function setUp(): void
{
    parent::setUp();
    
    $this->mockRepository = Mockery::mock(ProductoRepositoryInterface::class);
    $this->mockImageService = Mockery::mock(ImageServiceInterface::class);
    $this->mockBarcodeService = Mockery::mock(BarcodeServiceInterface::class);
    $this->mockStockValidator = Mockery::mock(StockValidatorServiceInterface::class);
    
    $this->service = new ProductoService(
        $this->mockRepository,
        $this->mockImageService,
        $this->mockBarcodeService,
        $this->mockStockValidator
    );
}
```

---

## 4. Comandos de Testing

### 4.1. Ejecutar Todos los Tests

```bash
# Ejecutar todos los tests del proyecto
php artisan test

# Ejecutar solo tests del módulo de inventario
php artisan test tests/Modulos/Inventario/

# Ejecutar tests con cobertura de código
php artisan test --coverage

# Ejecutar tests con cobertura mínima del 80%
php artisan test --coverage --min=80
```

### 4.2. Ejecutar Tests Específicos

```bash
# Ejecutar un test específico
php artisan test tests/Modulos/Inventario/Feature/Controllers/ProductoControllerTest.php

# Ejecutar un método de test específico
php artisan test --filter puede_crear_producto

# Ejecutar tests de un directorio
php artisan test tests/Modulos/Inventario/Feature/Controllers/

# Ejecutar tests unitarios
php artisan test tests/Modulos/Inventario/Unit/

# Ejecutar tests de integración
php artisan test tests/Modulos/Inventario/Feature/
```

### 4.3. Opciones Avanzadas

```bash
# Ejecutar tests en paralelo
php artisan test --parallel

# Detener en el primer fallo
php artisan test --stop-on-failure

# Mostrar output detallado
php artisan test --verbose

# Mostrar formato testdox
php artisan test --testdox

# Ejecutar tests con filtro por nombre de clase
php artisan test --filter ProductoControllerTest

# Ejecutar tests con filtro por namespace
php artisan test --filter Inventario
```

### 4.4. Tests en Entorno de Testing

```bash
# Configurar base de datos de testing
php artisan migrate --env=testing

# Ejecutar seeders en testing
php artisan db:seed --env=testing

# Ejecutar tests con base de datos fresh
php artisan test --env=testing
```

---

## 5. Comandos de Migración

### 5.1. Sistema de Migraciones Modulares

El módulo de Inventario utiliza el sistema de migraciones modulares del proyecto.

**Ver módulos disponibles:**
```bash
php artisan migrate:module --list
```

**Migrar módulo específico:**
```bash
# Migrar solo el módulo de inventario
php artisan migrate:module batch_15_inventario
```

**Migrar todos los módulos:**
```bash
# Migrar todos los módulos en orden
php artisan migrate:module --all

# Migrar todos los módulos desde cero (fresh)
php artisan migrate:module --all --fresh
```

### 5.2. Comandos de Migración Estándar

**Estado de migraciones:**
```bash
# Ver estado de todas las migraciones
php artisan migrate:status

# Ver estado de un módulo específico
php artisan migrate:status --path=database/migrations/batch_15_inventario
```

**Rollback:**
```bash
# Revertir última migración
php artisan migrate:rollback

# Revertir últimas N migraciones
php artisan migrate:rollback --step=3

# Revertir módulo específico
php artisan migrate:rollback --path=database/migrations/batch_15_inventario
```

**Reset:**
```bash
# Revertir todas las migraciones
php artisan migrate:reset

# Resetear y migrar todo (fresh)
php artisan migrate:fresh
```

### 5.3. Información de Base de Datos

```bash
# Mostrar información de la base de datos
php artisan db:show

# Mostrar estructura de una tabla
php artisan db:table productos

# Ver todas las tablas
php artisan db:show --table
```

---

## 6. Comandos de Desarrollo

### 6.1. Servidores de Desarrollo

```bash
# Iniciar servidor web de desarrollo
php artisan serve

# Iniciar servidor en puerto específico
php artisan serve --port=8001

# Iniciar servidor en host específico
php artisan serve --host=0.0.0.0
```

### 6.2. Compilación de Assets

```bash
# Compilar assets en modo desarrollo (watch)
npm run dev

# Compilar assets para producción
npm run build

# Compilar assets con Vite
npm run build -- --mode production
```

### 6.3. Limpieza de Caché

```bash
# Limpiar caché de configuración
php artisan config:clear

# Limpiar caché de rutas
php artisan route:clear

# Limpiar caché de vistas
php artisan view:clear

# Limpiar todos los cachés
php artisan optimize:clear
```

### 6.4. Seeders

```bash
# Ejecutar todos los seeders
php artisan db:seed

# Ejecutar seeder específico
php artisan db:seed --class=RolePermissionSeeder

# Ejecutar seeders con force (producción)
php artisan db:seed --force
```

---

## 7. Comandos de Producción

### 7.1. Optimización

```bash
# Optimizar autoloader de Composer
composer install --optimize-autoloader --no-dev

# Cachear configuración
php artisan config:cache

# Cachear rutas
php artisan route:cache

# Cachear vistas
php artisan view:cache

# Cachear eventos
php artisan event:cache
```

### 7.2. Verificación

```bash
# Verificar configuración
php artisan config:show

# Verificar rutas
php artisan route:list

# Verificar rutas del módulo de inventario
php artisan route:list --name=inventario

# Verificar servicios
php artisan list
```

---

## 8. Comandos Específicos del Módulo

### 8.1. Comandos de Inventario

Aunque el módulo no tiene comandos artisan personalizados, se pueden usar comandos genéricos de Laravel:

```bash
# Crear modelo de inventario
php artisan make:model Inventario/Producto

# Crear migración
php artisan make:migration create_productos_table

# Crear controlador
php artisan make:controller Inventario/ProductoController

# Crear servicio
php artisan make:service Inventario/ProductoService

# Crear test
php artisan make:test Inventario/ProductoTest
```

---

## 9. Buenas Prácticas de Testing

### 9.1. Organización

- **Separar Feature y Unit**: Tests de integración en `Feature/`, tests unitarios en `Unit/`
- **Nombres descriptivos**: Usar nombres que describan qué se está probando
- **Un test, una aserción**: Cada test debe verificar una cosa específica
- **Arrange-Act-Assert**: Estructurar tests en estas tres fases

### 9.2. Datos de Prueba

- **Usar Factories**: Generar datos con factories en lugar de crear manualmente
- **RefreshDatabase**: Usar `RefreshDatabase` para resetear BD en cada test
- **Seeders necesarios**: Ejecutar solo los seeders necesarios para el test

### 9.3. Mocks y Stubs

- **Mockear dependencias externas**: Usar Mockery para servicios externos
- **No mockear lo que se prueba**: Solo mockear dependencias, no el código bajo prueba
- **Verificar interacciones**: Usar `shouldReceive()` para verificar llamadas

### 9.4. Performance

- **Tests rápidos**: Los tests unitarios deben ser rápidos
- **Tests aislados**: Cada test debe ser independiente
- **Evitar I/O innecesario**: Mockear acceso a archivos, BD, etc.

### 9.5. Mantenibilidad

- **Código DRY**: Extraer setup común a métodos `setUp()`
- **Constantes**: Usar constantes para valores repetidos
- **Helpers**: Crear métodos helper para operaciones comunes

---

## 10. Ejecución de Tests en CI/CD

### 10.1. Pipeline de Testing

```bash
# Instalar dependencias
composer install --no-interaction --prefer-dist --optimize-autoloader

# Ejecutar migraciones
php artisan migrate --env=testing --force

# Ejecutar seeders
php artisan db:seed --env=testing --force

# Ejecutar tests con cobertura
php artisan test --coverage --min=80

# Generar reporte de cobertura
php artisan test --coverage --coverage-html=coverage/
```

### 10.2. Tests Paralelos

```bash
# Ejecutar tests en paralelo (múltiples procesos)
php artisan test --parallel

# Especificar número de procesos
php artisan test --parallel --processes=4
```

---

## 11. Troubleshooting

### 11.1. Problemas Comunes

**Tests fallan por permisos:**
```bash
# Verificar que los seeders de permisos se ejecuten
php artisan db:seed --class=RolePermissionSeeder
```

**Tests fallan por base de datos:**
```bash
# Resetear base de datos de testing
php artisan migrate:fresh --env=testing
php artisan db:seed --env=testing
```

**Tests lentos:**
```bash
# Ejecutar tests en paralelo
php artisan test --parallel

# Ejecutar solo tests específicos durante desarrollo
php artisan test --filter ProductoControllerTest
```

**Problemas con mocks:**
```bash
# Limpiar caché de configuración
php artisan config:clear

# Verificar que Mockery esté instalado
composer show mockery/mockery
```

---

## 12. Resumen de Comandos

### 12.1. Testing

```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar tests del módulo de inventario
php artisan test tests/Modulos/Inventario/

# Ejecutar con cobertura
php artisan test --coverage

# Ejecutar test específico
php artisan test --filter ProductoControllerTest
```

### 12.2. Migraciones

```bash
# Ver módulos disponibles
php artisan migrate:module --list

# Migrar módulo de inventario
php artisan migrate:module batch_15_inventario

# Migrar todos los módulos
php artisan migrate:module --all --fresh
```

### 12.3. Desarrollo

```bash
# Servidor de desarrollo
php artisan serve

# Compilar assets
npm run dev

# Limpiar cachés
php artisan optimize:clear
```

### 12.4. Producción

```bash
# Optimizar
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

**Fin de la Parte 8**

*Fin del Manual Técnico del Módulo de Inventario*

