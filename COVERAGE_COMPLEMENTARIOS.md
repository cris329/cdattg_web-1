# 📊 Análisis de Coverage - Módulo de Complementarios

**Fecha de análisis:** 30 de Noviembre de 2025  
**Generado por:** PHPUnit 11.5.44 con php-code-coverage 11.0.11

---

## 📈 Resumen General

Este documento presenta un análisis detallado del coverage de código del módulo de **Complementarios**, incluyendo todos sus componentes: controladores, servicios, repositorios, modelos, requests, commands, jobs, y la integración con **Sofia el Bot**.

---

## 🎯 Coverage por Categoría

### 1. Controllers (Controladores)

**Ubicación:** `app/Http/Controllers/Complementarios/`

| Archivo | Líneas | Métodos | Clases | Estado |
|---------|--------|---------|--------|--------|
| **Total** | **49.07%** (265/540) | **60.38%** (32/53) | **14.29%** (1/7) | ⚠️ Medio |
| `AspiranteComplementarioController.php` | 21.77% (27/124) | 64.29% (9/14) | 0.00% (0/1) | 🔴 Bajo |
| `DocumentoComplementarioController.php` | 0.00% (0/113) | 0.00% (0/5) | 0.00% (0/1) | 🔴 **SIN COVERAGE** |
| `EstadisticaComplementarioController.php` | 0.00% (0/17) | 0.00% (0/4) | 0.00% (0/1) | 🔴 **SIN COVERAGE** |
| `InscripcionComplementarioController.php` | 100.00% (7/7) | 100.00% (5/5) | 100.00% (1/1) | ✅ Completo |
| `PerfilComplementarioController.php` | 0.00% (0/23) | 0.00% (0/2) | 0.00% (0/1) | 🔴 **SIN COVERAGE** |
| `ProgramaComplementarioController.php` | 89.21% (124/139) | 71.43% (10/14) | 0.00% (0/1) | ⚠️ Medio |
| `ValidacionSofiaController.php` | 91.45% (107/117) | 88.89% (8/9) | 0.00% (0/1) | ✅ Alto |

**📝 Archivos que requieren tests:**
- ❌ `DocumentoComplementarioController.php` - **0% coverage**
- ❌ `EstadisticaComplementarioController.php` - **0% coverage**
- ❌ `PerfilComplementarioController.php` - **0% coverage**
- ⚠️ `AspiranteComplementarioController.php` - Solo 21.77%, necesita más tests

---

### 2. Services (Servicios)

**Ubicación:** `app/Services/Complementarios/`

| Archivo | Líneas | Métodos | Clases | Estado |
|---------|--------|---------|--------|--------|
| **Total** | **59.02%** (746/1264) | **50.83%** (61/120) | **0.00%** (0/11) | ⚠️ Medio |
| `AspiranteComplementarioService.php` | 31.58% (24/76) | 40.00% (4/10) | 0.00% (0/1) | 🔴 Bajo |
| `AspiranteDocumentoService.php` | 49.59% (61/123) | 25.00% (4/16) | 0.00% (0/1) | 🔴 Bajo |
| `AspiranteExportService.php` | 72.62% (61/84) | 62.50% (5/8) | 0.00% (0/1) | ⚠️ Medio |
| `AspiranteManagementService.php` | 76.38% (152/199) | 42.86% (6/14) | 0.00% (0/1) | ⚠️ Medio |
| `ComplementarioService.php` | 94.17% (97/103) | 73.33% (11/15) | 0.00% (0/1) | ✅ Alto |
| `EstadisticaComplementarioService.php` | 19.21% (29/151) | 57.14% (4/7) | 0.00% (0/1) | 🔴 **MUY BAJO** |
| `InscripcionComplementarioService.php` | 80.98% (149/184) | 66.67% (10/15) | 0.00% (0/1) | ⚠️ Medio |

**📝 Archivos que requieren más tests:**
- ❌ `EstadisticaComplementarioService.php` - Solo 19.21%, **PRIORIDAD ALTA**
- ❌ `AspiranteComplementarioService.php` - Solo 31.58%
- ❌ `AspiranteDocumentoService.php` - Solo 49.59%

---

### 3. Services Sofia (Bot Sofia)

**Ubicación:** `app/Services/Complementarios/Sofia/`

