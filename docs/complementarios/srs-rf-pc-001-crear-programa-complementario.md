# ESPECIFICACIÓN DE REQUERIMIENTOS DE SOFTWARE (SRS)
## RF-PC-001: Crear Programa Complementario

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

Este documento especifica los requerimientos funcionales y no funcionales para el caso de uso **"Crear Programa Complementario"** (RF-PC-001), que forma parte del módulo de Gestión de Programas Complementarios del sistema CDATTG Web.

### 1.2 Alcance

Este SRS cubre la funcionalidad que permite al administrador crear un nuevo programa complementario en el sistema, definiendo sus características básicas como nombre, código, cupos, modalidad, jornada, días de formación, competencias asociadas y otros parámetros configurables.

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
- Caso de Uso: CU-PC-01

---

## 2. DESCRIPCIÓN GENERAL

### 2.1 Perspectiva del Requerimiento

Este requerimiento es parte del módulo de Gestión de Programas Complementarios y permite al administrador registrar un nuevo programa de formación complementaria en el sistema, estableciendo todos los parámetros necesarios para su posterior gestión y asignación de aspirantes.

### 2.2 Funciones del Requerimiento

- Capturar información básica del programa (nombre, código, descripción)
- Definir características de formación (modalidad, jornada, días, horarios)
- Establecer cupos disponibles y duración
- Asignar competencias asociadas al programa
- Configurar parámetros adicionales (requisitos, documentos necesarios)
- Validar unicidad del código del programa
- Persistir la información en la base de datos

### 2.3 Características del Usuario

**Actor Principal:** Administrador del sistema

### 2.4 Restricciones

- Requiere autenticación de usuario con rol de administrador
- El código del programa debe ser único en el sistema
- Los campos obligatorios deben ser validados
- La fecha de inicio no puede ser anterior a la fecha actual

### 2.5 Suposiciones y Dependencias

- El usuario administrador tiene permisos para crear programas
- Existen catálogos predefinidos de modalidades, jornadas y competencias
- La base de datos está disponible y accesible

---

## 3. REQUERIMIENTOS FUNCIONALES

### 3.1 RF-PC-001: Crear Programa Complementario

**Identificador:** RF-PC-001  
**Título:** Crear Programa Complementario  
**Versión:** 1.0  
**Prioridad:** Alta  
**Urgencia:** Alta

#### 3.1.1 Descripción

El sistema debe permitir al administrador crear un nuevo programa complementario, capturando toda la información necesaria para su gestión, incluyendo datos básicos, características de formación, cupos, competencias asociadas y validando la unicidad del código del programa.

#### 3.1.2 Objetivos Asociados

- Permitir al administrador registrar nuevos programas complementarios en el sistema
- Establecer las características completas del programa para su posterior gestión
- Garantizar la integridad y unicidad de la información registrada
- Facilitar la asignación de competencias al momento de creación

#### 3.1.3 Precondiciones

- El usuario debe haber iniciado sesión con rol de administrador
- El usuario debe tener acceso al módulo de gestión de programas complementarios
- Los catálogos de modalidades, jornadas y competencias deben estar disponibles

#### 3.1.4 Secuencia Normal

1. El usuario accede a la gestión de programas complementarios y selecciona la opción "Crear Nuevo Programa"
2. El sistema autentica al usuario mediante middleware `auth` y valida permisos
3. El sistema muestra un formulario con las siguientes secciones:
   - **Información Básica:**
     - Nombre del programa (obligatorio)
     - Código del programa (obligatorio, único)
     - Descripción (opcional)
     - Duración en horas (obligatorio)
     - Fecha de inicio (obligatorio)
     - Fecha de fin (obligatorio)
   - **Características de Formación:**
     - Modalidad (selección de catálogo: Presencial, Virtual, Mixta)
     - Jornada (selección de catálogo: Diurna, Nocturna, Mixta)
     - Días de formación (checkboxes: Lunes a Domingo)
     - Horario de inicio y fin
   - **Cupos y Capacidad:**
     - Cupos disponibles (obligatorio, numérico)
     - Cupos mínimos para apertura (opcional)
     - Cupos máximos (opcional)
   - **Competencias Asociadas:**
     - Lista de competencias disponibles (selección múltiple)
     - Posibilidad de agregar nuevas competencias (si aplica)
   - **Parámetros Adicionales:**
     - Requisitos de ingreso (texto)
     - Documentos necesarios (checkboxes)
     - Observaciones (texto)
