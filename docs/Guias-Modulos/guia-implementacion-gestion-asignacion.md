# 📋 Guía de Implementación - Sistema de Gestión de Asignación

## 🎯 Propósito

Esta guía proporciona una referencia completa para implementar sistemas de gestión de asignación (asociar/desasociar elementos) en cualquier módulo del sistema SENA CDATTG, basada en la implementación probada en **Resultados de Aprendizaje**.

---

## 🏗️ Arquitectura General

### Componentes Principales
```
┌─────────────────────────────────────────────────────────────┐
│                    Componente Principal                     │
│  (Ej: ResultadoAprendizajeIndex)                           │
│  ┌─────────────────┐  ┌─────────────────────────────────┐  │
│  │   Lista Tabla   │  │      Modal de Gestión           │  │
│  │                 │  │                                 │  │
│  │ ┌─────────────┐ │  │ ┌─────────────────────────────┐ │  │
│  │ │ Botón       │ │  │ │ Subcomponente de Gestión    │ │  │
│  │ │ "Gestionar" │─┼──┼─▶│ (GestionarCompetencias)     │ │  │
│  │ └─────────────┘ │  │ │                             │ │  │
│  └─────────────────┘  │ │ ┌─────────┐ ┌─────────────┐ │ │  │
│                       │ │ │Asignados│ │Disponibles │ │ │  │
│                       │ │ └─────────┘ └─────────────┘ │ │  │
│                       │ └─────────────────────────────┘ │  │
│                       └─────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

---

## 📝 Implementación Paso a Paso

### 1. Componente Principal (Index)

#### **Propiedades PHP Necesarias**
```php
// Propiedades para controlar el modal
public $showGestionModal = false;
public $selectedItem = null;

// Listeners para eventos del subcomponente
protected $listeners = [
    'asignacionesActualizadas' => '$refresh',
];
```

#### **Métodos PHP**
```php
public function openGestionModal($itemId)
{
    $this->selectedItem = TuModelo::with(['relacion'])->find($itemId);
    $this->showGestionModal = true;
    
    // Cerrar otros modales si están abiertos
    if ($this->showOtherModal) {
        $this->showOtherModal = false;
    }
}

public function handleCloseModal()
{
    $this->showGestionModal = false;
    $this->selectedItem = null;
}
```

#### **Botón en la Tabla**
```blade
@can('GESTIONAR RELACION MODULO')
    <button wire:click="openGestionModal({{ $item->id }})" 
            class="btn-action btn-gestion" 
            title="Gestionar relaciones">
        <i class="fas fa-link"></i>
    </button>
@endcan
```

#### **Modal en la Vista**
```blade
<!-- Modal Gestión de Relaciones -->
@if ($showGestionModal && $selectedItem)
    <div class="modal-overlay" wire:click="$set('showGestionModal', false)">
        <div class="modal-container modal-lg" wire:click.stop>
            
            <!-- Header Simple -->
            <div class="modal-header-simple">
                <div>
                    <h4 class="modal-title">Gestión de Relaciones</h4>
                    <p class="modal-subtitle">
                        <span class="code-pill">{{ $selectedItem->codigo }}</span>
                    </p>
                </div>
                <button class="modal-close" wire:click="$set('showGestionModal', false)">✕</button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <div class="modal-content-wrapper">
                    <livewire:modulo.gestionar-relaciones :itemId="$selectedItem->id" />
                </div>
            </div>
        </div>
    </div>
@endif
```

#### **JavaScript Global**
```javascript
// Funciones globales para gestión de relaciones
window.confirmarAsignarRelacion = function(relacionId, nombreCompleto) {
    const partes = nombreCompleto.split(' - ');
    const codigo = partes[0] || relacionId;
    const nombre = partes[1] || nombreCompleto;
    
    // Prevenir doble click
    const button = event.target.closest('button');
    if (button) {
        button.disabled = true;
        setTimeout(() => {
            button.disabled = false;
        }, 2000);
    }
    
    showConfirmModal(
        'Asignar relación',
        '¿Desea asignar esta relación al elemento?',
        'success',
        'asignarRelacion',
        relacionId,
        codigo,
        nombre
    );
};

window.confirmarDesasignarRelacion = function(relacionId, nombreCompleto) {
    const partes = nombreCompleto.split(' - ');
    const codigo = partes[0] || relacionId;
    const nombre = partes[1] || nombreCompleto;
    
    // Prevenir doble click
    const button = event.target.closest('button');
    if (button) {
        button.disabled = true;
        setTimeout(() => {
            button.disabled = false;
        }, 2000);
    }
    
    showConfirmModal(
        'Desasignar relación',
        '¿Desea quitar esta relación del elemento?',
        'danger',
        'desasignarRelacion',
        relacionId,
        codigo,
        nombre
    );
};
```

---

### 2. Subcomponente de Gestión

#### **Estructura del Componente**
```php
<?php