| Archivo | Líneas | Métodos | Clases | Estado |
|---------|--------|---------|--------|--------|
| **Total** | **50.29%** (173/344) | **48.57%** (17/35) | **0.00%** (0/4) | ⚠️ Medio |
| `SofiaHttpClient.php` | 93.70% (119/127) | 90.00% (9/10) | 0.00% (0/1) | ✅ Alto |
| `SofiaStateMapper.php` | 93.33% (42/45) | 71.43% (5/7) | 0.00% (0/1) | ✅ Alto |
| `SofiaValidationProcessor.php` | 4.17% (3/72) | 9.09% (1/11) | 0.00% (0/1) | 🔴 **MUY BAJO** |
| `SofiaValidationService.php` | 9.00% (9/100) | 28.57% (2/7) | 0.00% (0/1) | 🔴 **MUY BAJO** |

**📝 Archivos que requieren más tests:**
- ❌ `SofiaValidationProcessor.php` - Solo 4.17%, **PRIORIDAD CRÍTICA**
- ❌ `SofiaValidationService.php` - Solo 9.00%, **PRIORIDAD CRÍTICA**

---

### 4. Repositories (Repositorios)

**Ubicación:** `app/Repositories/Complementarios/`

| Archivo | Líneas | Métodos | Clases | Estado |
|---------|--------|---------|--------|--------|
| **Total** | **51.32%** (78/152) | **61.76%** (21/34) | **0.00%** (0/3) | ⚠️ Medio |
| `AspiranteComplementarioRepository.php` | 42.45% (45/106) | 58.82% (10/17) | 0.00% (0/1) | 🔴 Bajo |
| `ComplementarioOfertadoRepository.php` | 97.06% (33/34) | 91.67% (11/12) | 0.00% (0/1) | ✅ Alto |
| `SenasofiaplusValidationLogRepository.php` | 0.00% (0/12) | 0.00% (0/5) | 0.00% (0/1) | 🔴 **SIN COVERAGE** |

**📝 Archivos que requieren tests:**
- ❌ `SenasofiaplusValidationLogRepository.php` - **0% coverage**
- ⚠️ `AspiranteComplementarioRepository.php` - Solo 42.45%

---

### 5. Models (Modelos)

**Ubicación:** `app/Models/Complementarios/`

| Archivo | Líneas | Métodos | Clases | Estado |
|---------|--------|---------|--------|--------|
| **Total** | **87.30%** (110/126) | **75.00%** (27/36) | **40.00%** (2/5) | ⚠️ Medio |
| `AspiranteComplementario.php` | 70.00% (7/10) | 75.00% (3/4) | 0.00% (0/1) | ⚠️ Medio |
| `CategoriaCaracterizacionComplementario.php` | 0.00% (0/10) | 0.00% (0/5) | 0.00% (0/1) | 🔴 **SIN COVERAGE** |
| `ComplementarioOfertado.php` | 100.00% (49/49) | 100.00% (12/12) | 100.00% (1/1) | ✅ Completo |
| `SenasofiaplusValidationLog.php` | 100.00% (27/27) | 100.00% (6/6) | 100.00% (1/1) | ✅ Completo |
| `SofiaValidationProgress.php` | 90.00% (27/30) | 66.67% (6/9) | 0.00% (0/1) | ✅ Alto |

**📝 Archivos que requieren tests:**
- ❌ `CategoriaCaracterizacionComplementario.php` - **0% coverage**

---

### 6. Requests (Validaciones de Formularios)

**Ubicación:** `app/Http/Requests/Complementarios/`

| Archivo | Líneas | Métodos | Clases | Estado |
|---------|--------|---------|--------|--------|
| **Total** | **99.52%** (209/210) | **93.75%** (15/16) | **80.00%** (4/5) | ✅ Excelente |
| `AspiranteRequest.php` | 100.00% (10/10) | 100.00% (3/3) | 100.00% (1/1) | ✅ Completo |
| `InscripcionComplementarioRequest.php` | 100.00% (70/70) | 100.00% (4/4) | 100.00% (1/1) | ✅ Completo |
| `InscripcionGeneralRequest.php` | 98.04% (50/51) | 66.67% (2/3) | 0.00% (0/1) | ✅ Alto |
| `StoreProgramaComplementarioRequest.php` | 100.00% (39/39) | 100.00% (3/3) | 100.00% (1/1) | ✅ Completo |
| `UpdateProgramaComplementarioRequest.php` | 100.00% (40/40) | 100.00% (3/3) | 100.00% (1/1) | ✅ Completo |