4. El usuario completa el formulario y hace clic en "Guardar"
5. El sistema valida:
   - Campos obligatorios completos
   - Unicidad del código del programa (consulta a tabla `complementarios_ofertados`)
   - Fechas válidas (inicio no anterior a hoy, fin posterior a inicio)
   - Valores numéricos válidos (cupos positivos)
6. El sistema persiste la información:
   - Crea registro en tabla `complementarios_ofertados`
   - Asocia competencias en tabla `programa_competencia` (si se seleccionaron)
   - Registra parámetros adicionales en tablas correspondientes
7. El sistema muestra mensaje de confirmación: "Programa complementario creado exitosamente"
8. El sistema redirige a la vista de detalle del programa creado

#### 3.1.5 Excepciones

**E-001:** Si el código del programa ya existe
- **Condición:** La validación de unicidad encuentra código duplicado
- **Acción:** El sistema muestra mensaje: "El código del programa ya existe. Por favor ingrese un código único."
- **Código de Error:** 422 (Unprocessable Entity)
- **Log:** Se registra el intento de creación con código duplicado

**E-002:** Si faltan campos obligatorios
- **Condición:** Validación de campos obligatorios falla
- **Acción:** El sistema muestra mensaje: "Por favor complete todos los campos obligatorios marcados con *"
- **Código de Error:** 422 (Unprocessable Entity)
- **Log:** Se registran los campos faltantes

**E-003:** Si hay error de persistencia en base de datos
- **Condición:** Se produce excepción al guardar en base de datos
- **Acción:** El sistema muestra mensaje: "Error al guardar el programa. Por favor intente nuevamente."
- **Código de Error:** 500 (Internal Server Error)
- **Log:** Se registra el error con stack trace completo

**E-004:** Si las fechas son inválidas
- **Condición:** Fecha de inicio anterior a hoy o fecha de fin anterior a fecha de inicio
- **Acción:** El sistema muestra mensaje: "Las fechas ingresadas no son válidas. Verifique que la fecha de inicio no sea anterior a hoy y que la fecha de fin sea posterior a la fecha de inicio."
- **Código de Error:** 422 (Unprocessable Entity)
- **Log:** Se registran las fechas inválidas

#### 3.1.6 Postcondiciones

- El programa complementario queda registrado en el sistema con estado "Activo"
- Las competencias asociadas quedan vinculadas al programa
- El programa está disponible para ser listado y gestionado
- El usuario puede acceder a la vista de detalle del programa creado
- El sistema genera log de creación con timestamp y usuario creador

#### 3.1.7 Requisitos Asociados

- **RF-PC-005:** Listar Programas Complementarios (para ver el programa creado en la lista)
- **RF-PC-002:** Ver Detalles del Programa Complementario (para acceder al detalle después de creación)
- **RF-PC-004:** Asignar Competencias al Programa (alternativa para asignar competencias después de creación)
- **RNF-01:** Acceso Restringido por Roles

---

## 4. REQUERIMIENTOS NO FUNCIONALES

### 4.1 RNF-01: Acceso Restringido por Roles

**Prioridad:** Crítica  
**Categoría:** Seguridad

**Descripción:** La funcionalidad de crear programas complementarios debe requerir autenticación de usuario y acceso exclusivo para rol de Administrador. El sistema no debe permitir acceso sin sesión activa ni sin los permisos adecuados.

**Criterios de Aceptación:**
- La ruta está protegida con middleware `auth` y verificación de rol
- Solo usuarios con rol Administrador pueden acceder
- Intentos de acceso sin autenticación redirigen a página de login
- Intentos de acceso con rol incorrecto retornan error 403

### 4.2 RNF-02: Validación de Datos en Tiempo Real

**Prioridad:** Alta  
**Categoría:** Usabilidad

**Descripción:** El sistema debe proporcionar validación en tiempo real de los campos del formulario, mostrando mensajes de error inmediatos mientras el usuario escribe o selecciona opciones.

**Criterios de Aceptación:**
- Validación de unicidad de código se realiza al perder foco del campo
- Validación de formato de fechas se realiza al ingresar datos
- Mensajes de error se muestran junto a cada campo afectado
- El botón de guardar se habilita solo cuando todos los campos son válidos

### 4.3 RNF-03: Rendimiento en Persistencia

**Prioridad:** Media  
**Categoría:** Rendimiento

**Descripción:** La operación de creación debe completarse en menos de 3 segundos incluso con múltiples relaciones (competencias, parámetros).

**Criterios de Aceptación:**
- Tiempo de respuesta total < 3 segundos
- Las transacciones de base de datos son atómicas
- Las relaciones se persisten de forma optimizada
- No hay consultas N+1 en el proceso de creación

