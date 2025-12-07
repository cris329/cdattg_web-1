# Endpoints de Programas Complementarios

## 📋 Resumen

Este documento lista todos los endpoints HTTP disponibles para la gestión de programas complementarios en el sistema CDATTG Web. Los endpoints están organizados por funcionalidad y categoría.

---

## 🏷️ Convenciones

- **Métodos HTTP:** GET, POST, PUT, DELETE
- **Autenticación:** La mayoría requiere autenticación (middleware `auth`)
- **Prefijos comunes:** `/complementarios`, `/programas-complementarios`
- **Formato:** JSON para request/response (API endpoints)

---

## 📊 Endpoints por Categoría

### 1. Gestión de Programas Complementarios (CRUD)

| Método | Endpoint | Descripción | Controlador | Método |
|--------|----------|-------------|-------------|---------|
| GET | `/complementarios-ofertados` | Listar todos los programas | `ProgramaComplementarioController` | `index()` |
| GET | `/complementarios-ofertados/create` | Formulario de creación | `ProgramaComplementarioController` | `create()` |
| POST | `/complementarios-ofertados` | Crear nuevo programa | `ProgramaComplementarioController` | `store()` |
| GET | `/complementarios-ofertados/{programa}` | Ver detalles de programa | `ProgramaComplementarioController` | `show()` |
| GET | `/complementarios-ofertados/{programa}/edit` | Formulario de edición | `ProgramaComplementarioController` | `edit()` |
| GET | `/complementarios-ofertados/{programa}/edit-api` | Edición vía API | `ProgramaComplementarioController` | `editApi()` |
| PUT | `/complementarios-ofertados/{programa}` | Actualizar programa | `ProgramaComplementarioController` | `update()` |
| DELETE | `/complementarios-ofertados/{programa}` | Eliminar programa | `ProgramaComplementarioController` | `destroy()` |

### 2. Gestión de Aspirantes

#### 2.1 Listado y Consulta
| Método | Endpoint | Descripción | Controlador | Método |
|--------|----------|-------------|-------------|---------|
| GET | `/aspirantes` | Listar todos los aspirantes | `AspiranteComplementarioController` | `index()` |
| GET | `/aspirantes/programa/{programa}` | Aspirantes por programa | `AspiranteComplementarioController` | `programa()` |
| GET | `/gestion-aspirantes` | Gestión de aspirantes | `AspiranteComplementarioController` | `gestionAspirantes()` |
| GET | `/programas-complementarios/{curso}` | Ver aspirantes de programa | `AspiranteComplementarioController` | `verAspirantes()` |

#### 2.2 Creación y Inscripción
| Método | Endpoint | Descripción | Controlador | Método |
|--------|----------|-------------|-------------|---------|
| POST | `/aspirantes/buscar-persona` | Buscar persona para inscripción | `AspiranteComplementarioController` | `buscarPersona()` |
| GET | `/aspirantes/programa/{programa}/create` | Formulario crear aspirante | `AspiranteComplementarioController` | `create()` |
| POST | `/aspirantes/programa/{programa}/store` | Guardar aspirante | `AspiranteComplementarioController` | `store()` |
| POST | `/aspirantes/programa/{programa}/create-new` | Crear nuevo aspirante | `AspiranteComplementarioController` | `storeNewAspirante()` |
| POST | `/programas-complementarios/{programa}/agregar-aspirante` | Agregar aspirante | `AspiranteComplementarioController` | `store()` |
| POST | `/programas-complementarios/{programa}/aspirantes` | Guardar aspirante (API) | `AspiranteComplementarioController` | `store()` |

#### 2.3 Actualización y Eliminación
| Método | Endpoint | Descripción | Controlador | Método |
|--------|----------|-------------|-------------|---------|
| PUT | `/programas-complementarios/{programa}/aspirantes/{aspirante}` | Actualizar aspirante | `AspiranteComplementarioController` | `update()` |
| DELETE | `/programas-complementarios/{complementarioId}/aspirante/{aspiranteId}` | Eliminar aspirante | `AspiranteComplementarioController` | `eliminarAspirante()` |

### 3. Inscripciones

| Método | Endpoint | Descripción | Controlador | Método |
|--------|----------|-------------|-------------|---------|
| GET | `/inscripciones/general` | Inscripción general | `InscripcionComplementarioController` | `inscripcionGeneral()` |
| POST | `/inscripciones/general` | Procesar inscripción general | `InscripcionComplementarioController` | `procesarInscripcionGeneral()` |
| GET | `/inscripciones/{programa}` | Formulario inscripción específica | `InscripcionComplementarioController` | `formularioInscripcion()` |
| POST | `/inscripciones/{programa}` | Procesar inscripción | `InscripcionComplementarioController` | `procesarInscripcion()` |

### 4. Estadísticas y Reportes

| Método | Endpoint | Descripción | Controlador | Método |
|--------|----------|-------------|-------------|---------|
| GET | `/complementarios/estadisticas` | Estadísticas generales | `EstadisticaComplementarioController` | `estadisticas()` |
| GET | `/complementarios/estadisticas/api` | API estadísticas | `EstadisticaComplementarioController` | `apiEstadisticas()` |
| GET | `/complementarios/estadisticas/exportar-excel` | Exportar Excel | `EstadisticaComplementarioController` | `exportarProgramasDemandaExcel()` |
| GET | `/programas-complementarios/{complementarioId}/estadisticas-exclusion` | Estadísticas exclusión | `AspiranteComplementarioController` | `getEstadisticasExclusion()` |

