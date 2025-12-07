# ESPECIFICACIÓN DE REQUERIMIENTOS DE SOFTWARE (SRS)
## RF-PC-003: Eliminar Programa Complementario

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

Este documento especifica los requerimientos funcionales y no funcionales para el caso de uso **"Eliminar Programa Complementario"** (RF-PC-003), que forma parte del módulo de Gestión de Programas Complementarios del sistema CDATTG Web.

### 1.2 Alcance

Este SRS cubre la funcionalidad que permite al administrador eliminar un programa complementario del sistema, incluyendo validaciones previas, manejo de dependencias, confirmación del usuario y eliminación lógica o física según corresponda.

### 1.3 Definiciones, Acrónimos y Abreviaciones

- **SRS**: Software Requirements Specification
- **RF**: Requerimiento Funcional
- **RNF**: Requerimiento No Funcional
- **CDATTG**: Centro de Desarrollo Agroempresarial y Turístico del Guaviare
- **SENA**: Servicio Nacional de Aprendizaje
- **Eliminación Lógica**: Marcado de registro como eliminado sin borrarlo físicamente de la base de datos
- **Eliminación Física**: Borrado permanente del registro de la base de datos

### 1.4 Referencias

- IEEE Std 830-1998, IEEE Recommended Practice for Software Requirements Specifications
- Documentación del módulo: `docs/modulo-complementarios.md`
- Caso de Uso: CU-PC-03

---

## 2. DESCRIPCIÓN GENERAL

### 2.1 Perspectiva del Requerimiento

Este requerimiento es parte del módulo de Gestión de Programas Complementarios y permite al administrador eliminar programas que ya no son requeridos, aplicando validaciones para prevenir eliminación de programas con dependencias activas y proporcionando mecanismos de confirmación y reversión cuando sea posible.

### 2.2 Funciones del Requerimiento

- Validar que el programa exista y sea eliminable
- Verificar que no tenga dependencias activas (aspirantes inscritos, validaciones en curso)
- Solicitar confirmación explícita del usuario
- Realizar eliminación lógica (soft delete) por defecto
- Proporcionar opción de eliminación física en casos específicos
- Manejar la eliminación de relaciones asociadas (competencias, parámetros)
- Notificar al usuario del resultado de la operación
- Registrar la acción en bitácora de auditoría

### 2.3 Características del Usuario

**Actor Principal:** Administrador del sistema

### 2.4 Restricciones

- Requiere autenticación de usuario con rol de administrador
- No se pueden eliminar programas con aspirantes inscritos activos
- No se pueden eliminar programas con validaciones SOFIA en curso
- La eliminación requiere confirmación explícita del usuario
- La eliminación física solo está permitida para programas sin historial

### 2.5 Suposiciones y Dependencias

- El programa complementario existe en el sistema
- El sistema implementa soft delete (eliminación lógica) para la mayoría de los casos
- Existen mecanismos de auditoría para registrar eliminaciones
- Los usuarios entienden las implicaciones de la eliminación

---

## 3. REQUERIMIENTOS FUNCIONALES

### 3.1 RF-PC-003: Eliminar Programa Complementario

**Identificador:** RF-PC-003  
**Título:** Eliminar Programa Complementario  
**Versión:** 1.0  
**Prioridad:** Media  
**Urgencia:** Baja

#### 3.1.1 Descripción

El sistema debe permitir al administrador eliminar un programa complementario, aplicando validaciones para prevenir eliminación de programas con dependencias activas, solicitando confirmación explícita y realizando eliminación lógica por defecto, con opción de eliminación física en casos específicos.

#### 3.1.2 Objetivos Asociados

- Permitir al administrador eliminar programas complementarios que ya no son requeridos
- Prevenir eliminación accidental de programas con dependencias activas
- Garantizar la integridad referencial de la base de datos
- Proporcionar trazabilidad de las eliminaciones mediante auditoría
- Ofrecer opción de recuperación mediante eliminación lógica