namespace App\Livewire\Modulo;

use Livewire\Component;
use App\Models\TuModelo;
use App\Models\RelacionModel;

class GestionarRelaciones extends Component
{
    public $itemId;
    public $item;
    public $asignados = [];
    public $disponibles = [];
    public $loading = true;

    protected $listeners = [
        'confirmAction' => 'handleConfirmAction',
    ];

    public function mount($itemId)
    {
        $this->itemId = $itemId;
        $this->loadData();
    }

    public function loadData()
    {
        $this->item = TuModelo::with(['relaciones'])->find($this->itemId);
        
        if ($this->item) {
            // Obtener relaciones asignadas
            $this->asignados = $this->item->relaciones()->get();
            
            // Obtener relaciones disponibles
            $asignadosIds = $this->asignados->pluck('id');
            $this->disponibles = RelacionModel::whereNotIn('id', $asignadosIds)->get();
        }
        
        $this->loading = false;
    }

    public function handleConfirmAction($data)
    {
        $action = $data['action'];
        $params = $data['params'];

        switch ($action) {
            case 'asignarRelacion':
                $this->asignarRelacion($params);
                break;
            case 'desasignarRelacion':
                $this->desasignarRelacion($params);
                break;
        }
    }

    public function asignarRelacion($relacionId)
    {
        try {
            $this->item->relaciones()->attach($relacionId);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Relación asignada correctamente'
            ]);
            
            $this->dispatch('asignacionesActualizadas');
            $this->loadData();
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al asignar relación'
            ]);
        }
    }

    public function desasignarRelacion($relacionId)
    {
        try {
            $this->item->relaciones()->detach($relacionId);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Relación quitada correctamente'
            ]);
            
            $this->dispatch('asignacionesActualizadas');
            $this->loadData();
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al quitar relación'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.modulo.gestionar-relaciones');
    }
}
```

#### **Vista del Subcomponente**
```blade
<div class="gestion-container">
    @if (!$loading && $item)
        <!-- Información del elemento -->
        <div class="gestion-header">
            <div class="gestion-info">
                <h3 class="gestion-title">{{ $item->nombre }}</h3>
                <div class="gestion-stats">
                    <span class="stat-item">
                        <i class="fas fa-link"></i>
                        {{ $asignados->count() }} asignadas
                    </span>
                    <span class="stat-item">
                        <i class="fas fa-layer-group"></i>
                        {{ $disponibles->count() }} disponibles
                    </span>
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="gestion-content">
            <!-- Asignados -->
            <div class="gestion-section">
                <div class="section-header">
                    <h4 class="section-title">
                        <i class="fas fa-check-circle text-success"></i>
                        Relaciones Asignadas
                    </h4>
                    <span class="section-count">{{ $asignados->count() }}</span>
                </div>
                
                <div class="section-content">
                    @if ($asignados->count() > 0)
                        <div class="modern-table-container">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th class="cell-codigo">Código</th>
                                        <th class="cell-nombre">Nombre</th>
                                        <th class="cell-estado">Estado</th>
                                        <th class="cell-accion">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($asignados as $item)
                                        <tr class="table-row">
                                            <td class="cell-codigo">
                                                <span class="code-badge">{{ $item->codigo }}</span>
                                            </td>
                                            <td class="cell-nombre">{{ $item->nombre }}</td>
                                            <td class="cell-estado">
                                                <span class="status-badge status-assigned">Asignado</span>
                                            </td>
                                            <td class="cell-accion">
                                                <button onclick="confirmarDesasignarRelacion({{ $item->id }}, '{{ $item->codigo }} - {{ $item->nombre }}')" 
                                                        class="btn-action btn-desasignar">
                                                    <i class="fas fa-times"></i>
                                                    <span>Desasignar</span>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-unlink"></i>
                            </div>
                            <div class="empty-text">
                                <span class="empty-title">Sin relaciones asignadas</span>
                                <span class="empty-subtitle">No hay relaciones asignadas a este elemento</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Disponibles -->
            <div class="gestion-section">
                <div class="section-header">
                    <h4 class="section-title">
                        <i class="fas fa-plus-circle text-primary"></i>
                        Relaciones Disponibles
                    </h4>
                    <span class="section-count">{{ $disponibles->count() }}</span>
                </div>
                
                <div class="section-content">
                    @if ($disponibles->count() > 0)
                        <div class="modern-table-container">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th class="cell-codigo">Código</th>
                                        <th class="cell-nombre">Nombre</th>
                                        <th class="cell-estado">Estado</th>
                                        <th class="cell-accion">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($disponibles as $item)
                                        <tr class="table-row">
                                            <td class="cell-codigo">
                                                <span class="code-badge">{{ $item->codigo }}</span>
                                            </td>
                                            <td class="cell-nombre">{{ $item->nombre }}</td>
                                            <td class="cell-estado">
                                                <span class="status-badge status-available">Disponible</span>
                                            </td>
                                            <td class="cell-accion">
                                                <button onclick="confirmarAsignarRelacion({{ $item->id }}, '{{ $item->codigo }} - {{ $item->nombre }}')" 
                                                        class="btn-action btn-asignar">
                                                    <i class="fas fa-plus"></i>
                                                    <span>Asignar</span>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <div class="empty-text">
                                <span class="empty-title">Sin relaciones disponibles</span>
                                <span class="empty-subtitle">Todas las relaciones están asignadas</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="loading-state">
            <div class="loading-content">
                <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                <p>Cargando información...</p>
            </div>
        </div>
    @endif