### 5. Exportación y Documentos

| Método | Endpoint | Descripción | Controlador | Método |
|--------|----------|-------------|-------------|---------|
| GET | `/programas-complementarios/{complementarioId}/exportar-excel` | Exportar aspirantes a Excel | `AspiranteComplementarioController` | `exportarAspirantesExcel()` |
| GET | `/programas-complementarios/{complementarioId}/descargar-cedulas` | Descargar cédulas | `AspiranteComplementarioController` | `descargarCedulas()` |
| POST | `/programas-complementarios/{complementarioId}/validar-documentos` | Validar documentos | `AspiranteComplementarioController` | `validarDocumentos()` |
| GET | `/procesar-documentos` | Procesar documentos | `DocumentoComplementarioController` | `procesarDocumentos()` |
| POST | `/procesar-documentos` | Procesar documentos (submit) | `DocumentoComplementarioController` | `procesarDocumentoSubmit()` |

### 6. API Endpoints (JSON)

| Método | Endpoint | Descripción | Controlador | Método |
|--------|----------|-------------|-------------|---------|
| GET | `/api/complementarios/competencias` | Listar competencias | `ComplementarioApiController` | `getCompetencias()` |
| GET | `/api/complementarios/raps` | Obtener RAPs por competencias | `ComplementarioApiController` | `getRapsByCompetencias()` |
| GET | `/api/complementarios/guias-aprendizaje` | Obtener guías de aprendizaje | `ComplementarioApiController` | `getGuiasAprendizaje()` |

---

## 🔐 Permisos y Autenticación

### Niveles de Acceso:
- **Público:** Endpoints de inscripción (`/inscripciones/*`)
- **Autenticado:** La mayoría de endpoints (requiere `auth` middleware)
- **Administrador:** Gestión completa de programas y aspirantes
- **Instructor:** Gestión de aspirantes en sus programas

### Middlewares Comunes:
- `auth`: Autenticación requerida
- `role:admin`: Solo administradores
- `permission`: Permisos específicos

---

## 📝 Notas Técnicas

### 1. Parámetros Comunes:
- `{programa}`: ID del programa complementario
- `{curso}`: Nombre del curso (slug)
- `{complementarioId}`: ID del programa complementario
- `{aspiranteId}`: ID del aspirante

### 2. Convenciones de Nombres:
- **Singular:** Para recursos individuales (`/programa/{id}`)
- **Plural:** Para colecciones (`/aspirantes`)
- **Verbos:** Acciones específicas (`/exportar-excel`, `/validar-documentos`)

### 3. Formatos de Respuesta:
- **Vistas HTML:** Para interfaces web (`*.blade.php`)
- **JSON:** Para API endpoints (`/api/*`)
- **Archivos:** Para exportaciones (Excel, PDF)

### 4. Validaciones:
- Campos requeridos según modelo `ComplementarioOfertado`
- Unicidad de código de programa
- Validación de fechas y números
- Permisos por rol y usuario

---

## 🔗 Rutas Relacionadas

### Archivos de Rutas:
- `routes/complementarios/gestion_programas_complementarios.php`
- `routes/complementarios/aspirantes.php`
- `routes/complementarios/aspirantes_management.php`
- `routes/complementarios/inscripciones.php`
- `routes/complementarios/estadisticas.php`
- `routes/complementarios/procesar_documentos.php`
- `routes/api.php` (para endpoints API)

### Controladores Principales:
- `ProgramaComplementarioController`: Gestión de programas
- `AspiranteComplementarioController`: Gestión de aspirantes
- `InscripcionComplementarioController`: Inscripciones
- `EstadisticaComplementarioController`: Estadísticas
- `DocumentoComplementarioController`: Procesamiento de documentos
- `ComplementarioApiController`: Endpoints API

---

## 🚀 Uso Práctico

### Ejemplo: Crear Programa
```bash
# 1. Acceder al formulario
GET /complementarios-ofertados/create

# 2. Enviar datos
POST /complementarios-ofertados
Content-Type: application/x-www-form-urlencoded

codigo=COMP999&nombre=Nuevo Programa&duracion=120&cupos=30&estado_id=1&modalidad_id=18&jornada_id=1
```

### Ejemplo: Inscribir Aspirante
```bash
# 1. Buscar persona
POST /aspirantes/buscar-persona
Content-Type: application/json

{"documento": "12345678"}

# 2. Inscribir en programa
POST /aspirantes/programa/1/store
Content-Type: application/json

{"persona_id": 100, "observaciones": "Nuevo aspirante"}
```

### Ejemplo: Exportar Datos
```bash
# Exportar aspirantes a Excel
GET /programas-complementarios/1/exportar-excel

# Descargar cédulas
GET /programas-complementarios/1/descargar-cedulas
```

---

## 📄 Documentación Relacionada

- `docs/complementarios/srs-rf-pc-001-crear-programa-complementario.md`
- `docs/complementarios/srs-rf-pc-002-ver-detalles-programa-complementario.md`
- `docs/complementarios/srs-rf-pc-003-eliminar-programa-complementario.md`
- `docs/complementarios/srs-rf-pc-004-asignar-competencias-al-programa.md`
- `docs/complementarios/srs-rf-pc-005-listar-programas-complementarios.md`

---

**Última actualización:** 2025-12-07  
**Versión:** 1.0
