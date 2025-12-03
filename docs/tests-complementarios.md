# 🧪 Tests del Módulo de Complementarios

## 📖 Descripción General

El módulo de **Tests de Complementarios** es un conjunto completo de pruebas unitarias y de integración que garantizan la calidad y funcionalidad del módulo de Complementarios. Estas pruebas cubren controladores, servicios, repositorios, modelos, jobs, comandos y validaciones, asegurando que todas las funcionalidades del módulo funcionen correctamente.

### Finalidad

Este conjunto de tests tiene como objetivo:

- **Validar funcionalidades**: Verificar que todas las funcionalidades del módulo de Complementarios funcionen según lo esperado.
- **Garantizar calidad**: Asegurar la estabilidad del código mediante pruebas automatizadas.
- **Prevenir regresiones**: Detectar cambios no deseados en el comportamiento del sistema.
- **Documentar comportamiento**: Servir como documentación viva del comportamiento esperado del sistema.
- **Facilitar refactorización**: Permitir refactorizar código con confianza, sabiendo que las pruebas detectarán errores.

---

## 🏗️ Arquitectura de Tests

### Estructura de Directorios

```
tests/
└── Complementarios/
    ├── Concerns/
    │   └── SeedsComplementariosDatabase.php
    ├── Feature/
    │   ├── Controllers/
    │   │   ├── AspiranteComplementarioControllerTest.php
    │   │   ├── DocumentoComplementarioControllerTest.php
    │   │   ├── EstadisticaComplementarioControllerTest.php
    │   │   ├── InscripcionComplementarioControllerTest.php
    │   │   ├── PerfilComplementarioControllerTest.php
    │   │   ├── ProgramaComplementarioControllerTest.php
    │   │   └── ValidacionSofiaControllerTest.php
    │   └── Views/
    │       └── ComplementariosViewsTest.php
    └── Unit/
        ├── Commands/
        │   └── ValidarSofiaCommandTest.php
        ├── Jobs/
        │   └── ValidarSofiaJobTest.php
        ├── Models/
        │   ├── AspiranteComplementarioModelTest.php
        │   ├── ComplementarioOfertadoModelTest.php
        │   ├── SenasofiaplusValidationLogModelTest.php
        │   └── SofiaValidationProgressModelTest.php
        ├── Repositories/
        │   ├── AspiranteComplementarioRepositoryTest.php
        │   ├── ComplementarioOfertadoRepositoryTest.php
        │   └── SenasofiaplusValidationLogRepositoryTest.php
        ├── Requests/
        │   ├── InscripcionComplementarioRequestTest.php
        │   ├── StoreProgramaComplementarioRequestTest.php
        │   └── UpdateProgramaComplementarioRequestTest.php
        └── Services/
            ├── AspiranteComplementarioServiceTest.php
            ├── AspiranteDocumentoServiceTest.php
            ├── AspiranteExportServiceTest.php
            ├── AspiranteManagementServiceTest.php
            ├── ComplementarioServiceTest.php
            ├── EstadisticaComplementarioServiceTest.php
            ├── InscripcionComplementarioServiceTest.php
            └── Sofia/
                ├── SofiaHttpClientTest.php
                ├── SofiaStateMapperTest.php
                ├── SofiaValidationProcessorTest.php
                └── SofiaValidationServiceTest.php
```

---

## 🎯 Tipos de Tests

### 1. Tests de Feature (Integración)

**Ubicación:** `tests/Complementarios/Feature/`

**Objetivo:** Probar la funcionalidad completa de los controladores y vistas, incluyendo rutas, respuestas HTTP, redirecciones y sesiones.

#### Características:
- ✅ Pruebas de rutas y endpoints HTTP
- ✅ Validación de respuestas y redirecciones
- ✅ Pruebas de autenticación y autorización
- ✅ Verificación de datos en sesiones
- ✅ Tests de vistas y datos pasados a las vistas

