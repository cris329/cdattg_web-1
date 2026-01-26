# Guía de Implementación de Módulos Livewire - SENA CDATTG

## 🎯 Objetivo

Guía paso a paso para crear nuevos módulos siguiendo el patrón establecido en el sistema SENA CDATTG, basado en el módulo de **Resultados de Aprendizaje** como referencia.

---

## 📋 PASO 1: Preparación del Módulo

### 1.1 Definir Estructura Base
```
resources/views/tu_modulo/
├── index.blade.php

app/Livewire/TuModulo/
├── TuModeloIndex.php
├── TuModeloForm.php

resources/views/livewire/tu_modulo/
├── tu-modelo-index.blade.php
├── tu-modelo-form.blade.php

resources/js/pages/
└── tu-modulo-index.js

resources/css/
└── tu-modulo.css
```

### 1.2 Configurar Rutas
```php
// routes/tu_modulo/web_tu_modulo.php
Route::get('/tu-modulo', [TuModuloController::class, 'index'])->name('tu_modulo.index');
```

### 1.3 Crear Controller
```php
// app/Http/Controllers/TuModuloController.php
public function index()
{
    return view('tu_modulo.index');
}
```

---

## 📋 PASO 2: Vista Principal (index.blade.php)

### 2.1 Estructura Completa
```blade
@extends('adminlte::page')

@section('title', 'Tus Modelos')

@section('css')
    @vite(['resources/css/tu-modulo.css'])
@endsection

@section('content_header')
    <div class="admin-header">
        <div class="admin-header-content">
            <div class="admin-header-left">
                <div class="admin-header-icon">
                    <i class="fas fa-tu-icono"></i>
                </div>
                <div class="admin-header-text">
                    <h1 class="admin-header-title">Tus Modelos</h1>
                    <p class="admin-header-subtitle">Gestiona y administra los modelos del sistema</p>
                </div>
            </div>
            <nav aria-label="breadcrumb" class="admin-breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('verificarLogin') }}">
                            <i class="fas fa-home me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-tu-icono me-1"></i>Tus Modelos
                    </li>
                </ol>
            </nav>
        </div>
    </div>
@endsection

@section('content')
    <div class="main-card">
        <x-session-alerts />
        
        <livewire:tu-modulo.tu-modelo-index />
    </div>
@endsection

@section('footer')
    @include('layouts.footer')
@endsection

@section('js')
    @vite(['resources/js/pages/tu-modulo-index.js'])
@endsection
```

---

## 📋 PASO 3: Componente Principal (TuModeloIndex.php)

### 3.1 Estructura Base del Componente
```php
<?php

namespace App\Livewire\TuModulo;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TuModelo;
use Livewire\Attributes\On;

class TuModeloIndex extends Component
{
    use WithPagination;

    // Búsqueda y filtros
    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $statusFilter = '';
    public $otroFiltro = '';
    
    // Modales
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showShowModal = false;
    public $showDeleteModal = false;
    public $showGestionRelacionesModal = false;
    public $selectedModelo = null;
    public $selectedId = null;

    // Query string para URL
    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'statusFilter' => ['except' => ''],
        'otroFiltro' => ['except' => ''],
    ];

    // Event listeners
    protected $listeners = [
        'modeloCreado' => '$refresh',
        'modeloActualizado' => '$refresh',
        'modeloEliminado' => '$refresh',
        'closeModal' => 'handleCloseModal',
        'notify' => 'showNotification',
        'refreshModal' => 'handleRefreshModal',
    ];

    public function render()
    {
        $query = TuModelo::with(['relaciones']);
        
        // Filtro de búsqueda
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('codigo', 'like', '%' . $this->search . '%')
                  ->orWhere('nombre', 'like', '%' . $this->search . '%');
            });
        }
        
        // Filtro de estado
        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter === '1');
        }
        
        // Otro filtro
        if ($this->otroFiltro !== '') {
            $query->whereHas('relacion', function ($q) {
                $q->where('relaciones.id', $this->otroFiltro);
            });
        }
        
        // Ordenamiento
        $query->orderBy($this->sortField, $this->sortDirection);
        
        $modelos = $query->paginate($this->perPage);

        return view('livewire.tu-modulo.tu-modelo-index', compact('modelos'));
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    // Métodos de modales
    public function openCreateModal()
    {
        $this->showCreateModal = true;
    }

    public function openEditModal($modeloId)
    {
        $this->selectedModelo = TuModelo::with(['relaciones'])->find($modeloId);
        $this->showEditModal = true;
    }

    public function openShowModal($modeloId)
    {
        $this->selectedModelo = TuModelo::with(['relaciones', 'userCreate', 'userEdit'])->find($modeloId);
        $this->showShowModal = true;
    }

    public function confirmDelete($modeloId)
    {
        $this->selectedModelo = TuModelo::with(['relaciones'])->find($modeloId);
        $this->showDeleteModal = true;
    }

    public function openGestionRelacionesModal($modeloId)
    {
        $this->selectedModelo = TuModelo::find($modeloId);
        $this->showGestionRelacionesModal = true;
    }

    // Métodos de cierre
    public function handleCloseModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showShowModal = false;
        $this->showDeleteModal = false;
        $this->showGestionRelacionesModal = false;
        $this->selectedModelo = null;
    }

    // CRUD
    public function deleteModelo($modeloId)
    {
        $modelo = TuModelo::with(['relaciones'])->find($modeloId);
        
        if (!$modelo) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Modelo no encontrado',
            ]);
            return;
        }
        
        // Validaciones antes de eliminar
        if ($modelo->relaciones->count() > 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se puede eliminar. Tiene relaciones asociadas.',
            ]);
            return;
        }
        
        try {
            $codigo = $modelo->codigo;
            $modelo->delete();
            $this->closeDeleteModal();
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => "Modelo '{$codigo}' eliminado correctamente",
            ]);
            $this->dispatch('modeloEliminado');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar: ' . $e->getMessage(),
            ]);
        }
    }

    public function toggleStatus($modeloId)
    {
        $modelo = TuModelo::find($modeloId);
        
        if (!$modelo) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Modelo no encontrado',
            ]);
            return;
        }

        $modelo->status = !$modelo->status;
        $modelo->save();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Estado actualizado correctamente",
        ]);
    }
}
```

