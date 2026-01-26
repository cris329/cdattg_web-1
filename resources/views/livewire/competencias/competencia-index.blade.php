<div>
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
                   placeholder="Buscar por código, nombre, descripción...">
        </div>
        
        <!-- Filtros adicionales -->
        <div class="filters-container">
            <select wire:model.live="statusFilter" class="filter-select">
                <option value="">Todos los estados</option>
                <option value="1">Activas</option>
                <option value="0">Inactivas</option>
            </select>
            
            <select wire:model.live="vigenciaFilter" class="filter-select">
                <option value="">Todas las vigencias</option>
                <option value="vigentes">Vigentes</option>
                <option value="no_vigentes">No vigentes</option>
            </select>
            
            @if ($search || $statusFilter !== '' || $vigenciaFilter !== '')
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
        
        @can('CREAR COMPETENCIA')
            <button wire:click="openCreateModal" class="btn-primary-modern">
                <i class="fas fa-plus"></i>
                Nueva Competencia
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

    <div wire:loading wire:target="vigenciaFilter" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por vigencia...
    </div>

    <div wire:loading wire:target="perPage" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Actualizando resultados...
    </div>

    <!-- Tabla ERP - Solución Definitiva (1 sola tabla) -->
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
                    <th class="descripcion">Descripción</th>
                    <th class="duracion">Duración</th>
                    <th class="vigencia">Vigencia</th>
                    <th class="programas">Programas</th>
                    <th class="estado">Estado</th>
                    <th class="th-actions sticky-actions">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($competencias as $competencia)
                    <tr>
                        <td class="codigo">
                            <span class="badge-modern badge-primary">{{ $competencia->codigo }}</span>
                        </td>
                        <td class="nombre fw-medium">{{ Str::limit($competencia->nombre, 50) }}</td>
                        <td class="descripcion">{{ Str::limit($competencia->descripcion, 60) }}</td>
                        <td class="duracion">
                            @if($competencia->duracion)
                                <span class="badge-modern badge-info">{{ number_format($competencia->duracion, 0) }}h</span>
                            @else
                                <span class="badge-modern badge-secondary">N/A</span>
                            @endif
                        </td>
                        <td class="vigencia">
                            @if($competencia->estaVigente())
                                <span class="badge-modern badge-success">Vigente</span>
                            @else
                                <span class="badge-modern badge-warning">No vigente</span>
                            @endif
                        </td>
                        <td class="programas">
                            <span class="badge-modern badge-primary">{{ $competencia->programasFormacion->count() }}</span>
                        </td>
                        <td class="estado">
                            <button
                                wire:click="toggleStatus({{ $competencia->id }})"
                                wire:loading.attr="disabled"
                                class="badge-toggle {{ (int) $competencia->status === 1 ? 'badge-success' : 'badge-danger' }}"
                                title="Cambiar estado">
                                <i class="fas fa-sync-alt me-1"></i>
                                {{ (int) $competencia->status === 1 ? 'Activa' : 'Inactiva' }}
                            </button>
                        </td>
                        <td class="td-actions sticky-actions">
                            @can('VER COMPETENCIA')
                                <button wire:click="openShowModal({{ $competencia->id }})" 
                                        class="btn-action btn-view" 
                                        title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @endcan
                            @can('GESTIONAR RESULTADOS COMPETENCIA')
                                <button wire:click="openResultadosModal({{ $competencia->id }})" 
                                   class="btn-action btn-info" 
                                   title="Gestionar Resultados">
                                    <i class="fas fa-tasks"></i>
                                </button>
                            @endcan
                            @can('EDITAR COMPETENCIA')
                                <button wire:click="openEditModal({{ $competencia->id }})" 
                                        class="btn-action btn-edit" 
                                        title="Editar competencia">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @endcan
                            @can('ELIMINAR COMPETENCIA')
                                <button wire:click="confirmDelete({{ $competencia->id }})" 
                                        class="btn-action btn-delete" 
                                        title="Eliminar competencia">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="empty-state">
                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No se encontraron competencias</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
        
        <!-- Paginación (siempre visible) -->
        <div class="pagination-wrapper">
            <div class="pagination-modern">
                <div class="pagination-info">
                    Mostrando {{ $competencias->firstItem() ?? 0 }} a {{ $competencias->lastItem() ?? 0 }} 
                    de {{ $competencias->total() }} resultados
                </div>
                <div class="pagination-links">
                    {{ $competencias->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>

    <!-- Modal Crear/Editar -->
    @if ($showCreateModal || $showEditModal)
        <div class="modal-overlay" wire:click="closeCreateEditModals">
            <div class="modal-container modal-lg" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $showCreateModal ? 'Crear Competencia' : 'Editar Competencia' }}
                    </h5>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    @if ($showCreateModal)
                        <livewire:competencias.competencia-form />
                    @endif
                    @if ($showEditModal && $selectedCompetencia)
                        <livewire:competencias.competencia-form :competenciaId="$selectedCompetencia->id" />
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Ver Detalles -->
    @if ($showShowModal && $selectedCompetencia)
        <div class="modal-overlay" wire:click="$set('showShowModal', false)">
            <div class="modal-container modal-lg" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">{{ $selectedCompetencia->nombre }}</h5>
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
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedCompetencia->codigo }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Nombre</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedCompetencia->nombre }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Descripción</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedCompetencia->descripcion }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Duración</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedCompetencia->duracionFormateada }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Estado del Programa -->
                    <div class="modal-section">
                        <h6 class="section-title">Estado de la Competencia</h6>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span class="badge-status {{ (int) $selectedCompetencia->status === 1 ? 'badge-active' : 'badge-inactive' }}" style="padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; text-transform: uppercase;">
                                <i class="fas fa-{{ (int) $selectedCompetencia->status === 1 ? 'check' : 'times' }}" style="margin-right: 4px;"></i>
                                {{ (int) $selectedCompetencia->status === 1 ? 'Activa' : 'Inactiva' }}
                            </span>
                            <span style="font-size: 13px; color: #6b7280; font-style: italic;">
                                Esta competencia {{ (int) $selectedCompetencia->status === 1 ? 'puede' : 'no puede' }} ser usada en nuevos programas
                            </span>
                        </div>
                    </div>
                    
                    <!-- Sección: Acciones -->
                    <div class="modal-section">
                        <h6 class="section-title">Acciones</h6>
                        <div style="display: flex; gap: 12px;">
                            @can('EDITAR COMPETENCIA')
                                <button class="btn-modal btn-primary" wire:click="openEditModal({{ $selectedCompetencia->id }})">
                                    <i class="fas fa-edit"></i>
                                    Editar Competencia
                                </button>
                            @endcan
                            <button class="btn-modal {{ (int) $selectedCompetencia->status === 1 ? 'btn-danger' : 'btn-success' }}" 
                                    wire:click="toggleStatus({{ $selectedCompetencia->id }})" 
                                    wire:loading.attr="disabled">
                                <i wire:loading.remove wire:target="toggleStatus" class="fas fa-sync-alt"></i>
                                <span wire:loading.remove wire:target="toggleStatus">
                                    {{ (int) $selectedCompetencia->status === 1 ? 'Desactivar Competencia' : 'Activar Competencia' }}
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
                                <div style="font-size: 13px; color: #374151; font-weight: 500;">{{ $selectedCompetencia->userCreated->name ?? 'Sistema' }}</div>
                                <div style="font-size: 12px; color: #6b7280;">{{ $selectedCompetencia->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #9ca3af; text-transform: uppercase; margin-bottom: 4px;">Última edición</div>
                                <div style="font-size: 13px; color: #374151; font-weight: 500;">{{ $selectedCompetencia->userEdited->name ?? 'Sin edición' }}</div>
                                <div style="font-size: 12px; color: #6b7280;">{{ $selectedCompetencia->updated_at->format('d/m/Y H:i') }}</div>
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
    @if ($showDeleteModal && $selectedCompetencia)
        <div class="modal-overlay" wire:click="closeDeleteModal">
            <div class="modal-container" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Competencia</h5>
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
                        <h6 class="section-title">Información de la Competencia</h6>
                        <div style="display: grid; gap: 8px;">
                            <div><strong>Código:</strong> {{ $selectedCompetencia->codigo }}</div>
                            <div><strong>Nombre:</strong> {{ $selectedCompetencia->nombre }}</div>
                            <div><strong>Descripción:</strong> {{ $selectedCompetencia->descripcion }}</div>
                        </div>
                    </div>
                    
                    <!-- Mensaje de confirmación -->
                    <div class="modal-section">
                        <p style="margin: 0; color: #6b7280; font-size: 14px;">
                            ¿Está seguro de que desea eliminar esta competencia?
                        </p>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="modal-footer">
                    <button class="btn-modal btn-secondary" wire:click="closeDeleteModal">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button class="btn-modal btn-danger" wire:click="deleteCompetencia({{ $selectedCompetencia->id }})" 
                            wire:loading.attr="disabled">
                        <i wire:loading.remove wire:target="deleteCompetencia" class="fas fa-trash"></i>
                        <span wire:loading.remove wire:target="deleteCompetencia">Eliminar</span>
                        <i wire:loading wire:target="deleteCompetencia" class="fas fa-spinner fa-spin"></i>
                        <span wire:loading wire:target="deleteCompetencia">Eliminando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Gestionar Resultados de Aprendizaje -->
    @if ($showResultadosModal && $selectedCompetencia)
        <div class="modal-overlay" wire:click="$set('showResultadosModal', false)">
            <div class="modal-container modal-xl" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">Resultados de Aprendizaje - {{ $selectedCompetencia->nombre }}</h5>
                    <button class="modal-close" wire:click="$set('showResultadosModal', false)">✕</button>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    <!-- Sección: Información de la Competencia -->
                    <div class="modal-section">
                        <h6 class="section-title">Información de la Competencia</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Código</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedCompetencia->codigo }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Nombre</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedCompetencia->nombre }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Lista de Resultados de Aprendizaje -->
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
                                        <th>Descripción</th>
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
                                                @if($selectedCompetencia->resultadosAprendizaje->contains($resultado->id))
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
                            <h6 class="section-title" style="margin-bottom: 0;">Resultados Asignados ({{ $selectedCompetencia->resultadosAprendizaje->count() }})</h6>
                            @if($selectedCompetencia->resultadosAprendizaje->count() > 0)
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
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th class="th-actions sticky-actions">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($selectedCompetencia->resultadosAprendizaje as $resultado)
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
                                    {{ $selectedCompetencia->resultadosAprendizaje->count() }}
                                </div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Total Resultados</div>
                            </div>
                            <div style="text-align: center; padding: 1rem; background: #f0fdf4; border-radius: 6px;">
                                <div style="font-size: 1.5rem; font-weight: 600; color: var(--success);">
                                    {{ $selectedCompetencia->resultadosAprendizaje->where('status', 1)->count() }}
                                </div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Activos</div>
                            </div>
                            <div style="text-align: center; padding: 1rem; background: #fef2f2; border-radius: 6px;">
                                <div style="font-size: 1.5rem; font-weight: 600; color: var(--danger);">
                                    {{ $selectedCompetencia->resultadosAprendizaje->where('status', 0)->count() }}
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
                            <button class="btn-modal btn-secondary" wire:click="$set('showResultadosModal', false)">
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