</div>

@push('styles')
<link href="{{ asset('css/guias-aprendizaje.css') }}" rel="stylesheet">
<style>
/* Estilos específicos para gestión de relaciones */

/* BOTONES DE ACCIÓN ESPECÍFICOS PARA GESTIÓN */
.gestion-container .btn-action {
    width: auto;
    min-width: 34px;
    height: 34px;
    border-radius: 6px;
    border: 2px solid;
    background: transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-size: 0.8rem;
    font-weight: 500;
    line-height: 1;
    padding: 0.625rem 1.25rem;
    margin: 0 2px;
    gap: 0.5rem;
    white-space: nowrap;
    cursor: pointer;
    text-decoration: none;
    position: relative;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.gestion-container .btn-action::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
    z-index: 1;
}

.gestion-container .btn-action:hover::before {
    left: 100%;
}

.gestion-container .btn-action i {
    font-size: 0.75rem;
    transition: transform 0.2s ease;
    position: relative;
    z-index: 2;
}

.gestion-container .btn-action:hover i {
    transform: scale(1.1);
}

.gestion-container .btn-action span {
    position: relative;
    z-index: 2;
}

.gestion-container .btn-action:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
}

.gestion-container .btn-action:disabled::before {
    display: none;
}

.gestion-container .btn-action:disabled:hover i {
    transform: none;
}

/* Botón Asignar - Específico para gestión */
.gestion-container .btn-asignar {
    color: #10b981 !important;
    border-color: #10b981 !important;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(16, 185, 129, 0.02)) !important;
}

.gestion-container .btn-asignar:hover {
    color: #059669 !important;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05)) !important;
    border-color: #059669 !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.1) !important;
}

.gestion-container .btn-asignar:active {
    transform: translateY(0) !important;
    box-shadow: 0 1px 2px rgba(16, 185, 129, 0.1) !important;
}

/* Botón Desasignar - Específico para gestión */
.gestion-container .btn-desasignar {
    color: #ef4444 !important;
    border-color: #ef4444 !important;
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), rgba(239, 68, 68, 0.02)) !important;
}

.gestion-container .btn-desasignar:hover {
    color: #dc2626 !important;
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05)) !important;
    border-color: #dc2626 !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 2px 4px rgba(239, 68, 68, 0.1) !important;
}

.gestion-container .btn-desasignar:active {
    transform: translateY(0) !important;
    box-shadow: 0 1px 2px rgba(239, 68, 68, 0.1) !important;
}

/* Contenedor principal */
.gestion-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

/* Header */
.gestion-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1.5rem;
    border-bottom: 1px solid #dee2e6;
}

.gestion-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.gestion-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #495057;
    margin: 0;
    line-height: 1.2;
}

.gestion-stats {
    display: flex;
    gap: 1rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6c757d;
}

.stat-item i {
    font-size: 0.75rem;
}

/* Contenido principal */
.gestion-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    padding: 1rem;
}

.gestion-section {
    background: #f8f9fa;
    border-radius: 6px;
    overflow: hidden;
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    background: white;
    border-bottom: 1px solid #dee2e6;
}

.section-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #495057;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-count {
    background: #e9ecef;
    color: #495057;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.section-content {
    padding: 1rem;
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    text-align: center;
}

.empty-icon {
    margin-bottom: 1rem;
    color: #6c757d;
}

.empty-icon i {
    font-size: 2rem;
}

