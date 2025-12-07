# ESPECIFICACIÓN DE REQUERIMIENTOS DE SOFTWARE (SRS)
## RF-PC-006: Endpoints de Gestión de Programas Complementarios

**Versión:** 1.0  
**Fecha:** 2025-12-07  
**Autor:** Equipo de Desarrollo  
**Cliente:** SENA - Centro de Desarrollo Agroempresarial y Turístico del Guaviare  
**Estándar:** IEEE 830-1998  
**Estado:** Aprobado

---

## CONTROL DE VERSIONES

| Versión | Fecha | Autor | Descripción |
|---------|-------|-------|-------------|
| 1.0 | 2025-12-07 | Equipo de Desarrollo | Versión inicial del documento SRS para endpoints |

---

## 1. INTRODUCCIÓN

### 1.1 Propósito

Este documento especifica los requerimientos funcionales y no funcionales para los **endpoints de gestión de programas complementarios** (RF-PC-006), que forman parte del módulo de Gestión de Programas Complementarios del sistema CDATTG Web. Estos endpoints proporcionan interfaces RESTful para operaciones CRUD sobre programas complementarios.

### 1.2 Alcance

Este SRS cubre todos los endpoints HTTP relacionados con la gestión de programas complementarios, incluyendo creación, lectura, actualización, eliminación (CRUD), así como endpoints especializados para operaciones específicas como asignación de competencias, gestión de aspirantes, y consultas estadísticas.

### 1.3 Definiciones, Acrónimos y Abreviaciones

- **SRS**: Software Requirements Specification
- **RF**: Requerimiento Funcional
- **RNF**: Requerimiento No Funcional
- **CDATTG**: Centro de Desarrollo Agroempresarial y Turístico del Guaviare
- **SENA**: Servicio Nacional de Aprendizaje
- **API**: Application Programming Interface
- **REST**: Representational State Transfer
- **CRUD**: Create, Read, Update, Delete
- **HTTP**: Hypertext Transfer Protocol
- **JSON**: JavaScript Object Notation

### 1.4 Referencias

- IEEE Std 830-1998, IEEE Recommended Practice for Software Requirements Specifications
- Documentación del módulo: `docs/modulo-complementarios.md`
- RF-PC-001: Crear Programa Complementario
- RF-PC-002: Ver Detalles del Programa Complementario
- RF-PC-003: Eliminar Programa Complementario
- RF-PC-004: Asignar Competencias al Programa
- RF-PC-005: Listar Programas Complementarios

---

## 2. DESCRIPCIÓN GENERAL

### 2.1 Perspectiva del Requerimiento

Estos endpoints son parte integral del módulo de Gestión de Programas Complementarios y proporcionan interfaces programáticas para que clientes web, móviles y otros sistemas interactúen con la funcionalidad de gestión de programas. Los endpoints siguen principios RESTful y utilizan convenciones HTTP estándar.

### 2.2 Funciones del Requerimiento

- Proporcionar endpoints RESTful para operaciones CRUD sobre programas complementarios
- Ofrecer endpoints para gestión de relaciones (competencias, aspirantes, etc.)
- Implementar validación de datos y manejo de errores estándar
- Garantizar seguridad mediante autenticación y autorización
- Proporcionar documentación consistente de parámetros y respuestas
- Implementar paginación, filtrado y ordenamiento donde sea apropiado

### 2.3 Características del Usuario

**Usuarios Principales:**
- Clientes web (frontend JavaScript)
- Aplicaciones móviles
- Sistemas externos de integración
- Herramientas de administración

### 2.4 Restricciones

- Todos los endpoints requieren autenticación mediante token Bearer
- Acceso restringido por roles (Administrador, Instructor, etc.)
- Formato de datos: JSON para request/response
- Codificación: UTF-8
- Límites de tasa (rate limiting) aplicados
- Validación de datos en todos los endpoints

### 2.5 Suposiciones y Dependencias

- El sistema de autenticación está disponible y funcionando
- La base de datos está disponible y accesible
- Los clientes conocen y respetan convenciones RESTful
- Los clientes manejan adecuadamente códigos de estado HTTP

---

## 3. REQUERIMIENTOS FUNCIONALES

### 3.1 RF-PC-006.01: Endpoints CRUD de Programas Complementarios

**Identificador:** RF-PC-006.01  
**Título:** Endpoints CRUD de Programas Complementarios  
**Versión:** 1.0  
**Prioridad:** Crítica  
**Urgencia:** Alta

#### 3.1.1 Descripción

El sistema debe proporcionar endpoints RESTful completos para operaciones CRUD (Crear, Leer, Actualizar, Eliminar) sobre programas complementarios, siguiendo convenciones HTTP y proporcionando respuestas JSON consistentes.

