# ESPECIFICACIÓN DE REQUERIMIENTOS DE SOFTWARE (SRS)
## RF-PC-004: Asignar Competencias al Programa

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

Este documento especifica los requerimientos funcionales y no funcionales para el caso de uso **"Asignar Competencias al Programa"** (RF-PC-004), que forma parte del módulo de Gestión de Programas Complementarios del sistema CDATTG Web.

### 1.2 Alcance

Este SRS cubre la funcionalidad que permite al administrador asignar, modificar o eliminar competencias asociadas a un programa complementario, gestionando la relación entre programas y las competencias que estos desarrollan en los aspirantes.

### 1.3 Definiciones, Acrónimos y Abreviaciones

- **SRS**: Software Requirements Specification
- **RF**: Requerimiento Funcional
- **RNF**: Requerimiento No Funcional
- **CDATTG**: Centro de Desarrollo Agroempresarial y Turístico del Guaviare
- **SENA**: Servicio Nacional de Aprendizaje
- **Competencia**: Habilidad, conocimiento o destreza que se espera desarrollar en un programa de formación
- **Programa Complementario**: Curso de formación complementaria ofertado por el SENA

### 1.4 Referencias

- IEEE Std 830-1998, IEEE Recommended Practice for Software Requirements Specifications
- Documentación del módulo: `docs/modulo-complementarios.md`
- Catálogo de Competencias: `docs/catalogo-competencias.md` (si existe)
- Caso de Uso: CU-PC-04

---

## 2. DESCRIPCIÓN GENERAL

### 2.1 Perspectiva del Requerimiento

Este requerimiento es parte del módulo de Gestión de Programas Complementarios y permite al administrador gestionar las competencias asociadas a un programa específico, facilitando la definición clara de lo que los aspirantes desarrollarán al completar el programa y permitiendo la actualización flexible de estas asociaciones.

### 2.2 Funciones del Requerimiento

- Listar competencias actualmente asignadas a un programa
- Mostrar catálogo de competencias disponibles para asignación
- Asignar nuevas competencias al programa
- Eliminar competencias asignadas al programa
- Reordenar la prioridad o secuencia de competencias
- Validar que las competencias sean compatibles con el programa
- Persistir los cambios en la relación programa-competencia

### 2.3 Características del Usuario

**Actor Principal:** Administrador u Operador del sistema

### 2.4 Restricciones

- Requiere autenticación de usuario
- El programa debe existir en el sistema
- Las competencias deben existir en el catálogo del sistema
- No se pueden asignar competencias duplicadas al mismo programa
- Algunas competencias pueden ser obligatorias para ciertos tipos de programas

### 2.5 Suposiciones y Dependencias

- El programa complementario existe en el sistema
- Existe un catálogo de competencias disponible y actualizado
- La relación entre programas y competencias se gestiona mediante tabla intermedia
- El sistema soporta la gestión de relaciones muchos-a-muchos

---

## 3. REQUERIMIENTOS FUNCIONALES

### 3.1 RF-PC-004: Asignar Competencias al Programa

**Identificador:** RF-PC-004  
**Título:** Asignar Competencias al Programa  
**Versión:** 1.0  
**Prioridad:** Media  
**Urgencia:** Media

#### 3.1.1 Descripción

El sistema debe permitir al administrador gestionar las competencias asociadas a un programa complementario, proporcionando interfaces para visualizar competencias actuales, explorar el catálogo disponible, asignar nuevas competencias, eliminar asignaciones existentes y reordenar la prioridad de las mismas.

#### 3.1.2 Objetivos Asociados

- Permitir la definición clara de las competencias que desarrolla cada programa complementario
- Facilitar la actualización flexible de las competencias asociadas a los programas
- Garantizar la consistencia en las relaciones programa-competencia
- Proporcionar visibilidad sobre las competencias desarrolladas por cada programa
- Soporte para la gestión curricular y alineación con estándares de formación

#### 3.1.3 Precondiciones

- El usuario debe haber iniciado sesión con rol de administrador u operador
- El programa complementario debe existir en el sistema
- Debe existir un catálogo de competencias disponible
- El usuario debe tener acceso a la gestión de programas complementarios

#### 3.1.4 Secuencia Normal

1. El usuario accede a la vista de detalle del programa complementario
2. El sistema autentica al usuario mediante middleware `auth`
3. El usuario selecciona la opción "Gestionar Competencias" o similar
4. El sistema carga y muestra:
   - **Competencias Asignadas Actualmente:**
     - Lista de competencias ya asociadas al programa
     - Para cada competencia: código, nombre, descripción, área
     - Opciones para eliminar o reordenar cada competencia
   - **Catálogo de Competencias Disponibles:**
     - Lista filtrada de competencias no asignadas al programa
     - Opciones de búsqueda y filtrado por área, código, nombre
     - Información detallada de cada competencia disponible