#### Controladores cubiertos:
- `ProgramaComplementarioControllerTest` - 20+ tests
- `AspiranteComplementarioControllerTest` - 15+ tests  
- `InscripcionComplementarioControllerTest` - 10+ tests
- `EstadisticaComplementarioControllerTest` - 8+ tests
- `DocumentoComplementarioControllerTest` - 6+ tests
- `PerfilComplementarioControllerTest` - 4+ tests
- `ValidacionSofiaControllerTest` - 7+ tests

### 2. Tests Unitarios

**Ubicación:** `tests/Complementarios/Unit/`

**Objetivo:** Probar unidades individuales de código (servicios, repositorios, modelos, etc.) de forma aislada.

#### 2.1 Tests de Servicios

**Ubicación:** `tests/Complementarios/Unit/Services/`

**Servicios cubiertos:**
- `ComplementarioServiceTest` - 20+ tests
- `InscripcionComplementarioServiceTest` - 15+ tests
- `EstadisticaComplementarioServiceTest` - 10+ tests
- `AspiranteComplementarioServiceTest` - 12+ tests
- `AspiranteManagementServiceTest` - 8+ tests
- `AspiranteExportServiceTest` - 6+ tests
- `AspiranteDocumentoServiceTest` - 5+ tests
- `SofiaValidationServiceTest` - 8+ tests

#### 2.2 Tests de Repositorios

**Ubicación:** `tests/Complementarios/Unit/Repositories/`

**Repositorios cubiertos:**
- `ComplementarioOfertadoRepositoryTest` - 30+ tests
- `AspiranteComplementarioRepositoryTest` - 25+ tests
- `SenasofiaplusValidationLogRepositoryTest` - 10+ tests

#### 2.3 Tests de Modelos

**Ubicación:** `tests/Complementarios/Unit/Models/`

**Modelos cubiertos:**
- `ComplementarioOfertadoModelTest` - 15+ tests
- `AspiranteComplementarioModelTest` - 12+ tests
- `SofiaValidationProgressModelTest` - 8+ tests
- `SenasofiaplusValidationLogModelTest` - 6+ tests

#### 2.4 Tests de Requests

**Ubicación:** `tests/Complementarios/Unit/Requests/`

**Requests cubiertos:**
- `StoreProgramaComplementarioRequestTest` - 10+ tests
- `UpdateProgramaComplementarioRequestTest` - 8+ tests
- `InscripcionComplementarioRequestTest` - 12+ tests

#### 2.5 Tests de Jobs y Comandos

**Ubicación:** `tests/Complementarios/Unit/Jobs/` y `tests/Complementarios/Unit/Commands/`

**Cubiertos:**
- `ValidarSofiaJobTest` - 8+ tests
- `ValidarSofiaCommandTest` - 6+ tests

### 3. Tests de Vistas

**Ubicación:** `tests/Complementarios/Feature/Views/ComplementariosViewsTest.php`

**Objetivo:** Probar la renderización correcta de las vistas del módulo de Complementarios.

---

## 🔧 Concerns y Utilidades

### SeedsComplementariosDatabase

**Ubicación:** `tests/Complementarios/Concerns/SeedsComplementariosDatabase.php`

**Propósito:** Trait para optimizar el seeding de la base de datos en tests de Complementarios. Solo ejecuta los seeders si los datos base no existen, mejorando significativamente el rendimiento de los tests.

**Características:**
- ✅ Verifica si las tablas existen y tienen datos antes de ejecutar seeders
- ✅ Evita re-ejecutar seeders costosos en cada test
- ✅ Compatible con `RefreshDatabase`
- ✅ Manejo de excepciones robusto

**Seeders incluidos:**
- `RolePermissionSeeder`
- `ParametroSeeder`
- `TemaSeeder`
- `PaisSeeder`
- `DepartamentoSeeder`
- `MunicipioSeeder`
- `PersonaSeeder`
- `UsersSeeder`
- `RegionalSeeder`
- `CentroFormacionSeeder`
- `SedeSeeder`
- `BloqueSeeder`
- `PisoSeeder`
- `AmbienteSeeder`
- `JornadaFormacionSeeder`

---

## 📊 Cobertura de Funcionalidades