.empty-text {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.empty-title {
    font-weight: 600;
    color: #495057;
}

.empty-subtitle {
    font-size: 0.875rem;
    color: #6c757d;
}

/* Loading State */
.loading-state {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 200px;
}

.loading-content {
    text-align: center;
    color: #6c757d;
}

/* Responsive */
@media (max-width: 768px) {
    .gestion-content {
        grid-template-columns: 1fr;
    }
    
    .modern-table {
        font-size: 0.8rem;
    }
    
    .modern-table thead th,
    .modern-table tbody td {
        padding: 0.75rem 0.5rem;
    }
    
    .code-badge {
        padding: 0.25rem 0.5rem;
        font-size: 0.7rem;
    }
    
    .status-badge {
        padding: 0.25rem 0.5rem;
        font-size: 0.7rem;
    }
}
</style>
@endpush
```

---

### 3. Estilos CSS

#### **Botones de Acción Principales**
```css
/* Botón Gestionar (Púrpura medio) */
.btn-gestion {
    color: #8b5cf6;
    border-color: #8b5cf6;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.05), rgba(139, 92, 246, 0.02));
}

.btn-gestion:hover {
    color: #7c3aed;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(139, 92, 246, 0.05));
    border-color: #7c3aed;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(139, 92, 246, 0.1);
}

.btn-gestion:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(139, 92, 246, 0.1);
}
```

---

### 4. Sistema de Modales Globales

Asegúrate de tener el sistema de modales globales implementado en:
- **Archivo:** `resources/views/layouts/partials/global-modals.blade.php`
- **Inclusión:** En el footer principal de la aplicación

#### **JavaScript Protegido**
```javascript
// En global-modals.blade.php
if (typeof modalConfig === 'undefined') {
    let modalConfig = null;
    // ... todo el código del modal
}
```

---

## 🎨 Personalización por Módulo

### Cambios Necesarios

| Elemento | Resultados de Aprendizaje | Tu Módulo |
|----------|---------------------------|-----------|
| **Modelo Principal** | `ResultadosAprendizaje` | `TuModelo` |
| **Modelo Relación** | `Competencia` | `RelacionModel` |
| **Nombre Componente** | `GestionarCompetencias` | `GestionarRelaciones` |
| **Permiso** | `GESTIONAR COMPETENCIAS RESULTADO APRENDIZAJE` | `GESTIONAR RELACIONES MODULO` |
| **Eventos** | `competenciasActualizadas` | `asignacionesActualizadas` |
| **Funciones JS** | `confirmarAsignarCompetencia` | `confirmarAsignarRelacion` |

### Nomenclatura Sugerida
- **Componente:** `Gestionar{Relaciones}`
- **Vista:** `gestionar-{relaciones}.blade.php`
- **CSS:** `{modulo}.css`
- **Eventos:** `{relaciones}Actualizadas`

---

## ✅ Checklist de Implementación

### Backend (PHP/Laravel)
- [ ] Crear componente principal con propiedades del modal
- [ ] Implementar método `openGestionModal()`
- [ ] Crear subcomponente de gestión
- [ ] Implementar lógica de asignar/desasignar
- [ ] Configurar listeners y eventos
- [ ] Verificar permisos y políticas

### Frontend (Blade/Vue)
- [ ] Agregar botón de gestión en la tabla
- [ ] Implementar modal en la vista principal
- [ ] Crear vista del subcomponente
- [ ] Incluir JavaScript global
- [ ] Configurar estilos CSS

### Sistema de Modales
- [ ] Verificar global-modals.blade.php
- [ ] Proteger variables JavaScript
- [ ] Configurar funciones de confirmación
- [ ] Probar flujo completo

---

## 🔧 Troubleshooting Común

### Errores Frecuentes

1. **`modalConfig already declared`**
   - **Solución:** Proteger con `if (typeof modalConfig === 'undefined')`

2. **Botones sin estilo**
   - **Solución:** Verificar CSS específico del componente

3. **Eventos no recibidos**
   - **Solución:** Verificar listeners en componente principal

4. **Modal no se cierra**
   - **Solución:** Implementar `handleCloseModal()`

---

## 📞 Referencias

### Archivos Base
- **Componente ejemplo:** `app/Livewire/ResultadosAprendizaje/ResultadoAprendizajeIndex.php`
- **Subcomponente ejemplo:** `app/Livewire/ResultadosAprendizaje/GestionarCompetencias.php`
- **Vista ejemplo:** `resources/views/livewire/resultados-aprendizaje/gestionar-competencias.blade.php`
- **CSS ejemplo:** `resources/css/resultados-aprendizaje.css`
- **Modales globales:** `resources/views/layouts/partials/global-modals.blade.php`

---

**Esta guía proporciona una base completa y probada para implementar sistemas de gestión de asignación en cualquier módulo del sistema SENA CDATTG, manteniendo consistencia en el diseño y funcionalidad.**