#### 3.1.3 Precondiciones

- El usuario debe haber iniciado sesión con rol de administrador
- El programa complementario debe existir en el sistema
- El usuario debe estar en la vista de detalle del programa o en la lista de programas

#### 3.1.4 Secuencia Normal

1. El usuario accede a la vista de detalle del programa o selecciona la opción de eliminar desde la lista
2. El sistema autentica al usuario mediante middleware `auth` y valida permisos de administrador
3. El sistema verifica que el programa sea eliminable:
   - Consulta si tiene aspirantes inscritos activos (estado diferente a "Rechazado" o "Finalizado")
   - Consulta si tiene validaciones SOFIA en curso
   - Verifica otras dependencias críticas
4. Si el programa es eliminable, el sistema muestra diálogo de confirmación con:
   - Nombre y código del programa a eliminar
   - Advertencia sobre la acción irreversible (si es eliminación física)
   - Información sobre dependencias que serán afectadas
   - Opción de seleccionar tipo de eliminación (lógica por defecto, física si está disponible)
   - Campo para ingresar motivo de eliminación (obligatorio)
5. El usuario confirma la eliminación, selecciona tipo (si aplica) e ingresa motivo
6. El sistema realiza la eliminación según el tipo seleccionado:
   - **Eliminación Lógica (Soft Delete):**
     - Actualiza campo `deleted_at` con timestamp actual
     - Actualiza campo `deleted_by` con ID del usuario
     - Actualiza campo `motivo_eliminacion` con texto proporcionado
     - Mantiene todas las relaciones intactas
   - **Eliminación Física (Hard Delete):**
     - Elimina relaciones en `programa_competencia`
     - Elimina parámetros asociados
     - Elimina registro principal en `complementarios_ofertados`
     - Registra acción completa en tabla de auditoría
7. El sistema muestra mensaje de confirmación: "Programa complementario eliminado exitosamente"
8. El sistema redirige a la lista de programas complementarios

#### 3.1.5 Excepciones

**E-001:** Si el programa tiene aspirantes inscritos activos
- **Condición:** La validación encuentra aspirantes con estado "En proceso" o "Admitido"
- **Acción:** El sistema muestra mensaje: "No se puede eliminar el programa porque tiene aspirantes inscritos activos. Primero debe rechazar o finalizar los aspirantes."
- **Código de Error:** 409 (Conflict)
- **Log:** Se registra el intento de eliminación con dependencias activas

**E-002:** Si el programa tiene validaciones SOFIA en curso
- **Condición:** La validación encuentra validaciones SOFIA con estado "En progreso"
- **Acción:** El sistema muestra mensaje: "No se puede eliminar el programa porque tiene validaciones SOFIA en curso. Espere a que finalicen las validaciones."
- **Código de Error:** 409 (Conflict)
- **Log:** Se registra el intento de eliminación con validaciones activas

**E-003:** Si el usuario no tiene permisos de administrador
- **Condición:** Validación de rol falla (usuario no es administrador)
- **Acción:** El sistema retornará error 403 con mensaje: "Solo los administradores pueden eliminar programas"
- **Código de Error:** 403 (Forbidden)
- **Log:** Se registrará el intento de eliminación no autorizado

**E-004:** Si hay error durante la eliminación
- **Condición:** Se produce excepción al ejecutar la eliminación en base de datos
- **Acción:** El sistema muestra mensaje: "Error al eliminar el programa. Por favor intente nuevamente."
- **Código de Error:** 500 (Internal Server Error)
- **Log:** Se registra el error con stack trace completo

#### 3.1.6 Postcondiciones

- El programa queda marcado como eliminado (soft delete) o removido permanentemente (hard delete)
- Se registra la acción en bitácora de auditoría con timestamp, usuario y motivo
- El usuario es redirigido a la lista de programas
- El programa ya no aparece en listados activos (si fue soft delete, aparece solo en vista de eliminados)
- Las relaciones dependientes son manejadas según el tipo de eliminación