### Gestión de Programas Complementarios

#### Tests de Controlador:
- ✅ Listar programas (admin)
- ✅ Crear programas
- ✅ Editar programas
- ✅ Eliminar programas
- ✅ Ver detalles de programas
- ✅ Programas públicos
- ✅ Programas con oferta/sin oferta

#### Tests de Servicio:
- ✅ Enriquecimiento de programas (iconos, badges, labels)
- ✅ Sincronización de días de formación
- ✅ Obtención de datos para formularios
- ✅ Verificación de inscripciones existentes
- ✅ Creación y actualización de aspirantes
- ✅ Estadísticas de programas

#### Tests de Repositorio:
- ✅ CRUD completo de programas
- ✅ Filtrado por estado
- ✅ Conteo de aspirantes
- ✅ Estadísticas y métricas
- ✅ Programas con mayor demanda

### Gestión de Aspirantes

#### Tests de Controlador:
- ✅ Ver aspirantes por programa
- ✅ Agregar aspirantes existentes
- ✅ Rechazar aspirantes
- ✅ Exportar a Excel
- ✅ Descargar PDF de cédulas
- ✅ Validar documentos

#### Tests de Servicio:
- ✅ Gestión de aspirantes
- ✅ Exportación de datos
- ✅ Validación de documentos
- ✅ Procesamiento de inscripciones

#### Tests de Repositorio:
- ✅ Búsqueda por programa y persona
- ✅ Conteo por estado
- ✅ Estadísticas de aspirantes
- ✅ Tendencia de inscripciones

### Procesos de Inscripción

#### Tests de Controlador:
- ✅ Inscripción general
- ✅ Inscripción a programa específico
- ✅ Formularios de inscripción
- ✅ Procesamiento de inscripciones

#### Tests de Servicio:
- ✅ Preparación de formularios
- ✅ Procesamiento de inscripciones
- ✅ Creación/actualización de personas y usuarios
- ✅ Gestión de caracterizaciones

#### Tests de Request:
- ✅ Validación de datos de inscripción
- ✅ Validación de documentos
- ✅ Validación de caracterización

### Validación SOFIA

#### Tests de Controlador:
- ✅ Iniciar validación SOFIA
- ✅ Obtener progreso de validación

#### Tests de Servicio:
- ✅ Cliente HTTP para SOFIA
- ✅ Mapeo de estados
- ✅ Procesamiento de validaciones
- ✅ Servicio de validación completo

#### Tests de Job/Comando:
- ✅ Procesamiento en cola
- ✅ Comando de validación
- ✅ Manejo de errores

### Estadísticas y Reportes

#### Tests de Controlador:
- ✅ Dashboard de estadísticas
- ✅ API de estadísticas con filtros

#### Tests de Servicio:
- ✅ Estadísticas reales de BD
- ✅ Estadísticas filtradas
- ✅ Reportes de tendencias
- ✅ Estadísticas por género y edad

---

## 🧪 Metodología de Testing

### Patrones Utilizados

#### 1. Arrange-Act-Assert (AAA)
```php
#[Test]
public function puede_crear_programa_complementario()
{
    // Arrange
    $this->actingAs($this->user);
    $data = [...];

    // Act
    $response = $this->post(route('complementarios-ofertados.store'), $data);

    // Assert
    $response->assertRedirect(route('complementarios-ofertados.index'));
    $this->assertDatabaseHas('complementarios_ofertados', [...]);
}
```

#### 2. Mocking de Dependencias
```php
protected function setUp(): void
{
    parent::setUp();
    
    $this->temaRepositoryMock = Mockery::mock(TemaRepository::class);
    $this->programaRepositoryMock = Mockery::mock(ComplementarioOfertadoRepository::class);
    
    $this->service = new ComplementarioService(
        $this->temaRepositoryMock,
        $this->programaRepositoryMock,
        $this->aspiranteRepositoryMock
    );
}
```

#### 3. Database Testing
```php
use RefreshDatabase;
use SeedsComplementariosDatabase;

protected function setUp(): void
{
    parent::setUp();
    $this->seedComplementariosDatabaseIfNeeded();
}
```