#### 3.1.2 Endpoints Específicos

##### **GET /api/complementarios-ofertados**
**Descripción:** Listar todos los programas complementarios  
**Método:** GET  
**Autenticación:** Requerida  
**Permisos:** Cualquier usuario autenticado  
**Parámetros Query:**
- `page` (opcional): Número de página para paginación
- `per_page` (opcional): Elementos por página (default: 15)
- `estado` (opcional): Filtrar por estado (0=Sin Oferta, 1=Con Oferta, 2=Cupos Llenos)
- `modalidad_id` (opcional): Filtrar por modalidad
- `search` (opcional): Búsqueda textual en nombre y código
- `sort_by` (opcional): Campo para ordenar (nombre, codigo, created_at)
- `sort_order` (opcional): Dirección (asc, desc)

**Respuesta Exitosa (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "codigo": "COMP001",
      "nombre": "Auxiliar de Cocina",
      "justificacion": "Formación en técnicas básicas de cocina",
      "duracion": 120,
      "cupos": 30,
      "estado_id": 1,
      "estado": "Con Oferta",
      "modalidad_id": 18,
      "jornada_id": 1,
      "ambiente_id": 5,
      "created_at": "2025-01-15T10:30:00Z",
      "updated_at": "2025-01-15T10:30:00Z",
      "modalidad": {
        "id": 18,
        "parametro": {
          "id": 18,
          "name": "PRESENCIAL"
        }
      },
      "jornada": {
        "id": 1,
        "name": "Diurna"
      },
      "ambiente": {
        "id": 5,
        "title": "Laboratorio de Cocina"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 75
  },
  "links": {
    "first": "/api/complementarios-ofertados?page=1",
    "last": "/api/complementarios-ofertados?page=5",
    "prev": null,
    "next": "/api/complementarios-ofertados?page=2"
  }
}
```

##### **GET /api/complementarios-ofertados/{id}**
**Descripción:** Obtener detalles de un programa específico  
**Método:** GET  
**Autenticación:** Requerida  
**Permisos:** Cualquier usuario autenticado  
**Parámetros Path:**
- `id` (requerido): ID del programa complementario

**Respuesta Exitosa (200 OK):**
```json
{
  "data": {
    "id": 1,
    "codigo": "COMP001",
    "nombre": "Auxiliar de Cocina",
    "justificacion": "Formación en técnicas básicas de cocina",
    "requisitos_ingreso": "Mayor de 16 años, documento de identidad",
    "duracion": 120,
    "cupos": 30,
    "estado_id": 1,
    "estado": "Con Oferta",
    "modalidad_id": 18,
    "jornada_id": 1,
    "ambiente_id": 5,
    "created_at": "2025-01-15T10:30:00Z",
    "updated_at": "2025-01-15T10:30:00Z",
    "modalidad": {
      "id": 18,
      "parametro": {
        "id": 18,
        "name": "PRESENCIAL"
      }
    },
    "jornada": {
      "id": 1,
      "name": "Diurna"
    },
    "ambiente": {
      "id": 5,
      "title": "Laboratorio de Cocina"
    },
    "dias_formacion": [
      {
        "id": 1,
        "name": "Lunes",
        "pivot": {
          "hora_inicio": "08:00:00",
          "hora_fin": "12:00:00"
        }
      }
    ],
    "competencias": [
      {
        "id": 1,
        "codigo": "210201501",
        "nombre": "Preparar alimentos según receta estándar"
      }
    ],
    "raps": [
      {
        "id": 1,
        "codigo": "RA21020150101",
        "descripcion": "Identificar ingredientes según receta"
      }
    ],
    "aspirantes_count": 25
  }
}
```

##### **POST /api/complementarios-ofertados**
**Descripción:** Crear un nuevo programa complementario  
**Método:** POST  
**Autenticación:** Requerida  
**Permisos:** Administrador  
**Content-Type:** application/json

**Request Body:**
```json
{
  "codigo": "COMP999",
  "nombre": "Nuevo Programa de Prueba",
  "justificacion": "Justificación del programa",
  "requisitos_ingreso": "Requisitos de ingreso",
  "duracion": 100,
  "cupos": 25,
  "estado_id": 1,
  "modalidad_id": 18,
  "jornada_id": 1,
  "ambiente_id": 5,
  "dias_formacion": [
    {
      "dia_id": 1,
      "hora_inicio": "08:00",
      "hora_fin": "12:00"
    }
  ],
  "competencias": [1, 2, 3],
  "raps": [1, 2]
}
```

**Respuesta Exitosa (201 Created):**
```json
{
  "message": "Programa complementario creado exitosamente",
  "data": {
    "id": 100,
    "codigo": "COMP999",
    "nombre": "Nuevo Programa de Prueba",
    "justificacion": "Justificación del programa",
    "requisitos_ingreso": "Requisitos de ingreso",
    "duracion": 100,
    "cupos": 25,
    "estado_id": 1,
    "modalidad_id": 18,
    "jornada_id": 1,
    "ambiente_id": 5,
    "created_at": "2025-12-07T14:30:00Z",
    "updated_at": "2025-12-07T14:30:00Z"
  }
}
```

##### **PUT /api/complementarios-ofertados/{id}**
**Descripción:** Actualizar un programa existente  
**Método:** PUT  
**Autenticación:** Requerida  
**Permisos:** Administrador  
**Content-Type:** application/json

**Request Body:** (campos a actualizar)
```json
{
  "nombre": "Programa Actualizado",
  "cupos": 35,
  "estado_id": 2,
  "justificacion": "Justificación actualizada"
}
```

**Respuesta Exitosa (200 OK):**
```json
{
  "message": "Programa complementario actualizado exitosamente",
  "data": {
    "id": 1,
    "codigo": "COMP001",
    "nombre": "Programa Actualizado",
    "justificacion": "Justificación actualizada",
    "requisitos_ingreso": "Mayor de 16 años, documento de identidad",
    "duracion": 120,
    "cupos": 35,
    "estado_id": 2,
    "modalidad_id": 18,
    "jornada_id": 1,
    "ambiente_id": 5,
    "created_at": "2025-01-15T10:30:00Z",
    "updated_at": "2025-12-07T14:35:00Z"
  }
}
```

##### **DELETE /api/complementarios-ofertados/{id}**
**Descripción:** Eliminar un programa complementario  
**Método:** DELETE  
**Autenticación:** Requerida  
**Permisos:** Administrador

**Respuesta Exitosa (200 OK):**
```json
{
  "message": "Programa complementario eliminado exitosamente"
}
```

#### 3.1.3 Validaciones

**Validaciones Comunes:**
- `codigo`: Requerido, único, máximo 20 caracteres
- `nombre`: Requerido, máximo 255 caracteres
- `duracion`: Requerido, numérico, mínimo 1
- `cupos`: Requerido, numérico, mínimo 1
- `estado_id`: Requerido, existe en `parametros_temas`
- `modalidad_id`: Requerido, existe en `parametros_temas`
- `jornada_id`: Requerido, existe en `jornadas_formacion`
- `ambiente_id`: Opcional, existe en `ambientes`

#### 3.1.4 Códigos de Error

**400 Bad Request:** Datos de entrada inválidos
```json
{
  "message": "Los datos proporcionados son inválidos",
  "errors": {
    "codigo": ["El campo código es requerido"],
    "nombre": ["El campo nombre es requerido"]
  }
}
```

**401 Unauthorized:** No autenticado
```json
{
  "message": "No autenticado"
}
```

**403 Forbidden:** Sin permisos
```json
{
  "message": "No tiene permisos para realizar esta acción"
}
```

**404 Not Found:** Recurso no encontrado
```json
{
  "message": "Programa complementario no encontrado"
}
```

**409 Conflict:** Conflicto (ej. código duplicado)
```json
{
  "message": "El código del programa ya existe"
}
```

**422 Unprocessable Entity:** Validación fallida
```json
{
  "message": "Los datos proporcionados no pasaron la validación",
  "errors": {
    "cupos": ["Los cupos deben ser un número positivo"]
  }
}
```

**500 Internal Server Error:** Error del servidor
```json
{
  "message": "Error interno del servidor"
}
```

### 3.2 RF-PC-006.02: Endpoints de Gestión de Competencias

**Identificador:** RF-PC-006.02  
**Título:** Endpoints de Gestión de Competencias  
**Versión:** 1.0  
**Prioridad:** Alta  
**Urgencia:** Media

#### 3.2.1 Descripción

Endpoints para gestionar la relación entre programas complementarios y competencias, incluyendo asignación, consulta y eliminación de competencias asociadas.

#### 3.2.2 Endpoints Específicos

##### **GET /api/complementarios-ofertados/{id}/competencias**
**Descripción:** Listar competencias asociadas a un programa  
**Método:** GET  
**Autenticación:** Requerida  
**Permisos:** Cualquier usuario autenticado

##### **POST /api/complementarios-ofertados/{id}/competencias**
**Descripción:** Asignar competencias a un programa  
**Método:** POST  
**Autenticación:** Requerida  
**Permisos:** Administrador, Instructor

**Request Body:**
```json
{
  "competencias": [1, 2, 3],
  "user_create_id": 1
}
```

##### **DELETE /api/complementarios-ofertados/{id}/competencias/{competencia_id}**
**Descripción:** Remover competencia de un programa  
**Método:** DELETE  
**Autenticación:** Requerida  
**Permisos:** Administrador, Instructor

### 3.3 RF-PC-006.03: Endpoints de Gestión de Aspirantes

**Identificador:** RF-PC-006.03  
**Título:** Endpoints de Gestión de Aspirantes  
**Versión:** 1.0  
**Prioridad:** Alta  
**Urgencia:** Alta

#### 3.3.1 Descripción

Endpoints para gestionar aspirantes inscritos en programas complementarios, incluyendo inscripción, consulta, actualización de estado y eliminación.

#### 3.3.2 Endpoints Específicos

##### **GET /api/complementarios-ofertados/{id}/aspirantes**
**Descripción:** Listar aspirantes de un programa  
**Método:** GET  
**Autenticación:** Requerida  
**Permisos:** Administrador, Instructor  
**Parámetros Query:**
- `estado` (opcional): Filtrar por estado del aspirante (1=En proceso, 3=Admitido, 4=Rechazado)
- `search` (opcional): Búsqueda en nombres y documento
- `page` (opcional): Paginación

**Respuesta Exitosa (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "persona_id": 100,
      "complementario_id": 1,
      "estado": 1,
      "observaciones": "Aspirante en proceso",
      "created_at": "2025-01-20T09:00:00Z",
      "updated_at": "2025-01-20T09:00:00Z",
      "persona": {
        "id": 100,
        "numero_documento": "12345678",
        "primer_nombre": "Juan",
        "primer_apellido": "Pérez",
        "email": "juan@example.com",
        "celular": "3001234567"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 25,
    "per_page": 15
  }
}
```