---

## 📋 PASO 4: Modales de Confirmación - Sistema SENA

### 4.1 ¿Qué es el Sistema de Modales de Confirmación?

El sistema SENA tiene un **sistema global de modales de confirmación** que permite:
- ✅ Confirmar acciones críticas (eliminar, desasignar, etc.)
- ✅ Diseño profesional y consistente
- ✅ Reutilizable en todos los componentes
- ✅ Sin duplicación de código

### 4.2 Estructura del Sistema

#### **4.2.1 Modal Global (en `resources/views/layouts/partials/global-modals.blade.php`)**
```html
<!-- Modal Global - Cargado UNA SOLA VEZ -->
<div id="globalConfirmModal">
    <div class="modal-confirm">
        <div class="modal-confirm-icon success">
            <i class="fas fa-link"></i>
        </div>
        <h5 class="modal-confirm-title">Confirmar acción</h5>
        <p class="modal-confirm-text">¿Desea realizar esta acción?</p>
        <div class="modal-confirm-item">
            <span class="code-pill">38362</span>
            <span class="tag">Competencia</span>
            <span>Modelado de los artefactos del software</span>
        </div>
        <div class="modal-confirm-actions">
            <button type="button" class="btn btn-light" onclick="closeConfirmModal()">Cancelar</button>
            <button type="button" class="btn btn-primary btn-confirm" onclick="confirmAction()">Confirmar</button>
        </div>
    </div>
</div>
```

#### **4.2.2 JavaScript Global (en el mismo archivo)**
```javascript
// Función principal del modal
function showConfirmModal(title, message, type, action, params, codigo, nombre) {
    modalConfig = {action, params};
    
    const modal = document.getElementById('globalConfirmModal');
    const titleEl = modal.querySelector('.modal-confirm-title');
    const messageEl = modal.querySelector('.modal-confirm-text');
    const iconEl = modal.querySelector('.modal-confirm-icon');
    const codeEl = modal.querySelector('.code-pill');
    const tagEl = modal.querySelector('.tag');
    const nameEl = modal.querySelector('.modal-confirm-item span:last-child');
    const confirmBtn = modal.querySelector('.btn-confirm');
    
    // Configurar contenido
    titleEl.textContent = title;
    messageEl.textContent = message;
    
    // Configurar icono según tipo
    iconEl.className = 'modal-confirm-icon';
    switch(type) {
        case 'danger':
            iconEl.classList.add('danger');
            iconEl.innerHTML = '<i class="fas fa-unlink"></i>';
            break;
        case 'info':
        case 'success':
            iconEl.classList.add('success');
            iconEl.innerHTML = '<i class="fas fa-link"></i>';
            break;
        default:
            iconEl.classList.add('success');
            iconEl.innerHTML = '<i class="fas fa-check"></i>';
    }
    
    // Configurar información
    if (codeEl) codeEl.textContent = codigo;
    if (tagEl) tagEl.textContent = 'Competencia';
    if (nameEl) nameEl.textContent = nombre;
    
    // Configurar botón
    if (confirmBtn) {
        confirmBtn.className = 'btn btn-' + type;
        confirmBtn.textContent = type === 'danger' ? 'Quitar' : 'Asignar';
    }
    
    // Mostrar modal
    modal.style.display = 'block';
    modal.classList.add('show');
}

function closeConfirmModal() {
    const modal = document.getElementById('globalConfirmModal');
    modal.style.display = 'none';
    modal.classList.remove('show');
    modalConfig = null;
}

function confirmAction() {
    if (!modalConfig) return;
    
    // Enviar evento a Livewire
    Livewire.dispatch('confirmAction', {
        action: modalConfig.action,
        params: modalConfig.params
    });
    
    closeConfirmModal();
}
```