#### 4. Factory States
```php
ComplementarioOfertado::factory()->conOferta()->create();
ComplementarioOfertado::factory()->sinOferta()->create();
ComplementarioOfertado::factory()->cuposLlenos()->create();

AspiranteComplementario::factory()->admitido()->create();
AspiranteComplementario::factory()->enProceso()->create();
AspiranteComplementario::factory()->completo()->create();
```

### Configuración de Base de Datos

#### RefreshDatabase
- Todos los tests usan `RefreshDatabase` para garantizar un estado limpio
- Se combina con `SeedsComplementariosDatabase` para seeding optimizado
- Las transacciones se manejan automáticamente

#### Factories Especializadas
```php
// Factory de ComplementarioOfertado
public function conOferta()
{
    return $this->state(['estado' => 1]);
}

public function sinOferta()
{
    return $this->state(['estado' => 0]);
}

public function cuposLlenos()
{
    return $this->state(['estado' => 2]);
}

// Factory de AspiranteComplementario
public function admitido()
{
    return $this->state(['estado' => 3]);
}

public function enProceso()
{
    return $this->state(['estado' => 1]);
}

public function completo()
{
    return $this->state(['estado' => 2]);
}

public function paraPrograma($programa)
{
    return $this->state(['complementario_id' => $programa->id]);
}
```

---

## 📈 Métricas y Cobertura

### Totales por Categoría

| Categoría | Cantidad de Tests | Cobertura Estimada |
|-----------|-------------------|-------------------|
| **Feature Tests** | 70+ tests | 85% |
| **Unit Tests - Servicios** | 80+ tests | 90% |
| **Unit Tests - Repositorios** | 65+ tests | 95% |
| **Unit Tests - Modelos** | 40+ tests | 80% |
| **Unit Tests - Requests** | 30+ tests | 95% |
| **Unit Tests - Jobs/Comandos** | 15+ tests | 75% |
| **TOTAL** | **300+ tests** | **87%** |

### Cobertura por Funcionalidad

| Funcionalidad | Tests | Cobertura |
|---------------|-------|-----------|
| Gestión de Programas | 100+ tests | 90% |
| Gestión de Aspirantes | 80+ tests | 85% |
| Procesos de Inscripción | 60+ tests | 88% |
| Validación SOFIA | 40+ tests | 82% |
| Estadísticas y Reportes | 30+ tests | 80% |
| Documentos y Validación | 20+ tests | 75% |

---

## 🚀 Ejecución de Tests

### Comandos Disponibles

```bash
# Todos los tests del módulo de Complementarios
php artisan test --filter Complementario

# Tests específicos por categoría
php artisan test tests/Complementarios/Feature/
php artisan test tests/Complementarios/Unit/

# Tests específicos por archivo
php artisan test tests/Complementarios/Feature/Controllers/ProgramaComplementarioControllerTest.php
php artisan test tests/Complementarios/Unit/Services/ComplementarioServiceTest.php

# Con cobertura (requiere Xdebug o PCOV)
php artisan test --coverage --filter Complementario
```

### Configuración para Desarrollo

#### 1. Requisitos Previos
```bash
# Instalar dependencias
composer install
npm install

# Configurar base de datos de testing
cp .env.example .env.testing
# Editar .env.testing con configuración de testing
```

#### 2. Base de Datos de Testing
```bash
# Crear base de datos de testing
php artisan db:create --env=testing

# Ejecutar migraciones de testing
php artisan migrate --env=testing

# Ejecutar seeders de testing
php artisan db:seed --env=testing --class=Database\\Seeders\\TestingSeeder
```

#### 3. Ejecución Rápida
```bash
# Ejecutar tests en paralelo (si está configurado)
php artisan test --parallel

# Ejecutar tests con reporte de cobertura
php artisan test --coverage --min=80

# Ejecutar tests específicos con salida detallada
php artisan test --filter "puede_crear_programa_complementario" --verbose
```

