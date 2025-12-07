# ESPECIFICACIÓN DE REQUERIMIENTOS DE SOFTWARE (SRS)
## RF-PC-002: Ver Detalles del Programa Complementario

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

Este documento especifica los requerimientos funcionales y no funcionales para el caso de uso **"Ver Detalles del Programa Complementario"** (RF-PC-002), que forma parte del módulo de Gestión de Programas Complementarios del sistema CDATTG Web.

### 1.2 Alcance

Este SRS cubre la funcionalidad que permite al administrador consultar la información completa de un programa complementario específico, incluyendo datos básicos, características de formación, competencias asociadas, estadísticas de aspirantes y estado actual del programa.

### 1.3 Definiciones, Acrónimos y Abreviaciones

- **SRS**: Software Requirements Specification
- **RF**: Requerimiento Funcional
- **RNF**: Requerimiento No Funcional
- **CDATTG**: Centro de Desarrollo Agroempresarial y Turístico del Guaviare
- **SENA**: Servicio Nacional de Aprendizaje
- **SOFIA Plus**: Sistema de Gestión de Formación del SENA
- **Programa Complementario**: Curso de formación complementaria ofertado por el SENA

### 1.4 Referencias

- IEEE Std 830-1998, IEEE Recommended Practice for Software Requirements Specifications
- Documentación del módulo: `docs/modulo-complementarios.md`
- Caso de Uso: CU-PC-02

---

## 2. DESCRIPCIÓN GENERAL

### 2.1 Perspectiva del Requerimiento

Este requerimiento es parte del módulo de Gestión de Programas Complementarios y permite al administrador visualizar todos los detalles de un programa específico, incluyendo información de configuración, relaciones con competencias, estadísticas de aspirantes y estado operativo.

### 2.2 Funciones del Requerimiento

- Consultar información básica del programa (nombre, código, descripción, estado)
- Visualizar características de formación (modalidad, jornada, días, horarios)
- Mostrar información de cupos (disponibles, ocupados, totales)
- Listar competencias asociadas al programa
- Presentar estadísticas de aspirantes (inscritos, admitidos, rechazados)
- Mostrar historial de cambios y auditoría
- Proporcionar acceso a acciones de gestión sobre el programa

### 2.3 Características del Usuario

**Actor Principal:** Administrador u Operador del sistema

### 2.4 Restricciones

- Requiere autenticación de usuario
- El programa debe existir en el sistema
- La consulta debe ser eficiente incluso con múltiples relaciones

### 2.5 Suposiciones y Dependencias

- El programa complementario existe en el sistema
- Existen relaciones establecidas entre programas, competencias y aspirantes
- La base de datos contiene información actualizada

---

## 3. REQUERIMIENTOS FUNCIONALES

### 3.1 RF-PC-002: Ver Detalles del Programa Complementario

**Identificador:** RF-PC-002  
**Título:** Ver Detalles del Programa Complementario  
**Versión:** 1.0  
**Prioridad:** Alta  
**Urgencia:** Media

#### 3.1.1 Descripción

El sistema debe permitir al administrador visualizar todos los detalles de un programa complementario específico, mostrando información completa del programa, sus características de formación, competencias asociadas, estadísticas de aspirantes y proporcionando acceso a acciones de gestión.

#### 3.1.2 Objetivos Asociados

- Permitir al administrador consultar información completa de un programa complementario
- Proporcionar visibilidad sobre el estado y configuración del programa
- Facilitar la toma de decisiones basada en estadísticas de aspirantes
- Proporcionar acceso centralizado a acciones de gestión sobre el programa

#### 3.1.3 Precondiciones

- El usuario debe haber iniciado sesión con rol de administrador u operador
- El programa complementario debe existir en el sistema
- El usuario debe tener acceso a la gestión de programas complementarios

#### 3.1.4 Secuencia Normal

1. El usuario accede a la lista de programas complementarios y selecciona un programa específico
2. El sistema autentica al usuario mediante middleware `auth`
3. El sistema valida que el programa exista consultando la tabla `complementarios_ofertados`
4. El sistema consulta información completa del programa incluyendo relaciones:
   - Datos básicos desde `complementarios_ofertados`
   - Modalidad y jornada (con parámetros)
   - Competencias asociadas desde `programa_competencia` con relación a `competencias`
   - Estadísticas de aspirantes desde `aspirantes_complementarios` (contando por estado)
   - Historial de cambios desde tabla de auditoría (si existe)
