# ESPECIFICACIÓN DE REQUERIMIENTOS DE SOFTWARE (SRS)
## RF-PC-005: Listar Programas Complementarios

**Versión:** 1.0  
**Fecha:** 2025-01-XX  
**Autor:** Equipo de Desarrollo  
**Cliente:** SENA - Centro de Desarrollo Agroempresarial y Turístico del Guaviare  
**Estándar:** IEEE 830-1998  
**Estado:** Aprobado

---

## CONTROL DE VERSIONES

| Versión | Fecha | Autor | Descripción |
|---------|-------|-------|-------------|
| 1.0 | 2025-01-XX | Equipo de Desarrollo | Versión inicial del documento SRS |

---

## 1. INTRODUCCIÓN

### 1.1 Propósito

Este documento especifica los requerimientos funcionales y no funcionales para el caso de uso **"Listar Programas Complementarios"** (RF-PC-005), que forma parte del módulo de Gestión de Programas Complementarios del sistema CDATTG Web.

### 1.2 Alcance

Este SRS cubre la funcionalidad que permite al administrador visualizar la lista completa de programas complementarios disponibles en el sistema, con opciones de filtrado, búsqueda, ordenamiento y acceso a acciones de gestión sobre cada programa.

### 1.3 Definiciones, Acrónimos y Abreviaciones

- **SRS**: Software Requirements Specification
- **RF**: Requerimiento Funcional
- **RNF**: Requerimiento No Funcional
- **CDATTG**: Centro de Desarrollo Agroempresarial y Turístico del Guaviare
- **SENA**: Servicio Nacional de Aprendizaje
- **Programa Complementario**: Curso de formación complementaria ofertado por el SENA
- **Paginación**: Técnica para dividir grandes conjuntos de resultados en páginas manejables

### 1.4 Referencias

- IEEE Std 830-1998, IEEE Recommended Practice for Software Requirements Specifications
- Documentación del módulo: `docs/modulo-complementarios.md`
- Caso de Uso: CU-PC-05

---

## 2. DESCRIPCIÓN GENERAL

### 2.1 Perspectiva del Requerimiento

Este requerimiento es parte del módulo de Gestión de Programas Complementarios y sirve como punto de entrada principal para la gestión de programas, proporcionando una vista consolidada de todos los programas disponibles con capacidades de búsqueda, filtrado y acceso rápido a operaciones de gestión individual.

### 2.2 Funciones del Requerimiento

- Listar todos los programas complementarios del sistema
- Proporcionar capacidades de búsqueda por nombre, código o descripción
- Ofrecer filtros por estado, modalidad, jornada y otros criterios
- Permitir ordenamiento por diferentes columnas
- Implementar paginación para manejar grandes volúmenes de datos
- Mostrar información resumida de cada programa
- Proporcionar acceso rápido a acciones de gestión (ver, editar, eliminar)
- Incluir estadísticas o resúmenes generales

### 2.3 Características del Usuario

**Actor Principal:** Administrador u Operador del sistema

### 2.4 Restricciones

- Requiere autenticación de usuario
- La lista debe ser eficiente incluso con cientos de programas
- La información mostrada debe estar actualizada en tiempo real
- Los filtros y búsquedas deben ser responsivos

### 2.5 Suposiciones y Dependencias

- Existen programas complementarios registrados en el sistema
- La base de datos contiene la información necesaria para los filtros
- El sistema soporta operaciones de paginación eficientes
- Los usuarios tienen permisos apropiados para ver la lista

---

## 3. REQUERIMIENTOS FUNCIONALES

### 3.1 RF-PC-005: Listar Programas Complementarios

**Identificador:** RF-PC-005  
**Título:** Listar Programas Complementarios  
**Versión:** 1.0  
**Prioridad:** Alta  
**Urgencia:** Alta

#### 3.1.1 Descripción

El sistema debe permitir al administrador visualizar una lista completa de todos los programas complementarios, con capacidades de búsqueda, filtrado, ordenamiento y paginación, mostrando información resumida de cada programa y proporcionando acceso rápido a acciones de gestión.