##### **POST /api/complementarios-ofertados/{id}/aspirantes**
**Descripción:** Inscribir un nuevo aspirante  
**Método:** POST  
**Autenticación:** Requerida  
**Permisos:** Administrador, Instructor  
**Content-Type:** application/json

**Request Body:**
```json
{
  "persona_id": 100,
  "observaciones": "Aspirante nuevo",
  "estado": 1
}
```

##### **PUT /api/complementarios-ofertados/{id}/aspirantes/{aspirante_id}**
**Descripción:** Actualizar estado de un aspirante  
**Método:** PUT  
**Autenticación:** Requerida  
**Permisos:** Administrador, Instructor

**Request Body:**
```json
{
  "estado": 3,
  "observaciones": "Aspirante admitido"
}
```

##### **DELETE /api/complementarios-ofertados/{id}/aspirantes/{aspirante_id}**
**Descripción:** Eliminar aspirante de un programa  
**Método:** DELETE  
**Autenticación:** Requerida  
**Permisos:** Administrador

### 3.4 RF-PC-006.04: Endpoints de Consultas y Estadísticas

**Identificador:** RF-PC-006.04  
**Título:** Endpoints de Consultas y Estadísticas  
**Versión:** 1.0  
**Prioridad:** Media  
**Urgencia:** Baja