5. El sistema muestra una vista organizada en secciones:
   - **Información Básica:**
     - Nombre del programa
     - Código del programa
     - Descripción
     - Estado (Activo, Inactivo, Finalizado)
     - Fechas de inicio y fin
     - Duración en horas
   - **Características de Formación:**
     - Modalidad (Presencial, Virtual, Mixta)
     - Jornada (Diurna, Nocturna, Mixta)
     - Días de formación
     - Horario de clases
     - Lugar de formación (si aplica)
   - **Cupos y Ocupación:**
     - Cupos totales
     - Cupos ocupados (aspirantes inscritos)
     - Cupos disponibles
     - Porcentaje de ocupación
     - Cupos mínimos/máximos
   - **Competencias Asociadas:**
     - Lista de competencias con código y descripción
     - Número total de competencias
     - Posibilidad de ver detalles de cada competencia
   - **Estadísticas de Aspirantes:**
     - Total de aspirantes inscritos
     - Aspirantes en proceso
     - Aspirantes admitidos
     - Aspirantes rechazados
     - Gráfico de distribución (opcional)
   - **Acciones Disponibles:**
     - Editar Programa
     - Eliminar Programa
     - Asignar Competencias
     - Ver Aspirantes
     - Exportar Información
     - Cambiar Estado
   - **Historial de Cambios** (si está habilitado):
     - Última modificación (fecha y usuario)
     - Cambios recientes en configuración

#### 3.1.5 Excepciones

**E-001:** Si el programa no existe
- **Condición:** La consulta no encuentra el programa con el ID proporcionado
- **Acción:** El sistema retornará error 404 con mensaje: "Programa no encontrado"
- **Código de Error:** 404 (Not Found)
- **Log:** Se registrará el intento de acceso a programa inexistente

**E-002:** Si hay error al consultar la información
- **Condición:** Se produce una excepción en la consulta de datos del programa
- **Acción:** El sistema mostrará mensaje: "Error al cargar la información del programa. Por favor intente nuevamente."
- **Código de Error:** 500 (Internal Server Error)
- **Log:** Se registrará el error con stack trace completo

**E-003:** Si el usuario no tiene permisos para ver el programa
- **Condición:** Validación de permisos falla (programa restringido)
- **Acción:** El sistema retornará error 403 con mensaje: "No tiene permisos para ver este programa"
- **Código de Error:** 403 (Forbidden)
- **Log:** Se registrará el intento de acceso no autorizado

#### 3.1.6 Postcondiciones

- El sistema muestra la vista completa con todos los detalles del programa
- La información presentada está actualizada al momento de la consulta
- El usuario puede acceder a las acciones de gestión disponibles
- Las estadísticas reflejan el estado actual de los aspirantes
- Se registra el acceso en el log de auditoría (si está habilitado)

#### 3.1.7 Requisitos Asociados

- **RF-PC-001:** Crear Programa Complementario (origen del programa)
- **RF-PC-003:** Eliminar Programa Complementario (acción disponible desde esta vista)
- **RF-PC-004:** Asignar Competencias al Programa (acción disponible desde esta vista)
- **RF-PC-005:** Listar Programas Complementarios (vista anterior en el flujo)
- **RF-ASP-002:** Ver Aspirantes de un Programa (acción disponible desde esta vista)
- **RNF-01:** Acceso Restringido por Roles

---

## 4. REQUERIMIENTOS NO FUNCIONALES

### 4.1 RNF-01: Acceso Restringido por Roles

**Prioridad:** Crítica  
**Categoría:** Seguridad

**Descripción:** La funcionalidad de ver detalles de programas debe requerir autenticación de usuario y acceso según roles (Administrador u Operador). El sistema no debe permitir acceso sin sesión activa ni sin los permisos adecuados.

**Criterios de Aceptación:**
- La ruta está protegida con middleware `auth`
- Solo usuarios con rol Administrador u Operador pueden acceder
- Intentos de acceso sin autenticación redirigen a página de login
- No se puede acceder a información de programas sin sesión válida

### 4.2 RNF-04: Eficiencia Operacional

**Prioridad:** Media  
**Categoría:** Rendimiento

**Descripción:** La consulta de detalles del programa debe ser eficiente incluso con múltiples relaciones (competencias, aspirantes, parámetros), optimizando consultas y recursos.

**Criterios de Aceptación:**
- Se utiliza eager loading para evitar consultas N+1
- El tiempo de carga no excede 3 segundos
- Las relaciones se cargan de forma optimizada
- Las consultas utilizan índices apropiados en la base de datos

### 4.3 RNF-05: Consistencia de Datos

**Prioridad:** Alta  
**Categoría:** Calidad

**Descripción:** La información mostrada debe ser consistente y reflejar el estado actual del sistema en tiempo real.

**Criterios de Aceptación:**
- Las estadísticas de aspirantes se calculan en tiempo real
- Los cambios en el programa se reflejan inmediatamente
- No se muestra información cacheadá obsoleta
- Los contadores coinciden con los registros en base de datos

---

## 5. CRITERIOS DE ACEPTACIÓN

### 5.1 Criterios Funcionales

**CA-001:** El sistema debe validar que el programa exista antes de mostrar detalles
- **Verificación:** Se prueba con ID de programa inexistente
- **Resultado Esperado:** Error 404 con mensaje "Programa no encontrado"