#### 3.1.2 Objetivos Asociados

- Proporcionar una vista consolidada de todos los programas complementarios del sistema
- Facilitar la búsqueda y localización de programas específicos
- Permitir el análisis y revisión de programas mediante filtros
- Servir como punto de entrada para operaciones de gestión de programas
- Ofrecer una experiencia de usuario eficiente para navegar por el catálogo de programas

#### 3.1.3 Precondiciones

- El usuario debe haber iniciado sesión con rol de administrador u operador
- El usuario debe tener acceso al módulo de gestión de programas complementarios
- Debe existir al menos un programa complementario en el sistema (aunque la lista puede estar vacía)

#### 3.1.4 Secuencia Normal

1. El usuario accede al módulo de gestión de programas complementarios
2. El sistema autentica al usuario mediante middleware `auth`
3. El sistema consulta los programas complementarios desde la base de datos:
   - Aplica filtros activos (si los hay)
   - Aplica criterios de búsqueda (si los hay)
   - Determina ordenamiento según configuración
   - Calcula paginación
4. El sistema muestra la vista principal con:
   - **Barra de Herramientas Superior:**
     - Campo de búsqueda por texto (nombre, código, descripción)
     - Botón "Nuevo Programa" para crear programas
     - Botón "Exportar" para exportar la lista
   - **Panel de Filtros (colapsable):**
     - Filtro por estado (Activo, Inactivo, Finalizado, Todos)
     - Filtro por modalidad (Presencial, Virtual, Mixta)
     - Filtro por jornada (Diurna, Nocturna, Mixta)
     - Filtro por rango de fechas (inicio, fin)
     - Botones "Aplicar Filtros" y "Limpiar Filtros"
   - **Tabla de Programas:**
     - Columnas: Código, Nombre, Modalidad, Jornada, Estado, Cupos (ocupados/totales), Fecha Inicio, Acciones
     - Cada fila representa un programa con información resumida
     - Indicadores visuales para estado (colores o íconos)
   - **Controles de Paginación:**
     - Navegación entre páginas
     - Selector de items por página (10, 25, 50, 100)
     - Contador de resultados (ej: "Mostrando 1-10 de 45 programas")
   - **Resumen/Estadísticas (opcional):**
     - Total de programas activos
     - Total de cupos ocupados vs disponibles
     - Distribución por modalidad o estado
5. El usuario interactúa con la lista:
   - **Búsqueda:** Escribe texto en campo de búsqueda, la lista se actualiza en tiempo real o al presionar Enter
   - **Filtrado:** Aplica filtros desde el panel, la lista se actualiza
   - **Ordenamiento:** Hace clic en encabezados de columna para ordenar ascendente/descendente
   - **Paginación:** Navega entre páginas o cambia items por página
   - **Acciones:** Para cada programa, puede hacer clic en:
     - **Ver:** Accede a vista de detalle del programa
     - **Editar:** Accede a formulario de edición
     - **Eliminar:** Inicia proceso de eliminación (con confirmación)
     - **Competencias:** Accede a gestión de competencias del programa
6. El sistema responde a las interacciones actualizando la vista sin recarga completa (AJAX) o con recarga parcial según implementación

#### 3.1.5 Excepciones

**E-001:** Si no hay programas que coincidan con los criterios
- **Condición:** La consulta retorna cero resultados después de aplicar filtros/búsqueda
- **Acción:** El sistema muestra mensaje: "No se encontraron programas que coincidan con los criterios de búsqueda"
- **Código de Error:** N/A (mensaje informativo)
- **Log:** Se registra la consulta sin resultados (opcional)

**E-002:** Si hay error al cargar la lista
- **Condición:** Se produce excepción en la consulta a base de datos
- **Acción:** El sistema muestra mensaje: "Error al cargar la lista de programas. Por favor intente nuevamente."
- **Código de Error:** 500 (Internal Server Error)
- **Log:** Se registra el error con stack trace completo