#### 3.1.7 Requisitos Asociados

- **RF-PC-002:** Ver Detalles del Programa Complementario (vista desde donde se puede iniciar la eliminación)
- **RF-PC-005:** Listar Programas Complementarios (vista a donde se redirige después de eliminar)
- **RF-ASP-004:** Rechazar Aspirante (requerimiento previo para eliminar programas con aspirantes)
- **RNF-01:** Acceso Restringido por Roles
- **RNF-06:** Auditoría y Trazabilidad

---

## 4. REQUERIMIENTOS NO FUNCIONALES

### 4.1 RNF-01: Acceso Restringido por Roles

**Prioridad:** Crítica  
**Categoría:** Seguridad

**Descripción:** La funcionalidad de eliminar programas complementarios debe requerir autenticación de usuario y acceso exclusivo para rol de Administrador. El sistema no debe permitir acceso sin sesión activa ni sin los permisos adecuados.

**Criterios de Aceptación:**
- La ruta está protegida con middleware `auth` y verificación de rol estricta
- Solo usuarios con rol Administrador pueden acceder
- Intentos de acceso sin autenticación redirigen a página de login
- Intentos de acceso con rol incorrecto retornan error 403

### 4.2 RNF-06: Auditoría y Trazabilidad

**Prioridad:** Alta  
**Categoría:** Seguridad

**Descripción:** Todas las eliminaciones de programas deben ser registradas en bitácora de auditoría con información completa: qué, quién, cuándo, por qué y cómo.

**Criterios de Aceptación:**
- Cada eliminación genera registro en tabla de auditoría
- El registro incluye: ID del programa, usuario eliminador, timestamp, tipo de eliminación, motivo
- Los registros de auditoría son inmutables y protegidos contra modificaciones
- Se puede generar reporte histórico de eliminaciones

### 4.3 RNF-07: Validación de Dependencias

**Prioridad:** Alta  
**Categoría:** Integridad

**Descripción:** El sistema debe validar exhaustivamente las dependencias antes de permitir la eliminación, previniendo la corrupción de datos y manteniendo la integridad referencial.

**Criterios de Aceptación:**
- Se verifican todas las dependencias críticas antes de eliminar
- Los mensajes de error son específicos sobre qué dependencia impide la eliminación
- No se permite la eliminación si hay dependencias activas no manejables
- Las validaciones son consistentes y confiables

---

## 5. CRITERIOS DE ACEPTACIÓN

### 5.1 Criterios Funcionales

**CA-001:** El sistema debe validar que no haya aspirantes activos antes de eliminar
- **Verificación:** Se intenta eliminar programa con aspirantes en estado "En proceso"
- **Resultado Esperado:** Error 409 con mensaje específico sobre aspirantes activos

**CA-002:** El sistema debe requerir confirmación explícita del usuario
- **Verificación:** Se hace clic en botón eliminar
- **Resultado Esperado:** Se muestra diálogo de confirmación con detalles del programa

**CA-003:** El sistema debe requerir motivo de eliminación
- **Verificación:** Se intenta confirmar eliminación sin ingresar motivo
- **Resultado Esperado:** Validación previene confirmación, solicita completar motivo

**CA-004:** El sistema debe realizar eliminación lógica por defecto
- **Verificación:** Se elimina programa sin dependencias activas
- **Resultado Esperado:** Campo `deleted_at` se actualiza, registro no se borra físicamente

**CA-005:** El sistema debe redirigir a lista de programas después de eliminar
- **Verificación:** Se completa eliminación exitosa
- **Resultado Esperado:** Redirección a `/programas-complementarios` con mensaje de éxito

### 5.2 Criterios No Funcionales