### 4.3 Cómo Implementar en tu Componente

#### **4.3.1 Paso 1: Agregar Listener al Componente**
```php
class TuModeloIndex extends Component
{
    protected $listeners = [
        'confirmAction' => 'handleConfirmedAction',
        // ... otros listeners
    ];

    public function handleConfirmedAction($action, $params)
    {
        if ($action === 'eliminarModelo') {
            $this->eliminarModelo($params);
        } else if ($action === 'desasignarRelacion') {
            $this->desasignarRelacion($params);
        }
        // ... más acciones
    }
}
```

#### **4.3.2 Paso 2: Crear Funciones JavaScript en tu Vista**
```javascript
// En tu vista Blade (directamente, no en @push)
<script>
// Función para confirmar eliminación
window.confirmarEliminarModelo = function(modeloId, nombreCompleto) {
    const partes = nombreCompleto.split(' - ');
    const codigo = partes[0] || modeloId;
    const nombre = partes[1] || nombreCompleto;
    
    showConfirmModal(
        'Eliminar modelo',
        '¿Desea eliminar este modelo del sistema?',
        'danger',
        'eliminarModelo',
        modeloId,
        codigo,
        nombre
    );
};

// Función para confirmar desasignación
window.confirmarDesasignarRelacion = function(relacionId, nombreCompleto) {
    const partes = nombreCompleto.split(' - ');
    const codigo = partes[0] || relacionId;
    const nombre = partes[1] || nombreCompleto;
    
    showConfirmModal(
        'Desasignar relación',
        '¿Desea quitar esta relación del modelo?',
        'danger',
        'desasignarRelacion',
        relacionId,
        codigo,
        nombre
    );
};

// Escuchar respuesta de la modal
document.addEventListener('livewire:init', () => {
    Livewire.on('confirmAction', (data) => {
        // El listener del componente manejará la acción
        console.log('Acción confirmada:', data);
    });
});
</script>
```

#### **4.3.3 Paso 3: Usar en los Botones**
```blade
<!-- Botón de Eliminar -->
<button onclick="confirmarEliminarModelo({{ $modelo->id }}, '{{ $modelo->codigo }} - {{ $modelo->nombre }}')" 
        class="btn btn-danger btn-sm">
    <i class="fas fa-trash"></i> Eliminar
</button>

<!-- Botón de Desasignar -->
<button onclick="confirmarDesasignarRelacion({{ $relacion->id }}, '{{ $relacion->codigo }} - {{ $relacion->nombre }}')" 
        class="btn btn-warning btn-sm">
    <i class="fas fa-unlink"></i> Quitar
</button>
```

### 4.4 Ejemplo Completo: Gestión de Resultados

#### **4.4.1 Componente Livewire**
```php
class GestionarResultados extends Component
{
    protected $listeners = [
        'confirmAction' => 'handleConfirmedAction',
    ];

    public function handleConfirmedAction($action, $params)
    {
        if ($action === 'desasignarResultado') {
            $this->desasignarResultado($params);
        } else if ($action === 'asignarResultado') {
            $this->asignarResultadoDirecto($params);
        }
    }

    public function desasignarResultado($resultadoId)
    {
        try {
            $resultado = ResultadosAprendizaje::find($resultadoId);
            
            if (!$resultado) {
                return;
            }

            // Desasociar el resultado
            $this->guia->resultadosAprendizaje()->detach($resultadoId);

            // Recargar los resultados
            $this->cargarResultados();

        } catch (\Exception $e) {
            \Log::error('Error en desasignarResultado: ' . $e->getMessage());
        }
    }
}
```