5. El usuario realiza acciones sobre las competencias:
   - **Para asignar nueva competencia:**
     - Selecciona una o más competencias del catálogo disponible
     - Opcionalmente define orden/prioridad
     - Confirma la asignación
   - **Para eliminar competencia asignada:**
     - Selecciona competencia de la lista de asignadas
     - Confirma eliminación (con advertencia si la competencia es considerada importante)
   - **Para reordenar competencias:**
     - Usa interfaz drag-and-drop o campos de orden
     - Guarda el nuevo orden
6. El sistema valida las operaciones:
   - Verifica que no se asignen competencias duplicadas
   - Valida que las competencias existan en el catálogo
   - Verifica permisos del usuario para realizar cambios
7. El sistema persiste los cambios:
   - Para nuevas asignaciones: crea registros en tabla `programa_competencia`
   - Para eliminaciones: elimina registros de `programa_competencia` (o marca como inactivo)
   - Para reordenamiento: actualiza campo `orden` en `programa_competencia`
8. El sistema muestra mensaje de confirmación: "Competencias actualizadas exitosamente"
9. El sistema actualiza la vista para reflejar los cambios

#### 3.1.5 Excepciones

**E-001:** Si se intenta asignar competencia duplicada
- **Condición:** El sistema detecta que la competencia ya está asignada al programa
- **Acción:** El sistema muestra mensaje: "Esta competencia ya está asignada al programa"
- **Código de Error:** 422 (Unprocessable Entity)
- **Log:** Se registra el intento de asignación duplicada

**E-002:** Si la competencia no existe en el catálogo
- **Condición:** La competencia referenciada no se encuentra en la base de datos
- **Acción:** El sistema muestra mensaje: "Competencia no encontrada en el catálogo"
- **Código de Error:** 404 (Not Found)
- **Log:** Se registra el intento de asignar competencia inexistente

**E-003:** Si hay error al persistir los cambios
- **Condición:** Se produce excepción al guardar en base de datos
- **Acción:** El sistema muestra mensaje: "Error al guardar los cambios. Por favor intente nuevamente."
- **Código de Error:** 500 (Internal Server Error)
- **Log:** Se registra el error con stack trace completo

**E-004:** Si el usuario no tiene permisos para modificar competencias
- **Condición:** Validación de permisos falla
- **Acción:** El sistema retornará error 403 con mensaje: "No tiene permisos para gestionar competencias"
- **Código de Error:** 403 (Forbidden)
- **Log:** Se registrará el intento de acceso no autorizado

#### 3.1.6 Postcondiciones

- Las competencias asignadas al programa están actualizadas según las operaciones realizadas
- La relación programa-competencia refleja el estado deseado
- El orden de las competencias (si aplica) está actualizado
- Se registra la acción en bitácora de auditoría (si está habilitado)
- La vista muestra el estado actualizado de las competencias del programa

#### 3.1.7 Requisitos Asociados

- **RF-PC-002:** Ver Detalles del Programa Complementario (vista desde donde se accede a la gestión de competencias)
- **RF-PC-001:** Crear Programa Complementario (donde se pueden asignar competencias inicialmente)
- **RNF-01:** Acceso Restringido por Roles
- **RNF-08:** Interfaz de Usuario Intuitiva

---

## 4. REQUERIMIENTOS NO FUNCIONALES

### 4.1 RNF-01: Acceso Restringido por Roles

**Prioridad:** Crítica  
**Categoría:** Seguridad

**Descripción:** La funcionalidad de gestionar competencias debe requerir autenticación de usuario y acceso según roles (Administrador u Operador). El sistema no debe permitir acceso sin sesión activa ni sin los permisos adecuados.

**Criterios de Aceptación:**
- La ruta está protegida con middleware `auth`
- Solo usuarios con rol Administrador u Operador pueden acceder
- Intentos de acceso sin autenticación redirigen a página de login
- No se puede modificar competencias sin sesión válida

### 4.2 RNF-08: Interfaz de Usuario Intuitiva

**Prioridad:** Alta  
**Categoría:** Usabilidad

**Descripción:** La interfaz para gestionar competencias debe ser intuitiva, con visualización clara de competencias asignadas vs disponibles, y mecanismos fáciles para asignar/eliminar/reordenar.

**Criterios de Aceptación:**
- Diseño de dos columnas (asignadas vs disponibles) o similar claro
- Mecanismos visuales para arrastrar y soltar (drag-and-drop) para reordenar
- Búsqueda y filtrado eficiente en catálogo de competencias
- Feedback visual inmediato de las acciones realizadas

### 4.3 RNF-09: Rendimiento con Catálogos Grandes

**Prioridad:** Media  
**Categoría:** Rendimiento

**Descripción:** La interfaz debe ser responsiva incluso con catálogos grandes de competencias (100+), optimizando la carga y búsqueda.

**Criterios de Aceptación:**
- Tiempo de carga inicial < 2 segundos con 100 competencias
- Búsqueda en tiempo real con respuesta < 1 segundo
- Paginación o scroll infinito para catálogos muy grandes
- No hay bloqueo de interfaz durante operaciones

---

## 5. CRITERIOS DE ACEPTACIÓN

### 5.1 Criterios Funcionales