**CA-002:** El sistema debe mostrar información completa del programa
- **Verificación:** Se verifica que se muestren todas las secciones: básica, formación, cupos, competencias, estadísticas
- **Resultado Esperado:** Todas las secciones están presentes con información correcta

**CA-003:** El sistema debe listar todas las competencias asociadas
- **Verificación:** Se consulta programa con 5 competencias asociadas
- **Resultado Esperado:** Se muestran las 5 competencias con código y descripción

**CA-004:** El sistema debe calcular estadísticas correctas de aspirantes
- **Verificación:** Programa tiene 10 aspirantes: 5 en proceso, 3 admitidos, 2 rechazados
- **Resultado Esperado:** Estadísticas muestran: Total=10, En proceso=5, Admitidos=3, Rechazados=2

**CA-005:** El sistema debe proporcionar acceso a acciones de gestión
- **Verificación:** Se verifica la presencia de botones: Editar, Eliminar, Asignar Competencias, Ver Aspirantes
- **Resultado Esperado:** Todos los botones de acción están presentes y funcionales

### 5.2 Criterios No Funcionales

**CA-006:** El tiempo de carga no debe exceder 3 segundos
- **Verificación:** Se mide el tiempo de respuesta con programa con 10 competencias y 50 aspirantes
- **Resultado Esperado:** Tiempo de respuesta < 3 segundos

**CA-007:** La funcionalidad debe estar disponible solo para usuarios autenticados con roles apropiados
- **Verificación:** Se intenta acceder sin autenticación o con rol incorrecto
- **Resultado Esperado:** Redirección a página de login o error 403

**CA-008:** Las consultas deben usar eager loading para optimizar rendimiento
- **Verificación:** Se revisa el código del servicio y repositorio
- **Resultado Esperado:** Se utilizan `with()` para cargar relaciones de competencias y aspirantes

### 5.3 Criterios de Validación

**CA-009:** Si el programa no existe, se debe mostrar error 404
- **Verificación:** Se prueba con ID de programa inexistente
- **Resultado Esperado:** Error 404 con mensaje apropiado

**CA-010:** Los errores deben manejarse apropiadamente
- **Verificación:** Se simula error en la consulta de competencias
- **Resultado Esperado:** Mensaje de error claro y registro en log

**CA-011:** La información debe ser consistente en tiempo real
- **Verificación:** Se agrega aspirante mientras la vista está abierta y se refresca
- **Resultado Esperado:** El contador de aspirantes se actualiza correctamente

---

## 6. TRAZABILIDAD

### 6.1 Requerimientos Relacionados

| Requerimiento | Relación | Descripción |
|---------------|----------|-------------|
| RF-PC-001 | Anterior | Crear Programa Complementario (origen del programa) |
| RF-PC-003 | Siguiente | Eliminar Programa Complementario (acción disponible desde esta vista) |
| RF-PC-004 | Siguiente | Asignar Competencias al Programa (acción disponible desde esta vista) |
| RF-PC-005 | Anterior | Listar Programas Complementarios (se ejecuta antes de seleccionar un programa) |
| RF-ASP-002 | Extiende | Ver Aspirantes de un Programa (acción disponible desde esta vista) |
| RNF-01 | Depende | Acceso Restringido por Roles |
| RNF-04 | Depende | Eficiencia Operacional |
| RNF-05 | Depende | Consistencia de Datos |

### 6.2 Casos de Uso Relacionados

- **CU-PC-02:** Ver Detalles del Programa Complementario

### 6.3 Componentes del Sistema

- **Controlador:** `ProgramaComplementarioController@ver()` o `@mostrar()`
- **Servicio:** `ProgramaComplementarioService@obtenerDetallesCompletos()`
- **Repositorio:** `ComplementarioOfertadoRepository@findWithAllRelations()`
- **Repositorio:** `CompetenciaRepository@findByPrograma()`
- **Repositorio:** `AspiranteComplementarioRepository@contarPorEstado()`
- **Vista:** `resources/views/complementarios/programas/detalle.blade.php`
- **Rutas:** `GET /programas-complementarios/{id}`

### 6.4 Pruebas Relacionadas

- Test unitario del servicio `obtenerDetallesCompletos()`
- Test de integración de la ruta de detalle
- Test de autenticación requerida
- Test de manejo de errores (programa no existe)
- Test de rendimiento con múltiples relaciones
- Test de consistencia de estadísticas

---

## 7. GLOSARIO

| Término | Definición |
|--------|------------|
| **Programa Complementario** | Curso de formación complementaria ofertado por el SENA |
| **Competencia** | Habilidad, conocimiento o destreza que se espera desarrollar en el programa |
| **Modalidad** | Forma en que se imparte el programa: Presencial, Virtual o Mixta |
| **Jornada** | Horario de formación: Diurna, Nocturna o Mixta |
| **Cupo** | Número máximo de aspirantes que pueden inscribirse en el programa |