#### **4.4.2 JavaScript en la Vista**
```javascript
<script>
window.confirmarDesasignarResultado = function(resultadoId, nombreCompleto) {
    const partes = nombreCompleto.split(' - ');
    const codigo = partes[0] || resultadoId;
    const nombre = partes[1] || nombreCompleto;
    
    showConfirmModal(
        'Desasignar resultado',
        '¿Desea quitar este resultado de la guía?',
        'danger',
        'desasignarResultado',
        resultadoId,
        codigo,
        nombre
    );
};

window.confirmarAsignarResultado = function(resultadoId, nombreCompleto) {
    const partes = nombreCompleto.split(' - ');
    const codigo = partes[0] || resultadoId;
    const nombre = partes[1] || nombreCompleto;
    
    showConfirmModal(
        'Asignar resultado',
        '¿Desea asignar este resultado a la guía?',
        'success',
        'asignarResultado',
        resultadoId,
        codigo,
        nombre
    );
};

document.addEventListener('livewire:init', () => {
    Livewire.on('confirmAction', (data) => {
        if (data.action === 'desasignarResultado') {
            @this.desasignarResultado(data.params);
        } else if (data.action === 'asignarResultado') {
            @this.asignarResultadoDirecto(data.params);
        }
    });
});
</script>
```

### 4.5 Tipos de Modal Disponibles

#### **4.5.1 Modal de Peligro (danger)**
```javascript
showConfirmModal(
    'Título peligroso',
    'Mensaje de advertencia',
    'danger',
    'accionPeligrosa',
    params,
    codigo,
    nombre
);
```
- **Icono:** `fas fa-unlink` (rojo)
- **Botón:** "Quitar" (rojo)
- **Uso:** Eliminar, desasignar, acciones irreversibles

#### **4.5.2 Modal de Éxito (success)**
```javascript
showConfirmModal(
    'Título exitoso',
    'Mensaje de confirmación',
    'success',
    'accionExitosa',
    params,
    codigo,
    nombre
);
```
- **Icono:** `fas fa-link` (verde)
- **Botón:** "Asignar" (verde)
- **Uso:** Asignar, vincular, acciones positivas

#### **4.5.3 Modal de Información (info)**
```javascript
showConfirmModal(
    'Título informativo',
    'Mensaje informativo',
    'info',
    'accionInformativa',
    params,
    codigo,
    nombre
);
```
- **Icono:** `fas fa-link` (azul)
- **Botón:** "Confirmar" (azul)
- **Uso:** Acciones informativas, no críticas

### 4.6 Mejores Prácticas

#### **4.6.1 ✅ Buenas Prácticas**
- **Usar siempre el sistema global** - No crear modales personalizadas
- **Nombres descriptivos** - `confirmarEliminarModelo`, `confirmarDesasignarRelacion`
- **Validar en el componente** - Verificar existencia antes de ejecutar
- **Logging de errores** - Usar `\Log::error()` en lugar de notificaciones
- **Fallback simple** - Si `showConfirmModal` no existe, usar `confirm()`

#### **4.6.2 ❌ Malas Prácticas**
- **Crear modales personalizadas** - Duplica código y diseño
- **Usar `wire:click` para confirmaciones** - No funciona con modales globales
- **Olvidar el listener** - El componente no recibirá la confirmación
- **Notificar errores locales** - Duplica notificaciones del sistema

#### **4.6.3 🎯 Ejemplo Completo con Fallback**
```javascript
window.confirmarAccionCritica = function(id, nombreCompleto) {
    const partes = nombreCompleto.split(' - ');
    const codigo = partes[0] || id;
    const nombre = partes[1] || nombreCompleto;
    
    if (typeof showConfirmModal === 'function') {
        // Usar modal global del sistema
        showConfirmModal(
            'Confirmar acción crítica',
            '¿Desea realizar esta acción irreversible?',
            'danger',
            'accionCritica',
            id,
            codigo,
            nombre
        );
    } else {
        // Fallback simple si el sistema no está disponible
        if (confirm('¿Desea realizar esta acción? (ID: ' + id + ')')) {
            Livewire.dispatch('confirmAction', {
                action: 'accionCritica',
                params: id
            });
        }
    }
};
```

---

## 📋 PASO 5: CSS y Estilos