---

## 🔍 Casos de Prueba Destacados

### 1. Creación de Programa con Estructura Académica Completa

**Archivo:** `ProgramaComplementarioControllerTest.php`

```php
#[Test]
public function puede_crear_programa_complementario_con_estructura_academica()
{
    // Arrange: Crear competencia, RAP y guía
    $competencia = Competencia::create([...]);
    $rap = ResultadosAprendizaje::create([...]);
    $guia = GuiasAprendizaje::create([...]);

    // Act: Crear programa con estructura académica
    $response = $this->post(route('complementarios-ofertados.store'), [
        'competencias' => [$competencia->id],
        'raps' => [$rap->id],
        'guias' => [$guia->id],
        // ... otros datos del programa
    ]);

    // Assert: Verificar creación y sincronización
    $response->assertRedirect(route('complementarios-ofertados.index'));
    $programa = ComplementarioOfertado::where('codigo', 'COMP0002')->first();
    $this->assertTrue($programa->competencias->contains($competencia->id));
    $this->assertTrue($programa->raps->contains($rap->id));
    $this->assertTrue($programa->guiasAprendizaje->contains($guia->id));
}
```

### 2. Validación SOFIA con Procesamiento en Cola

**Archivo:** `ValidarSofiaJobTest.php`

```php
#[Test]
public function puede_procesar_validacion_sofia_en_cola()
{
    // Arrange: Crear aspirantes y progreso de validación
    $aspirantes = AspiranteComplementario::factory()->count(10)->create();
    $progress = SofiaValidationProgress::create([
        'total_records' => 10,
        'processed_records' => 0,
        'status' => 'pending'
    ]);

    // Act: Ejecutar job de validación
    $job = new ValidarSofiaJob($progress->id);
    $job->handle($this->createMock(SofiaValidationService::class));

    // Assert: Verificar progreso y resultados
    $progress->refresh();
    $this->assertEquals('completed', $progress->status);
    $this->assertEquals(10, $progress->processed_records);
    $this->assertGreaterThan(0, $progress->validated_records);
}
```

### 3. Exportación de Aspirantes a Excel

**Archivo:** `AspiranteExportServiceTest.php`

```php
#[Test]
public function puede_exportar_aspirantes_a_excel()
{
    // Arrange: Crear programa con aspirantes
    $programa = ComplementarioOfertado::factory()->create();
    $aspirantes = AspiranteComplementario::factory()
        ->count(5)
        ->paraPrograma($programa)
        ->create();

    // Act: Exportar a Excel
    $service = new AspiranteExportService();
    $filePath = $service->exportarAspirantesExcel($programa->id);

    // Assert: Verificar archivo generado
    $this->assertFileExists($filePath);
    $this->assertEquals('xlsx', pathinfo($filePath, PATHINFO_EXTENSION));
    
    // Leer archivo y verificar contenido
    $spreadsheet = IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    $this->assertGreaterThan(5, $worksheet->getHighestRow());
}
```

### 4. Estadísticas Filtradas por Fecha y Programa

**Archivo:** `EstadisticaComplementarioServiceTest.php`

```php
#[Test]
public function puede_obtener_estadisticas_filtradas()
{
    // Arrange: Crear datos de prueba con diferentes fechas
    $programa1 = ComplementarioOfertado::factory()->create();
    $programa2 = ComplementarioOfertado::factory()->create();
    
    // Aspirantes en diferentes meses
    AspiranteComplementario::factory()
        ->paraPrograma($programa1)
        ->create(['created_at' => now()->subMonths(2)]);
    
    AspiranteComplementario::factory()
        ->paraPrograma($programa2)
        ->create(['created_at' => now()->subMonth()]);

    // Act: Obtener estadísticas filtradas
    $service = new EstadisticaComplementarioService();
    $filtros = [
        'fecha_inicio' => now()->subMonths(3)->format('Y-m-d'),
        'fecha_fin' => now()->format('Y-m-d'),
        'programa_id' => $programa1->id
    ];
    
    $estadisticas = $service->obtenerEstadisticasFiltradas($filtros);

    // Assert: Verificar filtrado correcto
    $this->assertEquals(1, $estadisticas['total_aspirantes']);
    $this->assertEquals($programa1->nombre, $estadisticas['programa_nombre']);
}
```