---

## 5. CRITERIOS DE ACEPTACIÓN

### 5.1 Criterios Funcionales

**CA-001:** El sistema debe validar unicidad del código del programa
- **Verificación:** Se intenta crear programa con código existente
- **Resultado Esperado:** Error 422 con mensaje "El código del programa ya existe"

**CA-002:** El sistema debe requerir campos obligatorios
- **Verificación:** Se envía formulario con campos obligatorios vacíos
- **Resultado Esperado:** Error 422 con mensaje indicando campos faltantes

**CA-003:** El sistema debe validar fechas correctas
- **Verificación:** Se ingresa fecha de inicio anterior a hoy
- **Resultado Esperado:** Error 422 con mensaje de fechas inválidas

**CA-004:** El sistema debe persistir programa con todas sus relaciones
- **Verificación:** Se crea programa con 3 competencias asociadas
- **Resultado Esperado:** Programa creado en BD con 3 registros en tabla `programa_competencia`

**CA-005:** El sistema debe redirigir a vista de detalle después de creación exitosa
- **Verificación:** Se completa creación exitosa
- **Resultado Esperado:** Redirección a `/programas-complementarios/{id}` con mensaje de éxito

### 5.2 Criterios No Funcionales

**CA-006:** Solo administradores pueden crear programas
- **Verificación:** Usuario con rol Operador intenta acceder
- **Resultado Esperado:** Error 403 (Forbidden)

**CA-007:** Tiempo de respuesta menor a 3 segundos
- **Verificación:** Se mide tiempo desde envío hasta redirección
- **Resultado Esperado:** Tiempo de respuesta < 3 segundos

**CA-008:** Validación en tiempo real funciona
- **Verificación:** Se ingresa código duplicado y se cambia de campo
- **Resultado Esperado:** Mensaje de error aparece inmediatamente junto al campo

### 5.3 Criterios de Validación

**CA-009:** Manejo apropiado de errores de base de datos
- **Verificación:** Se simula fallo de conexión a BD durante creación
- **Resultado Esperado:** Mensaje de error claro y registro en log

**CA-010:** Transacciones atómicas
- **Verificación:** Se simula error al guardar competencias después de crear programa
- **Resultado Esperado:** Rollback completo, programa no creado

---

## 6. TRAZABILIDAD

### 6.1 Requerimientos Relacionados

| Requerimiento | Relación | Descripción |
|---------------|----------|-------------|
| RF-PC-002 | Siguiente | Ver Detalles del Programa (se accede después de creación) |
| RF-PC-003 | Alternativo | Eliminar Programa (acción posterior sobre programa creado) |
| RF-PC-004 | Complementario | Asignar Competencias (alternativa para asignación posterior) |
| RF-PC-005 | Depende | Listar Programas (muestra el programa creado) |
| RNF-01 | Depende | Acceso Restringido por Roles |
| RNF-02 | Depende | Validación de Datos en Tiempo Real |
| RNF-03 | Depende | Rendimiento en Persistencia |

### 6.2 Casos de Uso Relacionados

- **CU-PC-01:** Crear Programa Complementario

### 6.3 Componentes del Sistema

- **Controlador:** `ProgramaComplementarioController@crear()` y `@guardar()`
- **Servicio:** `ProgramaComplementarioService@crearPrograma()`
- **Repositorio:** `ComplementarioOfertadoRepository@crearConRelaciones()`
- **Repositorio:** `CompetenciaRepository@listarDisponibles()`
- **Vista:** `resources/views/complementarios/programas/crear.blade.php`
- **Rutas:** 
  - `GET /programas-complementarios/crear`
  - `POST /programas-complementarios`

### 6.4 Pruebas Relacionadas

- Test unitario del servicio `crearPrograma()`
- Test de integración del formulario de creación
- Test de validación de unicidad de código
- Test de permisos (solo administrador)
- Test de transacciones atómicas

---

## 7. GLOSARIO

| Término | Definición |
|--------|------------|
| **Programa Complementario** | Curso de formación complementaria ofertado por el SENA |
| **Modalidad** | Forma en que se imparte el programa: Presencial, Virtual o Mixta |
| **Jornada** | Horario de formación: Diurna, Nocturna o Mixta |
| **Competencia** | Habilidad, conocimiento o destreza que se espera desarrollar en el programa |
| **Cupo** | Número máximo de aspirantes que pueden inscribirse en el programa |

---

**FIN DEL DOCUMENT