**CA-001:** El sistema debe mostrar competencias asignadas y disponibles por separado
- **Verificación:** Se accede a gestión de competencias de programa con 5 asignadas y 20 disponibles
- **Resultado Esperado:** Se muestran claramente 5 en "Asignadas" y 20 en "Disponibles"

**CA-002:** El sistema debe prevenir asignación de competencias duplicadas
- **Verificación:** Se intenta asignar competencia ya asignada al programa
- **Resultado Esperado:** Error 422 con mensaje "Esta competencia ya está asignada"

**CA-003:** El sistema debe permitir eliminar competencias asignadas
- **Verificación:** Se elimina competencia asignada y se refresca la vista
- **Resultado Esperado:** Competencia desaparece de "Asignadas" y aparece en "Disponibles"

**CA-004:** El sistema debe permitir reordenar competencias asignadas
- **Verificación:** Se cambia orden de 3 competencias y se guarda
- **Resultado Esperado:** El nuevo orden se persiste y se refleja en la vista

**CA-005:** El sistema debe proporcionar búsqueda en catálogo de competencias
- **Verificación:** Se ingresa texto en campo de búsqueda
- **Resultado Esperado:** La lista de competencias disponibles se filtra en tiempo real

### 5.2 Criterios No Funcionales

**CA-006:** Solo usuarios autorizados pueden gestionar competencias
- **Verificación:** Usuario sin rol adecuado intenta acceder
- **Resultado Esperado:** Error 403 (Forbidden)

**CA-007:** Interfaz responsiva y clara
- **Verificación:** Se evalúa diseño visual de la interfaz
- **Resultado Esperado:** Diseño de dos columnas claro, controles intuitivos

**CA-008:** Rendimiento con catálogo grande
- **Verificación:** Se prueba con 150 competencias en catálogo
- **Resultado Esperado:** Tiempo de carga < 2 segundos, búsqueda responsiva

### 5.3 Criterios de Validación

**CA-009:** Validación de competencias existentes
- **Verificación:** Se intenta asignar ID de competencia inexistente
- **Resultado Esperado:** Error 404 con mensaje "Competencia no encontrada"

**CA-010:** Manejo apropiado de errores de persistencia
- **Verificación:** Se simula fallo de conexión a BD durante guardado
- **Resultado Esperado:** Mensaje de error claro, cambios no aplicados

**CA-011:** Persistencia atómica de cambios múltiples
- **Verificación:** Se realizan 3 operaciones (asignar, eliminar, reordenar) y se simula error en la segunda
- **Resultado Esperado:** Rollback completo, ningún cambio aplicado

---

## 6. TRAZABILIDAD

### 6.1 Requerimientos Relacionados

| Requerimiento | Relación | Descripción |
|---------------|----------|-------------|
| RF-PC-001 | Complementario | Crear Programa Complementario (asignación inicial de competencias) |
| RF-PC-002 | Anterior | Ver Detalles del Programa (desde donde se accede a gestión de competencias) |
| RF-PC-005 | Relacionado | Listar Programas Complementarios (muestra programas con sus competencias) |
| RNF-01 | Depende | Acceso Restringido por Roles |
| RNF-08 | Depende | Interfaz de Usuario Intuitiva |
| RNF-09 | Depende | Rendimiento con Catálogos Grandes |

### 6.2 Casos de Uso Relacionados

- **CU-PC-04:** Asignar Competencias al Programa

### 6.3 Componentes del Sistema

- **Controlador:** `ProgramaComplementarioController@gestionarCompetencias()` y `@actualizarCompetencias()`
- **Servicio:** `ProgramaComplementarioService@asignarCompetencias()` y `@actualizarCompetenciasPrograma()`
- **Repositorio:** `CompetenciaRepository@listarDisponiblesParaPrograma()` y `@listarAsignadasAPrograma()`
- **Repositorio:** `ProgramaCompetenciaRepository@asignar()`, `@eliminar()`, `@reordenar()`
- **Vista:** `resources/views/complementarios/programas/gestionar-competencias.blade.php`
- **Rutas:** 
  - `GET /programas-complementarios/{id}/competencias` (gestión)
  - `PUT /programas-complementarios/{id}/competencias` (actualización)

### 6.4 Pruebas Relacionadas

- Test unitario del servicio `actualizarCompetenciasPrograma()`
- Test de integración de la interfaz de gestión de competencias
- Test de validación de duplicados
- Test de permisos (solo usuarios autorizados)
- Test de rendimiento con catálogo grande
- Test de transacciones atómicas

---

## 7. GLOSARIO

| Término | Definición |
|--------|------------|
| **Competencia** | Habilidad, conocimiento o destreza que se espera desarrollar en un programa de formación |
| **Catálogo de Competencias** | Conjunto estructurado de todas las competencias disponibles en el sistema |
| **Programa-Competencia** | Relación muchos-a-muchos entre programas y competencias que estos desarrollan |
| **Orden de Competencias** | Secuencia o prioridad en que se presentan o desarrollan las competencias en un programa |
| **Asignación** | Establecimiento de relación entre un programa y una competencia |

---

**FIN DEL DOCUMENTO**