### 5.1 Estructura CSS Base
```css
/* resources/css/tu-modulo.css */
.admin-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.admin-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.admin-header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.admin-header-icon {
    width: 48px;
    height: 48px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.admin-header-title {
    font-size: 1.75rem;
    font-weight: 600;
    margin: 0;
}

.admin-header-subtitle {
    margin: 0.25rem 0 0 0;
    opacity: 0.9;
}

.admin-breadcrumb {
    margin: 0;
}

.breadcrumb-item {
    color: rgba(255, 255, 255, 0.8);
}

.breadcrumb-item a {
    color: white;
    text-decoration: none;
}

.breadcrumb-item.active {
    color: white;
}

.main-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
}

/* Estilos de tabla */
.modern-table {
    width: 100%;
    border-collapse: collapse;
}

.modern-table th {
    background: #f8f9fa;
    padding: 0.75rem;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
}

.modern-table td {
    padding: 0.75rem;
    border-bottom: 1px solid #dee2e6;
}

.modern-table tr:hover {
    background: #f8f9fa;
}

/* Badges */
.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge.active {
    background: #d4edda;
    color: #155724;
}

.status-badge.inactive {
    background: #f8d7da;
    color: #721c24;
}
```

---

## 📋 PASO 6: JavaScript Adicional

### 6.1 Estructura JavaScript
```javascript
// resources/js/pages/tu-modulo-index.js
document.addEventListener('DOMContentLoaded', function() {
    // Inicialización del módulo
    console.log('Módulo TuModulo inicializado');
    
    // Event listeners específicos del módulo
    const tabla = document.querySelector('.modern-table');
    if (tabla) {
        // Agregar interactividad a la tabla
        tabla.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-action')) {
                // Manejar clics en botones de acción
                console.log('Acción en tabla:', e.target.dataset.action);
            }
        });
    }
});

// Funciones globales del módulo
window.tuModuloHelpers = {
    // Funciones auxiliares
    formatearFecha: function(fecha) {
        return new Date(fecha).toLocaleDateString('es-ES');
    },
    
    formatearNumero: function(numero) {
        return new Intl.NumberFormat('es-CO').format(numero);
    }
};
```

---

## 📋 PASO 7: Testing y Validación

### 7.1 Pruebas Unitarias
```php
// tests/Feature/TuModuloTest.php
class TuModuloTest extends TestCase
{
    public function test_puede_crear_modelo()
    {
        $modelo = TuModelo::factory()->create();
        
        $this->assertDatabaseHas('tu_tabla', [
            'id' => $modelo->id,
            'codigo' => $modelo->codigo,
        ]);
    }

    public function test_puede_eliminar_modelo()
    {
        $modelo = TuModelo::factory()->create();
        
        $response = $this->delete("/tu-modulo/{$modelo->id}");
        
        $this->assertDatabaseMissing('tu_tabla', [
            'id' => $modelo->id,
        ]);
    }
}
```

### 7.2 Pruebas de JavaScript
```javascript
// tests/js/tu-modulo.test.js
describe('TuModulo', () => {
    test('debe inicializar correctamente', () => {
        expect(document.querySelector('.admin-header')).toBeTruthy();
    });

    test('debe mostrar modal de confirmación', () => {
        // Simular clic en botón de eliminar
        const botonEliminar = document.querySelector('.btn-danger');
        botonEliminar.click();
        
        // Verificar que la modal aparezca
        expect(document.getElementById('globalConfirmModal').style.display).toBe('block');
    });
});
```

---

## 📋 PASO 8: Despliegue

### 8.1 Compilar Assets
```bash
npm run build
```

### 8.2 Limpiar Cache
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

### 8.3 Verificar Funcionalidad
1. **Acceder** a la ruta del módulo
2. **Probar** todas las acciones CRUD
3. **Verificar** las modales de confirmación
4. **Validar** los estilos y responsive

---

## 🎯 Resumen de Implementación

### ✅ Checklist Final
- [ ] Estructura de archivos creada
- [ ] Rutas configuradas
- [ ] Controller implementado
- [ ] Componente Livewire funcional
- [ ] Vistas Blade con diseño consistente
- [ ] CSS optimizado y responsive
- [ ] JavaScript con interactividad
- [ ] Modales de confirmación integradas
- [ ] Tests unitarios funcionales
- [ ] Documentación completa

### 🚀 Siguientes Pasos
1. **Personalizar** los estilos según la marca
2. **Agregar** más funcionalidades específicas
3. **Optimizar** performance si es necesario
4. **Documentar** APIs si aplica

---

## 📚 Referencias Adicionales

- [Documentación de Livewire](https://laravel-livewire.com/docs)
- [Guía de CSS del sistema](resources/css/template-styles.css)
- [Componentes reutilizables](resources/views/components/)
- [Ejemplos de módulos existentes](app/Livewire/)

---

**Nota:** Esta guía es un punto de partida. Adapta las estructuras según las necesidades específicas de tu módulo, manteniendo siempre la consistencia con el patrón establecido en el sistema SENA CDATTG.