---

## 🛠️ Mejores Prácticas de Testing

### 1. Organización de Tests

- **Nombres descriptivos**: Usar nombres que describan el comportamiento esperado
- **Agrupación lógica**: Agrupar tests relacionados en métodos o clases
- **Comentarios claros**: Documentar casos de prueba complejos

### 2. Mantenibilidad

- **DRY (Don't Repeat Yourself)**: Usar traits, factories y helpers
- **Setup optimizado**: Configurar datos de prueba eficientemente
- **Cleanup automático**: Usar `RefreshDatabase` y transacciones

### 3. Aserciones Específicas

- **Aserciones precisas**: Evitar aserciones genéricas
- **Mensajes claros**: Incluir mensajes descriptivos en aserciones
- **Validación completa**: Verificar todos los aspectos relevantes

### 4. Performance

- **Mocking estratégico**: Mockear dependencias externas costosas
- **Seeding optimizado**: Usar `SeedsComplementariosDatabase`
- **Datos mínimos**: Crear solo los datos necesarios para cada test

---

## 📚 Recursos Adicionales

### Documentación Relacionada

- **Módulo de Complementarios**: Ver `docs/modulo-complementarios.md`
- **Guía de Testing de Laravel**: Documentación oficial de Laravel Testing
- **PHPUnit Documentation**: Documentación oficial de PHPUnit
- **Mockery Documentation**: Documentación de Mockery para mocking

### Herramientas Recomendadas

- **PHPUnit**: Framework de testing principal
- **Mockery**: Biblioteca para mocking de objetos
- **Faker**: Generación de datos de prueba realistas
- **Laravel Dusk**: Testing de navegador (si es necesario)

---

## 👥 Contribuidores

Estos tests fueron desarrollados como parte del sistema CDATTG Web para garantizar la calidad del módulo de Complementarios.

---

## 📅 Versión

**Versión actual:** 1.0.0  
**Última actualización:** 2025  
**Total de tests:** 300+ tests

---

## 📞 Soporte

Para consultas o problemas relacionados con los tests del módulo de Complementarios, contactar al equipo de desarrollo.

### Comandos de Diagnóstico

```bash
# Verificar estado de tests
php artisan test --filter Complementario --stop-on-failure

# Ejecutar tests con reporte detallado
php artisan test --filter Complementario --testdox

# Limpiar cache antes de ejecutar tests
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Logs de Testing

Los logs de ejecución de tests se encuentran en:
- `storage/logs/laravel.log` (durante ejecución de tests)
- Salida de consola (para resultados detallados)

---

## 🔄 Mantenimiento y Actualización

### Agregar Nuevos Tests

1. **Identificar funcionalidad**: Determinar qué funcionalidad necesita testing
2. **Seleccionar tipo**: Decidir si es test de feature o unitario
3. **Crear archivo**: En la ubicación correspondiente según la estructura
4. **Seguir convenciones**: Usar los patrones establecidos en los tests existentes
5. **Ejecutar y verificar**: Asegurar que el test pasa correctamente

### Actualizar Tests Existentes

1. **Refactorizar con cuidado**: Mantener la cobertura existente
2. **Actualizar datos de prueba**: Ajustar factories y seeders si es necesario
3. **Verificar regresiones**: Ejecutar todos los tests relacionados
4. **Documentar cambios**: Actualizar esta documentación si corresponde

### Ejecución Continua

Recomendado ejecutar los tests:
- **Antes de cada commit**: Para prevenir regresiones
- **En CI/CD**: Como parte del pipeline de integración continua
- **Después de refactorizaciones**: Para verificar que nada se rompió

---

**Nota**: Esta documentación se actualiza periódicamente para reflejar cambios en la suite de tests. Para la versión más reciente, consultar el código fuente en `tests/Complementarios/`.