**📝 Estado:** ✅ **Excelente coverage** - Solo falta cubrir 1 línea en `InscripcionGeneralRequest.php`

---

### 7. Commands (Comandos de Consola)

**Ubicación:** `app/Console/Commands/Complementarios/`

| Archivo | Líneas | Métodos | Clases | Estado |
|---------|--------|---------|--------|--------|
| **Total** | **18.52%** (5/27) | **0.00%** (0/1) | **0.00%** (0/1) | 🔴 Bajo |
| `ValidarSofiaCommand.php` | 18.52% (5/27) | 0.00% (0/1) | 0.00% (0/1) | 🔴 **MUY BAJO** |

**📝 Archivos que requieren tests:**
- ❌ `ValidarSofiaCommand.php` - Solo 18.52%, necesita más tests

---

### 8. Jobs (Trabajos en Cola)

**Ubicación:** `app/Jobs/Complementarios/`

| Archivo | Líneas | Métodos | Clases | Estado |
|---------|--------|---------|--------|--------|
| **Total** | **84.00%** (42/50) | **66.67%** (6/9) | **0.00%** (0/1) | ⚠️ Medio |
| `ValidarSofiaJob.php` | 84.00% (42/50) | 66.67% (6/9) | 0.00% (0/1) | ⚠️ Medio |

**📝 Estado:** ⚠️ Coverage aceptable, pero puede mejorarse

---

### 9. Exceptions (Excepciones)

**Ubicación:** `app/Exceptions/Complementarios/`

| Archivo | Líneas | Métodos | Clases | Estado |
|---------|--------|---------|--------|--------|
| **Total** | **n/a** (0/0) | **n/a** (0/0) | **n/a** (0/0) | ⚠️ Sin ejecutar |
| `GoogleDriveException.php` | n/a | n/a | n/a | ⚠️ Sin ejecutar |
| `SofiaConnectionException.php` | n/a | n/a | n/a | ⚠️ Sin ejecutar |
| `SofiaHttpErrorException.php` | n/a | n/a | n/a | ⚠️ Sin ejecutar |
| `SofiaInvalidResponseException.php` | n/a | n/a | n/a | ⚠️ Sin ejecutar |
| `SofiaRequestException.php` | n/a | n/a | n/a | ⚠️ Sin ejecutar |
| `SofiaServiceErrorException.php` | n/a | n/a | n/a | ⚠️ Sin ejecutar |
| `SofiaUnexpectedStatusException.php` | n/a | n/a | n/a | ⚠️ Sin ejecutar |

**📝 Nota:** Las excepciones no tienen coverage porque no se han ejecutado en los tests. Se recomienda crear tests que verifiquen el manejo de excepciones.

---

## 🎯 Resumen de Archivos Sin Coverage o Con Coverage Muy Bajo

### 🔴 **PRIORIDAD CRÍTICA** (0% - 10% coverage)

1. **Controllers:**
   - `DocumentoComplementarioController.php` - **0%**
   - `EstadisticaComplementarioController.php` - **0%**
   - `PerfilComplementarioController.php` - **0%**

2. **Services:**
   - `SofiaValidationProcessor.php` - **4.17%**
   - `SofiaValidationService.php` - **9.00%**

3. **Repositories:**
   - `SenasofiaplusValidationLogRepository.php` - **0%**

4. **Models:**
   - `CategoriaCaracterizacionComplementario.php` - **0%**

5. **Commands:**
   - `ValidarSofiaCommand.php` - **18.52%** (aunque está por encima del 10%, necesita más tests)

### ⚠️ **PRIORIDAD ALTA** (10% - 50% coverage)

1. **Controllers:**
   - `AspiranteComplementarioController.php` - **21.77%**

2. **Services:**
   - `EstadisticaComplementarioService.php` - **19.21%**
   - `AspiranteComplementarioService.php` - **31.58%**
   - `AspiranteDocumentoService.php` - **49.59%**

3. **Repositories:**
   - `AspiranteComplementarioRepository.php` - **42.45%**

---

## 📋 Archivos con Coverage Excelente (90%+)

✅ **Completamente cubiertos o con coverage excelente:**