#### 3.4.1 Descripción

Endpoints para consultas especializadas y estadísticas sobre programas complementarios, incluyendo reportes, métricas y datos agregados.

#### 3.4.2 Endpoints Específicos

##### **GET /api/complementarios/estadisticas**
**Descripción:** Obtener estadísticas generales de programas  
**Método:** GET  
**Autenticación:** Requerida  
**Permisos:** Administrador, Instructor

**Respuesta Exitosa (200 OK):**
```json
{
  "total_programas": 75,
  "programas_activos": 45,
  "programas_sin_oferta": 15,
  "programas_cupos_llenos": 15,
  "total_aspirantes": 1250,
  "aspirantes_admitidos": 850,
  "aspirantes_pendientes": 400,
  "tasa_ocupacion_promedio": 68.5
}
```

##### **GET /api/complementarios-ofertados/{id}/reporte-demanda**
**Descripción:** Obtener reporte de demanda del programa  
**Método:** GET  
**Autenticación:** Requerida  
**Permisos:** Administrador, Instructor

##### **GET /api/complementarios/competencias**
**Descripción:** Listar todas las competencias disponibles  
**Método:** GET  
**Autenticación:** Requerida  
**Permisos:** Cualquier usuario autenticado

##### **GET /api/complementarios/raps**
**Descripción:** Obtener RAPs por competencias  
**Método:** GET  
**Autenticación:** Requerida  
**Permisos:** Cualquier usuario autenticado  
**Parámetros Query:**
- `competencias` (requerido): IDs de competencias separados por coma

##### **GET /api/complementarios/guias-aprendizaje**
**Descripción:** Obtener guías de aprendizaje disponibles  
**Método:** GET  
**Autenticación:** Requerida  
**Permisos:** Cualquier usuario autenticado

---