**E-003:** Si el usuario no tiene permisos para ver la lista
- **Condición:** Validación de permisos falla
- **Acción:** El sistema retornará error 403 con mensaje: "No tiene permisos para ver la lista de programas"
- **Código de Error:** 403 (Forbidden)
- **Log:** Se registrará el intento de acceso no autorizado

#### 3.1.6 Postcondiciones

- El usuario visualiza la lista de programas según los criterios aplicados
- La información mostrada está actualizada al momento de la consulta
- El usuario puede interactuar con la lista (buscar, filtrar, ordenar, paginar)
- El usuario puede acceder a acciones de gestión sobre programas individuales
- La experiencia de usuario es fluida y responsiva

#### 3.1.7 Requisitos Asociados

- **RF-PC-001:** Crear Programa Complementario (acción disponible desde esta vista mediante "Nuevo Programa")
- **RF-PC-002:** Ver Detalles del Programa Complementario (acción disponible desde esta vista)
- **RF-PC-003:** Eliminar Programa Complementario (acción disponible desde esta vista)
- **RF-PC-004:** Asignar Competencias al Programa (acción disponible desde esta vista)
- **RNF-01:** Acceso Restringido por Roles
- **RNF-10:** Rendimiento con Grandes Volúmenes de Datos

---

## 4. REQUERIMIENTOS NO FUNCIONALES

### 4.1 RNF-01: Acceso Restringido por Roles

**Prioridad:** Crítica  
**Categoría:** Seguridad

**Descripción:** La funcionalidad de listar programas complementarios debe requerir autenticación de usuario y acceso según roles (Administrador u Operador). El sistema no debe permitir acceso sin sesión activa ni sin los permisos adecuados.

**Criterios de Aceptación:**
- La ruta está protegida con middleware `auth`
- Solo usuarios con rol Administrador u Operador pueden acceder
- Intentos de acceso sin autenticación redirigen a página de login
- No se puede ver la lista de programas sin sesión válida

### 4.2 RNF-10: Rendimiento con Grandes Volúmenes de Datos

**Prioridad:** Alta  
**Categoría:** Rendimiento

**Descripción:** La lista de programas debe cargar y responder eficientemente incluso con grandes volúmenes de datos (500+ programas), optimizando consultas, paginación y recursos del cliente.

**Criterios de Aceptación:**
- Tiempo de carga inicial < 3 segundos con 500 programas
- Búsqueda y filtrado responden en < 2 segundos
- Paginación eficiente con consultas optimizadas
- No hay bloqueo de interfaz durante operaciones
- Uso apropiado de índices en base de datos

### 4.3 RNF-11: Experiencia de Usuario Fluida

**Prioridad:** Alta  
**Categoría:** Usabilidad

**Descripción:** La interfaz debe proporcionar una experiencia fluida con actualizaciones en tiempo real (o casi real), feedback visual inmediato y controles intuitivos.

**Criterios de Aceptación:**
- Búsqueda en tiempo real (debounce implementado)
- Filtros aplicados con feedback visual claro
- Ordenamiento con indicadores visuales de dirección
- Paginación sin recarga completa de página (AJAX)
- Estados de carga y error bien comunicados

---

## 5. CRITERIOS DE ACEPTACIÓN

### 5.1 Criterios Funcionales

**CA-001:** El sistema debe listar todos los programas complementarios
- **Verificación:** Se accede a la lista con 15 programas en sistema
- **Resultado Esperado:** Se muestran los 15 programas en la tabla

**CA-002:** El sistema debe permitir búsqueda por texto
- **Verificación:** Se busca "programa" cuando hay 3 programas con esa palabra en nombre
- **Resultado Esperado:** Se muestran solo los 3 programas que coinciden

**CA-003:** El sistema debe permitir filtrado por estado
- **Verificación:** Se aplica filtro "Estado=Activo" cuando hay 10 activos y 5 inactivos
- **Resultado Esperado:** Se muestran solo los 10 programas activos

**CA-004:** El sistema debe permitir ordenamiento por columnas
- **Verificación:** Se hace clic en columna "Nombre" dos veces
- **Resultado Esperado:** Los programas se ordenan ascendente y luego descendente por nombre