- `InscripcionComplementarioController.php` - **100%**
- `ComplementarioOfertado.php` (Model) - **100%**
- `SenasofiaplusValidationLog.php` (Model) - **100%**
- `AspiranteRequest.php` - **100%**
- `InscripcionComplementarioRequest.php` - **100%**
- `StoreProgramaComplementarioRequest.php` - **100%**
- `UpdateProgramaComplementarioRequest.php` - **100%**
- `ComplementarioOfertadoRepository.php` - **97.06%**
- `ComplementarioService.php` - **94.17%**
- `SofiaHttpClient.php` - **93.70%**
- `SofiaStateMapper.php` - **93.33%**
- `ValidacionSofiaController.php` - **91.45%**
- `SofiaValidationProgress.php` (Model) - **90.00%**
- `ProgramaComplementarioController.php` - **89.21%**

---

## 🎯 Recomendaciones

### Prioridad 1: Archivos Críticos Sin Coverage

1. **Crear tests para Controllers sin coverage:**
   - `DocumentoComplementarioControllerTest.php` (no existe)
   - `EstadisticaComplementarioControllerTest.php` (no existe)
   - `PerfilComplementarioControllerTest.php` (no existe)

2. **Mejorar tests de Sofia (Bot):**
   - `SofiaValidationProcessorTest.php` - Aumentar de 4.17% a al menos 80%
   - `SofiaValidationServiceTest.php` - Aumentar de 9.00% a al menos 80%

3. **Crear tests para Repositories:**
   - `SenasofiaplusValidationLogRepositoryTest.php` - Crear desde cero

4. **Crear tests para Models:**
   - `CategoriaCaracterizacionComplementarioModelTest.php` - Crear desde cero

### Prioridad 2: Mejorar Coverage Existente

1. **Aspirantes Complementarios:**
   - Mejorar `AspiranteComplementarioControllerTest.php` (actualmente 21.77%)
   - Mejorar `AspiranteComplementarioServiceTest.php` (actualmente 31.58%)
   - Mejorar `AspiranteComplementarioRepositoryTest.php` (actualmente 42.45%)

2. **Estadísticas:**
   - Mejorar `EstadisticaComplementarioServiceTest.php` (actualmente 19.21%)

3. **Documentos:**
   - Mejorar `AspiranteDocumentoServiceTest.php` (actualmente 49.59%)

4. **Commands:**
   - Mejorar `ValidarSofiaCommandTest.php` (actualmente 18.52%)

### Prioridad 3: Tests de Excepciones

Crear tests que verifiquen el manejo correcto de excepciones:
- Tests para todas las excepciones de Sofia
- Tests para `GoogleDriveException`

---

## 📊 Estadísticas Generales del Módulo

| Categoría | Coverage Líneas | Coverage Métodos | Coverage Clases | Estado General |
|-----------|----------------|------------------|-----------------|----------------|
| **Controllers** | 49.07% | 60.38% | 14.29% | ⚠️ Medio |
| **Services** | 59.02% | 50.83% | 0.00% | ⚠️ Medio |
| **Services Sofia** | 50.29% | 48.57% | 0.00% | ⚠️ Medio |
| **Repositories** | 51.32% | 61.76% | 0.00% | ⚠️ Medio |
| **Models** | 87.30% | 75.00% | 40.00% | ⚠️ Medio |
| **Requests** | 99.52% | 93.75% | 80.00% | ✅ Excelente |
| **Commands** | 18.52% | 0.00% | 0.00% | 🔴 Bajo |
| **Jobs** | 84.00% | 66.67% | 0.00% | ⚠️ Medio |
| **Exceptions** | n/a | n/a | n/a | ⚠️ Sin ejecutar |

**📈 Coverage Promedio del Módulo:** ~60% (aproximado)

---

## 📝 Notas Finales

- El módulo tiene un coverage general aceptable, pero hay áreas críticas que requieren atención inmediata.
- Los **Requests** tienen excelente coverage (99.52%), lo cual es positivo.
- Los **Controllers** y **Services** necesitan más tests, especialmente los relacionados con:
  - Documentos
  - Estadísticas
  - Validación Sofia (Bot)
- Se recomienda priorizar los tests de **Sofia** ya que es una funcionalidad crítica del módulo.

---

**Última actualización:** 30 de Noviembre de 2025  
**Generado desde:** `/home/jhon/Documents/Sena/cdattg_web/storage/coverage/`

