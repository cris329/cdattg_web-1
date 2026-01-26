<div class="vista-guias-aprendizaje">
    <!-- Toast Minimalista ERP -->
    <div class="toast toast-minimal">
        <i class="toast-icon"></i>
        <span class="toast-text"></span>
    </div>

    <!-- Barra de herramientas moderna -->
    <div class="toolbar">
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   class="search-input" 
                   placeholder="Buscar por código, nombre...">
        </div>
        
        <!-- Filtros adicionales -->
        <div class="filters-container">
            <select wire:model.live="statusFilter" class="filter-select">
                <option value="">Todos los estados</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
            
            <select wire:model.live="resultadoFilter" class="filter-select">
                <option value="">Todos los resultados</option>
                @foreach($resultados as $resultado)
                    <option value="{{ $resultado->id }}">{{ Str::limit($resultado->nombre, 25) }}</option>
                @endforeach
            </select>
            
            @if ($search || $statusFilter !== '' || $resultadoFilter !== '')
                <button wire:click="clearFilters" class="btn-clear-filters" title="Limpiar filtros">
                    <i class="fas fa-times"></i>
                </button>
            @endif
        </div>
        
        <div class="results-selector">
            <select wire:model.live="perPage" class="results-select">
                <option value="10">10 resultados</option>
                <option value="15">15 resultados</option>
                <option value="25">25 resultados</option>
                <option value="50">50 resultados</option>
            </select>
        </div>
        
        @can('CREAR GUIA APRENDIZAJE')
            <button wire:click="openCreateModal" class="btn-primary-modern">
                <i class="fas fa-plus"></i>
                Nueva Guía
            </button>
        @endcan
    </div>

    <!-- Indicador de carga -->
    <div wire:loading wire:target="search" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Buscando...
    </div>

    <div wire:loading wire:target="statusFilter" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por estado...
    </div>

    <div wire:loading wire:target="resultadoFilter" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por resultado...
    </div>

    <div wire:loading wire:target="perPage" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Actualizando resultados...
    </div>

    <div wire:loading wire:target="page" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Cargando página...
    </div>

    <!-- Tabla ERP -->
    <div class="table-scroll-wrapper">
        <table class="modern-table">
            <thead>
                <tr>
                    <th class="sortable codigo" wire:click="sortBy('codigo')">
                        Código
                        @if ($sortField === 'codigo')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} sort-icon"></i>
                        @endif
                    </th>
                    <th class="sortable nombre" wire:click="sortBy('nombre')">
                        Nombre
                        @if ($sortField === 'nombre')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} sort-icon"></i>
                        @endif
                    </th>
                    <th class="duracion">Duración</th>
                    <th class="programa">Programa</th>
                    <th class="resultados">Resultados</th>
                    <th class="estado">Estado</th>
                    <th class="th-actions sticky-actions">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @forelse($guias as $guia)
                    <tr>
                        <td class="codigo fw-medium">{{ $guia->codigo }}</td>
                        <td class="nombre">{{ Str::limit($guia->nombre, 50) }}</td>
                        <td class="duracion">
                            <span class="badge-modern badge-info">
                                {{ $guia->duracion_horas }}h / {{ $guia->duracion_meses }}m
                            </span>
                        </td>
                        <td class="programa">
                            @if($guia->programaFormacion)
                                <span class="badge-modern badge-secondary">
                                    {{ $guia->programaFormacion->codigo }}
                                </span>
                            @else
                                <span class="text-muted">Sin programa</span>
                            @endif
                        </td>
                        <td class="resultados text-center">
                            <span class="badge-modern badge-primary">
                                {{ $guia->resultadosAprendizaje->count() }}
                            </span>
                        </td>
                        <td class="estado">
                            <button
                                wire:click="toggleStatus({{ $guia->id }})"
                                wire:loading.attr="disabled"
                                class="badge-toggle {{ $guia->status ? 'badge-success' : 'badge-danger' }}"
                                title="Cambiar estado">
                                <i class="fas fa-sync-alt me-1"></i>
                                {{ $guia->status ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        <td class="td-actions sticky-actions">
                            @can('VER GUIA APRENDIZAJE')
                                <button wire:click="openShowModal({{ $guia->id }})" 
                                        class="btn-action btn-view" 
                                        title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @endcan
                            
                            @can('GESTIONAR RESULTADOS GUIA')
                                <button wire:click="openGestionarResultados({{ $guia->id }})" 
                                        class="btn-action btn-relations" 
                                        title="Gestionar resultados">
                                    <i class="fas fa-link"></i>
                                </button>
                            @endcan
                            
                            @can('EDITAR GUIA APRENDIZAJE')
                                <button wire:click="openEditModal({{ $guia->id }})" 
                                        class="btn-action btn-edit" 
                                        title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @endcan
                            
                            @can('ELIMINAR GUIA APRENDIZAJE')
                                <button wire:click="openDeleteModal({{ $guia->id }})" 
                                        class="btn-action btn-delete" 
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            @if($guias->total() > 0)
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    <h3>No hay resultados en esta página</h3>
                                    <p>Esta página está vacía. Intenta con otra página o ajusta los filtros.</p>
                                </div>
                            @else
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-book-open"></i>
                                    </div>
                                    <h3>Aún no hay guías de aprendizaje</h3>
                                    <p>Comienza creando tu primera guía para organizar el contenido formativo.</p>
                                    @can('CREAR GUIA APRENDIZAJE')
                                        <button wire:click="openCreateModal" class="btn-primary-modern">
                                            <i class="fas fa-plus"></i>
                                            Crear Primera Guía
                                        </button>
                                    @endcan
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="pagination-wrapper">
        <div class="pagination-modern">
            <div class="pagination-info">
                Mostrando {{ $guias->firstItem() ?? 0 }} a {{ $guias->lastItem() ?? 0 }} 
                de {{ $guias->total() }} resultados
            </div>
            <div class="pagination-links">
                {{ $guias->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    <!-- Modal Ver Detalles -->
    @if ($showShowModal && $selectedGuia)
        <div class="modal-overlay" wire:click="$set('showShowModal', false)">
            <div class="modal-container modal-lg" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">{{ $selectedGuia->nombre }}</h5>
                    <button class="modal-close" wire:click="$set('showShowModal', false)">✕</button>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    <!-- Sección: Información General -->
                    <div class="modal-section">
                        <h6 class="section-title">Información General</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Código</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedGuia->codigo }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Nombre</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedGuia->nombre }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Duración</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedGuia->duracion_horas ?? 0 }} horas / {{ $selectedGuia->duracion_meses ?? 0 }} meses
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Programa</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    @if($selectedGuia->programaFormacion)
                                        {{ $selectedGuia->programaFormacion->codigo }} - {{ Str::limit($selectedGuia->programaFormacion->nombre, 30) }}
                                    @else
                                        Sin programa asignado
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Descripción -->
                    <div class="modal-section">
                        <h6 class="section-title">Descripción</h6>
                        <div style="font-size: 14px; color: #374151; line-height: 1.6;">
                            @if($selectedGuia->descripcion)
                                <p>{{ $selectedGuia->descripcion }}</p>
                            @else
                                <p style="color: #6b7280; font-style: italic;">Sin descripción registrada</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Sección: Estado de la Guía -->
                    <div class="modal-section">
                        <h6 class="section-title">Estado de la Guía de Aprendizaje</h6>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span class="badge-modern {{ (int) $selectedGuia->status === 1 ? 'badge-active' : 'badge-inactive' }}" style="padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; text-transform: uppercase;">
                                <i class="fas fa-{{ (int) $selectedGuia->status === 1 ? 'check' : 'times' }}" style="margin-right: 4px;"></i>
                                {{ (int) $selectedGuia->status === 1 ? 'Activa' : 'Inactiva' }}
                            </span>
                            <span style="font-size: 13px; color: #6b7280; font-style: italic;">
                                Esta guía {{ (int) $selectedGuia->status === 1 ? 'está' : 'no está' }} disponible para uso en los programas
                            </span>
                        </div>
                    </div>
                    
                    <!-- Sección: Estadísticas -->
                    <div class="modal-section">
                        <h6 class="section-title">Estadísticas</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;">
                            <div style="text-align: center; padding: 1rem; background: #f8fafc; border-radius: 6px;">
                                <div style="font-size: 1.5rem; font-weight: 600; color: var(--primary);">
                                    {{ $selectedGuia->resultadosAprendizaje->count() }}
                                </div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Resultados de Aprendizaje</div>
                            </div>
                            <div style="text-align: center; padding: 1rem; background: #f0fdf4; border-radius: 6px;">
                                <div style="font-size: 1.5rem; font-weight: 600; color: var(--success);">
                                    {{ $selectedGuia->actividades->count() }}
                                </div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Actividades</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Resultados de Aprendizaje -->
                    <div class="modal-section">
                        <h6 class="section-title">Resultados de Aprendizaje Asociados ({{ $selectedGuia->resultadosAprendizaje->count() }})</h6>
                        @if ($selectedGuia->resultadosAprendizaje->count() > 0)
                            <div style="display: grid; gap: 8px;">
                                @foreach ($selectedGuia->resultadosAprendizaje->take(5) as $resultado)
                                    <div style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: #f8fafc; border-radius: 6px;">
                                        <span style="font-size: 12px; font-weight: 600; color: var(--primary);">{{ $resultado->codigo }}</span>
                                        <span style="font-size: 13px; color: #374151;">{{ Str::limit($resultado->nombre, 60) }}</span>
                                    </div>
                                @endforeach
                            </div>
                            @if ($selectedGuia->resultadosAprendizaje->count() > 5)
                                <div style="font-size: 13px; color: #6b7280; margin-top: 8px; font-style: italic;">
                                    ... y {{ $selectedGuia->resultadosAprendizaje->count() - 5 }} más
                                </div>
                            @endif
                        @else
                            <div style="text-align: center; padding: 2rem; color: #6b7280;">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>No hay resultados de aprendizaje asociados</p>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Sección: Acciones -->
                    <div class="modal-section">
                        <h6 class="section-title">Acciones</h6>
                        <div style="display: flex; gap: 12px;">
                            @can('EDITAR GUIA APRENDIZAJE')
                                <button class="btn-modal btn-primary" wire:click="openEditModal({{ $selectedGuia->id }})">
                                    <i class="fas fa-edit"></i>
                                    Editar Guía
                                </button>
                            @endcan
                            @can('GESTIONAR RESULTADOS GUIA APRENDIZAJE')
                                <button class="btn-modal btn-info" wire:click="openGestionarResultados({{ $selectedGuia->id }})">
                                    <i class="fas fa-tasks"></i>
                                    Gestionar Resultados
                                </button>
                            @endcan
                            <button class="btn-modal {{ (int) $selectedGuia->status === 1 ? 'btn-danger' : 'btn-success' }}" 
                                    wire:click="toggleStatus({{ $selectedGuia->id }})" 
                                    wire:loading.attr="disabled">
                                <i wire:loading.remove wire:target="toggleStatus" class="fas fa-sync-alt"></i>
                                <span wire:loading.remove wire:target="toggleStatus">
                                    {{ (int) $selectedGuia->status === 1 ? 'Desactivar Guía' : 'Activar Guía' }}
                                </span>
                                <i wire:loading wire:target="toggleStatus" class="fas fa-spinner fa-spin"></i>
                                <span wire:loading wire:target="toggleStatus">Procesando...</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Sección: Auditoría -->
                    <div class="modal-section">
                        <h6 class="section-title">Auditoría</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                            <div>
                                <div style="font-size: 12px; color: #9ca3af; text-transform: uppercase; margin-bottom: 4px;">Creado por</div>
                                <div style="font-size: 13px; color: #374151; font-weight: 500;">{{ $selectedGuia->userCreated->name ?? 'Sistema' }}</div>
                                <div style="font-size: 12px; color: #6b7280;">{{ $selectedGuia->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #9ca3af; text-transform: uppercase; margin-bottom: 4px;">Última edición</div>
                                <div style="font-size: 13px; color: #374151; font-weight: 500;">{{ $selectedGuia->userEdited->name ?? 'Sin edición' }}</div>
                                <div style="font-size: 12px; color: #6b7280;">{{ $selectedGuia->updated_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="modal-footer">
                        <button class="btn-modal btn-secondary" wire:click="$set('showShowModal', false)">
                            <i class="fas fa-times"></i>
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Confirmación Eliminación -->
    @if ($showDeleteModal && $selectedGuia)
        <div class="modal-overlay" wire:click="closeDeleteModal">
            <div class="modal-container" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Guía de Aprendizaje</h5>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    <!-- Alerta de advertencia -->
                    <div class="modal-alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Esta acción es permanente y no se puede deshacer.</span>
                    </div>
                    
                    <!-- Información del elemento -->
                    <div class="modal-section">
                        <h6 class="section-title">Información de la Guía de Aprendizaje</h6>
                        <div style="display: grid; gap: 8px;">
                            <div><strong>Código:</strong> {{ $selectedGuia->codigo }}</div>
                            <div><strong>Nombre:</strong> {{ $selectedGuia->nombre }}</div>
                            <div><strong>Descripción:</strong> {{ $selectedGuia->descripcion ?? 'Sin descripción' }}</div>
                            <div><strong>Programa:</strong> 
                                @if($selectedGuia->programaFormacion)
                                    {{ $selectedGuia->programaFormacion->codigo }} - {{ Str::limit($selectedGuia->programaFormacion->nombre, 50) }}
                                @else
                                    Sin programa asignado
                                @endif
                            </div>
                            <div><strong>Duración:</strong> {{ $selectedGuia->duracion_horas ?? 0 }} horas / {{ $selectedGuia->duracion_meses ?? 0 }} meses</div>
                        </div>
                    </div>
                    
                    <!-- Mensaje de confirmación -->
                    <div class="modal-section">
                        <p style="margin: 0; color: #6b7280; font-size: 14px;">
                            ¿Está seguro de que desea eliminar esta guía de aprendizaje?
                        </p>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="modal-footer">
                    <button class="btn-modal btn-secondary" wire:click="closeDeleteModal">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button class="btn-modal btn-danger" wire:click="deleteGuia({{ $selectedGuia->id }})" 
                            wire:loading.attr="disabled">
                        <i wire:loading.remove wire:target="deleteGuia" class="fas fa-trash"></i>
                        <span wire:loading.remove wire:target="deleteGuia">Eliminar</span>
                        <i wire:loading wire:target="deleteGuia" class="fas fa-spinner fa-spin"></i>
                        <span wire:loading wire:target="deleteGuia">Eliminando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Crear/Editar -->
    @if ($showCreateModal || $showEditModal)
        <div class="modal-overlay" wire:click="$set('showCreateModal', false); $set('showEditModal', false)">
            <div class="modal-container" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $showEditModal ? 'Editar Guía de Aprendizaje' : 'Nueva Guía de Aprendizaje' }}
                    </h5>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    @if ($showCreateModal)
                        <livewire:guias-aprendizaje.guia-aprendizaje-form />
                    @endif
                    @if ($showEditModal && $selectedGuia)
                        <livewire:guias-aprendizaje.guia-aprendizaje-form :guiaId="$selectedGuia->id" />
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Gestionar Resultados -->
    @if ($showGestionarResultadosModal && $selectedGuia)
        <div class="modal-overlay" wire:click="$set('showGestionarResultadosModal', false)">
            <div class="modal-container modal-xl" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">Resultados de Aprendizaje - {{ $selectedGuia->nombre }}</h5>
                    <button class="modal-close" wire:click="$set('showGestionarResultadosModal', false)">✕</button>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    <!-- Sección: Información de la Guía -->
                    <div class="modal-section">
                        <h6 class="section-title">Información de la Guía de Aprendizaje</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Código</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedGuia->codigo }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Nombre</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedGuia->nombre }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Duración</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedGuia->duracion_horas ?? 0 }} horas
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Lista de Resultados -->
                    <div class="modal-section">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h6 class="section-title" style="margin-bottom: 0;">Resultados de Aprendizaje Disponibles</h6>
                            <button class="btn-modal btn-primary" wire:click="refreshResultados">
                                <i class="fas fa-sync-alt"></i>
                                Actualizar Lista
                            </button>
                        </div>
                        
                        <!-- Filtro de búsqueda -->
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <input type="text" 
                                   wire:model.live="searchResultados" 
                                   class="form-control-erp" 
                                   placeholder="Buscar resultados por código o descripción...">
                        </div>
                        
                        <!-- Tabla de Resultados Disponibles -->
                        <div class="table-scroll-wrapper" style="max-height: 250px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 6px;">
                            <table class="modern-table" style="margin: 0;">
                                <thead style="position: sticky; top: 0; background: #f9fafb; z-index: 10;">
                                    <tr>
                                        <th style="width: 100px;">
                                            <input type="checkbox" 
                                                   wire:model.live="selectAll" 
                                                   class="form-check-input">
                                        </th>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Estado</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($this->getResultadosDisponibles() as $resultado)
                                        <tr>
                                            <td style="width: 100px; text-align: center; padding-left: 30px;">
                                                <input type="checkbox" 
                                                       wire:model.live="resultadosSeleccionados" 
                                                       value="{{ $resultado->id }}"
                                                       class="form-check-input">
                                            </td>
                                            <td class="fw-medium">{{ $resultado->codigo }}</td>
                                            <td>{{ Str::limit($resultado->nombre, 80) }}</td>
                                            <td>
                                                <span class="badge-modern {{ $resultado->status ? 'badge-active' : 'badge-inactive' }}">
                                                    {{ $resultado->status ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($selectedGuia->resultadosAprendizaje->contains($resultado->id))
                                                    <button wire:click="desasignarResultado({{ $resultado->id }})" 
                                                            class="btn-action btn-danger" 
                                                            title="Desasignar resultado">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                @else
                                                    <button wire:click="asignarResultado({{ $resultado->id }})" 
                                                            class="btn-action btn-success" 
                                                            title="Asignar resultado">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <div style="color: #6b7280;">
                                                    <i class="fas fa-search fa-2x mb-2"></i>
                                                    <p>No se encontraron resultados disponibles</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Sección: Resultados Asignados -->
                    <div class="modal-section">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h6 class="section-title" style="margin-bottom: 0;">Resultados Asignados ({{ $selectedGuia->resultadosAprendizaje->count() }})</h6>
                            @if($selectedGuia->resultadosAprendizaje->count() > 0)
                                <button class="btn-modal btn-warning" wire:click="desasignarTodos" 
                                        wire:loading.attr="disabled">
                                    <i wire:loading.remove wire:target="desasignarTodos" class="fas fa-minus"></i>
                                    <span wire:loading.remove wire:target="desasignarTodos">Desasignar Todos</span>
                                    <i wire:loading wire:target="desasignarTodos" class="fas fa-spinner fa-spin"></i>
                                    <span wire:loading wire:target="desasignarTodos">Procesando...</span>
                                </button>
                            @endif
                        </div>
                        
                        <!-- Tabla de Resultados Asignados -->
                        <div class="table-scroll-wrapper" style="max-height: 200px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 6px;">
                            <table class="modern-table" style="margin: 0;">
                                <thead style="position: sticky; top: 0; background: #f9fafb; z-index: 10;">
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Estado</th>
                                        <th class="th-actions sticky-actions">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($selectedGuia->resultadosAprendizaje as $resultado)
                                        <tr>
                                            <td class="fw-medium">{{ $resultado->codigo }}</td>
                                            <td>{{ Str::limit($resultado->nombre, 80) }}</td>
                                            <td>
                                                <span class="badge-modern {{ $resultado->status ? 'badge-active' : 'badge-inactive' }}">
                                                    {{ $resultado->status ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </td>
                                            <td class="td-actions sticky-actions">
                                                <button wire:click="desasignarResultado({{ $resultado->id }})" 
                                                        class="btn-action btn-danger" 
                                                        title="Desasignar resultado">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <div style="color: #6b7280;">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <p>No hay resultados asignados</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Sección: Estadísticas -->
                    <div class="modal-section">
                        <h6 class="section-title">Estadísticas</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;">
                            <div style="text-align: center; padding: 1rem; background: #f8fafc; border-radius: 6px;">
                                <div style="font-size: 1.5rem; font-weight: 600; color: var(--primary);">
                                    {{ $selectedGuia->resultadosAprendizaje->count() }}
                                </div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Total Resultados</div>
                            </div>
                            <div style="text-align: center; padding: 1rem; background: #f0fdf4; border-radius: 6px;">
                                <div style="font-size: 1.5rem; font-weight: 600; color: var(--success);">
                                    {{ $selectedGuia->resultadosAprendizaje->where('status', 1)->count() }}
                                </div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Activos</div>
                            </div>
                            <div style="text-align: center; padding: 1rem; background: #fef2f2; border-radius: 6px;">
                                <div style="font-size: 1.5rem; font-weight: 600; color: var(--danger);">
                                    {{ $selectedGuia->resultadosAprendizaje->where('status', 0)->count() }}
                                </div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Inactivos</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="modal-footer">
                    <div style="display: flex; justify-content: space-between; width: 100%;">
                        <div>
                            <button class="btn-modal btn-secondary" wire:click="$set('showGestionarResultadosModal', false)">
                                <i class="fas fa-times"></i>
                                Cerrar
                            </button>
                        </div>
                        <div style="display: flex; gap: 0.75rem;">
                            @if(count($this->resultadosSeleccionados ?? []) > 0)
                                <button class="btn-modal btn-success" wire:click="asignarSeleccionados" 
                                        wire:loading.attr="disabled">
                                    <i wire:loading.remove wire:target="asignarSeleccionados" class="fas fa-plus"></i>
                                    <span wire:loading.remove wire:target="asignarSeleccionados">Asignar Seleccionados ({{ count($this->resultadosSeleccionados ?? []) }})</span>
                                    <i wire:loading wire:target="asignarSeleccionados" class="fas fa-spinner fa-spin"></i>
                                    <span wire:loading wire:target="asignarSeleccionados">Procesando...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