**CA-006:** Solo administradores pueden eliminar programas
- **Verificación:** Usuario con rol Operador intenta eliminar programa
- **Resultado Esperado:** Error 403 (Forbidden)

**CA-007:** Todas las eliminaciones se registran en auditoría
- **Verificación:** Se elimina programa y se consulta tabla de auditoría
- **Resultado Esperado:** Existe registro con información completa de la eliminación

**CA-008:** Las validaciones de dependencias son exhaustivas
- **Verificación:** Programa tiene validación SOFIA en curso
- **Resultado Esperado:** Error 409 con mensaje específico sobre validación SOFIA

### 5.3 Criterios de Validación

**CA-009:** Manejo apropiado de errores durante eliminación
- **Verificación:** Se simula fallo de conexión a BD durante eliminación
- **Resultado Esperado:** Mensaje de error claro, rollback completo, registro en log

**CA-010:** Eliminación física solo disponible en casos específicos
- **Verificación:** Programa tiene historial de aspirantes (aunque estén finalizados)
- **Resultado Esperado:** Opción de eliminación física no disponible o restringida

**CA-011:** Integridad referencial mantenida
- **Verificación:** Se elimina programa con soft delete y se consultan relaciones
- **Resultado Esperado:** Las relaciones permanecen intactas, solo el programa principal está marcado como eliminado

---

## 6. TRAZABILIDAD

### 6.1 Requerimientos Relacionados

| Requerimiento | Relación | Descripción |
|---------------|----------|-------------|
| RF-PC-002 | Anterior | Ver Detalles del Programa (desde donde se inicia la eliminación) |
| RF-PC-005 | Siguiente | Listar Programas Complementarios (a donde se redirige después de eliminar) |
| RF-ASP-004 | Depende | Rechazar Aspirante (debe ejecutarse antes si hay aspirantes activos) |
| RNF-01 | Depende | Acceso Restringido por Roles |
| RNF-06 | Depende | Auditoría y Trazabilidad |
| RNF-07 | Depende | Validación de Dependencias |

### 6.2 Casos de Uso Relacionados

- **CU-PC-03:** Eliminar Programa Complementario

### 6.3 Componentes del Sistema

- **Controlador:** `ProgramaComplementarioController@eliminar()` y `@confirmarEliminacion()`
- **Servicio:** `ProgramaComplementarioService@eliminarPrograma()`
- **Repositorio:** `ComplementarioOfertadoRepository@verificarDependencias()`
- **Repositorio:** `ComplementarioOfertadoRepository@eliminarLogico()` y `@eliminarFisico()`
- **Vista:** Diálogo de confirmación en `resources/views/complementarios/programas/confirmar-eliminacion.blade.php`
- **Rutas:** 
  - `GET /programas-complementarios/{id}/eliminar` (confirmación)
  - `DELETE /programas-complementarios/{id}` (ejecución)

### 6.4 Pruebas Relacionadas

- Test unitario del servicio `eliminarPrograma()`
- Test de validación de dependencias (aspirantes activos, validaciones SOFIA)
- Test de permisos (solo administrador)
- Test de eliminación lógica vs física
- Test de auditoría (registro de eliminaciones)
- Test de integridad referencial después de eliminación

---

## 7. GLOSARIO

| Término | Definición |
|--------|------------|
| **Eliminación Lógica** | Marcado de registro como eliminado sin borrarlo físicamente de la base de datos, permitiendo posible recuperación |
| **Eliminación Física** | Borrado permanente del registro de la base de datos, sin posibilidad de recuperación |
| **Soft Delete** | Sinónimo de Eliminación Lógica, implementado comúnmente con campo `deleted_at` |
| **Hard Delete** | Sinónimo de Eliminación Física |
| **Integridad Referencial** | Propiedad que garantiza que las relaciones entre tablas se mantengan consistentes |
| **Auditoría** | Registro detallado de acciones realizadas en el sistema para trazabilidad |

---

**FIN DEL DOCUMENTO**