**CA-005:** El sistema debe implementar paginación
- **Verificación:** Hay 45 programas y se configuran 10 por página
- **Resultado Esperado:** Se muestran 5 páginas con controles de navegación

### 5.2 Criterios No Funcionales

**CA-006:** Solo usuarios autorizados pueden ver la lista
- **Verificación:** Usuario sin rol adecuado intenta acceder
- **Resultado Esperado:** Error 403 (Forbidden)

**CA-007:** Rendimiento con volumen grande de datos
- **Verificación:** Se prueba con 300 programas en sistema
- **Resultado Esperado:** Tiempo de carga < 3 segundos, búsqueda responsiva

**CA-008:** Experiencia de usuario fluida
- **Verificación:** Se aplican múltiples filtros y búsquedas rápidamente
- **Resultado Esperado:** Interfaz responsiva sin bloqueos, feedback visual claro

### 5.3 Criterios de Validación

**CA-009:** Manejo de lista vacía
- **Verificación:** No hay programas en sistema o filtros no retornan resultados
- **Resultado Esperado:** Mensaje informativo "No se encontraron programas"

**CA-010:** Manejo de errores de carga
- **Verificación:** Se simula fallo de conexión a BD durante carga
- **Resultado Esperado:** Mensaje de error claro, opción para reintentar

**CA-011:** Persistencia de estado de interfaz
- **Verificación:** Se aplican filtros, se navega a detalle de programa y se regresa
- **Resultado Esperado:** Los filtros aplicados se mantienen

---

## 6. TRAZABILIDAD

### 6.1 Requerimientos Relacionados

| Requerimiento | Relación | Descripción |
|---------------|----------|-------------|
| RF-PC-001 | Siguiente | Crear Programa Complementario (acción disponible desde esta vista) |
| RF-PC-002 | Siguiente | Ver Detalles del Programa Complementario (acción disponible desde esta vista) |
| RF-PC-003 | Siguiente | Eliminar Programa Complementario (acción disponible desde esta vista) |
| RF-PC-004 | Siguiente | Asignar Competencias al Programa (acción disponible desde esta vista) |
| RNF-01 | Depende | Acceso Restringido por Roles |
| RNF-10 | Depende | Rendimiento con Grandes Volúmenes de Datos |
| RNF-11 | Depende | Experiencia de Usuario Fluida |

### 6.2 Casos de Uso Relacionados

- **CU-PC-05:** Listar Programas Complementarios

### 6.3 Componentes del Sistema

- **Controlador:** `ProgramaComplementarioController@index()` o `@listar()`
- **Servicio:** `ProgramaComplementarioService@listarProgramas()`
- **Repositorio:** `ComplementarioOfertadoRepository@listarConFiltros()`
- **Vista:** `resources/views/complementarios/programas/index.blade.php` o `listar.blade.php`
- **Rutas:** `GET /programas-complementarios`

### 6.4 Pruebas Relacionadas

- Test unitario del servicio `listarProgramas()` con diferentes filtros
- Test de integración de la interfaz de lista
- Test de rendimiento con grandes volúmenes de datos
- Test de permisos (solo usuarios autorizados)
- Test de funcionalidades de búsqueda, filtrado, ordenamiento, paginación
- Test de experiencia de usuario (responsividad, feedback)

---

## 7. GLOSARIO

| Término | Definición |
|--------|------------|
| **Programa Complementario** | Curso de formación complementaria ofertado por el SENA |
| **Paginación** | División de grandes conjuntos de resultados en páginas más pequeñas para mejor rendimiento y usabilidad |
| **Filtro** | Criterio aplicado para restringir los resultados mostrados en una lista |
| **Búsqueda en tiempo real** | Actualización automática de resultados mientras el usuario escribe en el campo de búsqueda |
| **Ordenamiento** | Organización de resultados según un criterio específico (ascendente o descendente) |
| **AJAX** | Técnica para actualizar partes de una página web sin recargarla completamente |

---

**FIN DEL DOCUMENTO**
