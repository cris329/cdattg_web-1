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
                   placeholder="Buscar por código, nombre...">
        </div>
        
        <!-- Filtros adicionales -->
        <div class="filters-container">
            <select wire:model.live="statusFilter" class="filter-select">
                <option value="">Todos los estados</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
            
            <select wire:model.live="competenciaFilter" class="filter-select">
                <option value="">Todas las competencias</option>
                @foreach($competencias as $competencia)
                    <option value="{{ $competencia->id }}">{{ Str::limit($competencia->nombre, 25) }}</option>
                @endforeach
            </select>
            
            @if ($search || $statusFilter !== '' || $competenciaFilter !== '')
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
        
        @can('CREAR RESULTADO APRENDIZAJE')
            <button wire:click="openCreateModal" class="btn-primary-modern">
                <i class="fas fa-plus"></i>
                Nuevo Resultado
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

    <div wire:loading wire:target="competenciaFilter" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por competencia...
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
                    <th class="descripcion">Descripción</th>
                    <th class="duracion">Duración</th>
                    <th class="competencias">Competencias</th>
                    <th class="guias">Guías</th>
                    <th class="estado">Estado</th>
                    <th class="th-actions sticky-actions">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($resultados as $resultado)
                    <tr>
                        <td class="codigo">
                            <span class="badge-modern badge-primary">{{ $resultado->codigo }}</span>
                        </td>
                        <td class="nombre fw-medium">{{ Str::limit($resultado->nombre, 50) }}</td>
                        <td class="descripcion">{{ Str::limit($resultado->descripcion, 60) }}</td>
                        <td class="duracion">
                            @if($resultado->duracion)
                                <span class="badge-modern badge-info">{{ number_format($resultado->duracion, 0) }}h</span>
                            @else
                                <span class="badge-modern badge-secondary">N/A</span>
                            @endif
                        </td>
                        <td class="competencias">
                            <span class="badge-modern badge-primary">{{ $resultado->competencias->count() }}</span>
                        </td>
                        <td class="guias">
                            <span class="badge-modern badge-warning">{{ $resultado->guiasAprendizaje->count() }}</span>
                        </td>
                        <td class="estado">
                            <button
                                wire:click="toggleStatus({{ $resultado->id }})"
                                wire:loading.attr="disabled"
                                class="badge-toggle {{ (int) $resultado->status === 1 ? 'badge-success' : 'badge-danger' }}"
                                title="Cambiar estado">
                                <i class="fas fa-sync-alt me-1"></i>
                                {{ (int) $resultado->status === 1 ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        <td class="td-actions sticky-actions">
                            @can('VER RESULTADO APRENDIZAJE')
                                <button wire:click="openShowModal({{ $resultado->id }})" 
                                        class="btn-action btn-view" 
                                        title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @endcan
                            @can('GESTIONAR COMPETENCIAS RESULTADO APRENDIZAJE')
                                <button wire:click="openCompetenciasModal({{ $resultado->id }})" 
                                   class="btn-action btn-info" 
                                   title="Gestionar Competencias">
                                    <i class="fas fa-tasks"></i>
                                </button>
                            @endcan
                            @can('EDITAR RESULTADO APRENDIZAJE')
                                <button wire:click="openEditModal({{ $resultado->id }})" 
                                        class="btn-action btn-edit" 
                                        title="Editar resultado">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @endcan
                            @can('ELIMINAR RESULTADO APRENDIZAJE')
                                <button wire:click="confirmDelete({{ $resultado->id }})" 
                                        class="btn-action btn-delete" 
                                        title="Eliminar resultado">
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
                                <p class="text-muted">No se encontraron resultados de aprendizaje</p>
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
                Mostrando {{ $resultados->firstItem() ?? 0 }} a {{ $resultados->lastItem() ?? 0 }} 
                de {{ $resultados->total() }} resultados
            </div>
            <div class="pagination-links">
                {{ $resultados->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    <!-- Modal Crear/Editar -->
    @if ($showCreateModal || $showEditModal)
        <div class="modal-overlay" wire:click="$set('showCreateModal', false); $set('showEditModal', false)">
            <div class="modal-container" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $showEditModal ? 'Editar Resultado de Aprendizaje' : 'Nuevo Resultado de Aprendizaje' }}
                    </h5>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    @if ($showCreateModal)
                        <livewire:resultados-aprendizaje.resultado-aprendizaje-form />
                    @endif
                    @if ($showEditModal && $selectedResultado)
                        <livewire:resultados-aprendizaje.resultado-aprendizaje-form :isEdit="true" :resultadoId="$selectedResultado->id" />
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Ver Detalles -->
    @if ($showShowModal && $selectedResultado)
        <div class="modal-overlay" wire:click="$set('showShowModal', false)">
            <div class="modal-container modal-lg" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">{{ $selectedResultado->nombre }}</h5>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    <!-- Sección: Información General -->
                    <div class="modal-section">
                        <h6 class="section-title">Información General</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Código</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedResultado->codigo }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Nombre</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedResultado->nombre }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Descripción</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedResultado->descripcion }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Duración</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    @if($selectedResultado->duracion)
                                        {{ number_format($selectedResultado->duracion, 0) }} horas
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Estado del Resultado -->
                    <div class="modal-section">
                        <h6 class="section-title">Estado del Resultado de Aprendizaje</h6>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span class="badge-modern {{ (int) $selectedResultado->status === 1 ? 'badge-active' : 'badge-inactive' }}" style="padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; text-transform: uppercase;">
                                <i class="fas fa-{{ (int) $selectedResultado->status === 1 ? 'check' : 'times' }}" style="margin-right: 4px;"></i>
                                {{ (int) $selectedResultado->status === 1 ? 'Activo' : 'Inactivo' }}
                            </span>
                            <span style="font-size: 13px; color: #6b7280; font-style: italic;">
                                Este resultado {{ (int) $selectedResultado->status === 1 ? 'puede' : 'no puede' }} ser usado en nuevas competencias
                            </span>
                        </div>
                    </div>
                    
                    <!-- Sección: Estadísticas -->
                    <div class="modal-section">
                        <h6 class="section-title">Estadísticas</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;">
                            <div style="text-align: center; padding: 1rem; background: #f8fafc; border-radius: 6px;">
                                <div style="font-size: 1.5rem; font-weight: 600; color: var(--primary);">
                                    {{ $selectedResultado->competencias->count() }}
                                </div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Competencias</div>
                            </div>
                            <div style="text-align: center; padding: 1rem; background: #f0fdf4; border-radius: 6px;">
                                <div style="font-size: 1.5rem; font-weight: 600; color: var(--success);">
                                    {{ $selectedResultado->guiasAprendizaje->count() }}
                                </div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Guías de Aprendizaje</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Acciones -->
                    <div class="modal-section">
                        <h6 class="section-title">Acciones</h6>
                        <div style="display: flex; gap: 12px;">
                            @can('EDITAR RESULTADO APRENDIZAJE')
                                <button class="btn-modal btn-primary" wire:click="openEditModal({{ $selectedResultado->id }})">
                                    <i class="fas fa-edit"></i>
                                    Editar Resultado
                                </button>
                            @endcan
                            @can('GESTIONAR COMPETENCIAS RESULTADO APRENDIZAJE')
                                <button class="btn-modal btn-info" wire:click="openCompetenciasModal({{ $selectedResultado->id }})">
                                    <i class="fas fa-tasks"></i>
                                    Gestionar Competencias
                                </button>
                            @endcan
                            <button class="btn-modal {{ (int) $selectedResultado->status === 1 ? 'btn-danger' : 'btn-success' }}" 
                                    wire:click="toggleStatus({{ $selectedResultado->id }})" 
                                    wire:loading.attr="disabled">
                                <i wire:loading.remove wire:target="toggleStatus" class="fas fa-sync-alt"></i>
                                <span wire:loading.remove wire:target="toggleStatus">
                                    {{ (int) $selectedResultado->status === 1 ? 'Desactivar Resultado' : 'Activar Resultado' }}
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
                                <div style="font-size: 13px; color: #374151; font-weight: 500;">{{ $selectedResultado->userCreated->name ?? 'Sistema' }}</div>
                                <div style="font-size: 12px; color: #6b7280;">{{ $selectedResultado->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #9ca3af; text-transform: uppercase; margin-bottom: 4px;">Última edición</div>
                                <div style="font-size: 13px; color: #374151; font-weight: 500;">{{ $selectedResultado->userEdited->name ?? 'Sin edición' }}</div>
                                <div style="font-size: 12px; color: #6b7280;">{{ $selectedResultado->updated_at->format('d/m/Y H:i') }}</div>
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
    @if ($showDeleteModal && $selectedResultado)
        <div class="modal-overlay" wire:click="closeDeleteModal">
            <div class="modal-container" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Resultado de Aprendizaje</h5>
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
                        <h6 class="section-title">Información del Resultado de Aprendizaje</h6>
                        <div style="display: grid; gap: 8px;">
                            <div><strong>Código:</strong> {{ $selectedResultado->codigo }}</div>
                            <div><strong>Nombre:</strong> {{ $selectedResultado->nombre }}</div>
                            <div><strong>Descripción:</strong> {{ $selectedResultado->descripcion }}</div>
                        </div>
                    </div>
                    
                    <!-- Mensaje de confirmación -->
                    <div class="modal-section">
                        <p style="margin: 0; color: #6b7280; font-size: 14px;">
                            ¿Está seguro de que desea eliminar este resultado de aprendizaje?
                        </p>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="modal-footer">
                    <button class="btn-modal btn-secondary" wire:click="closeDeleteModal">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button class="btn-modal btn-danger" wire:click="deleteResultado({{ $selectedResultado->id }})" 
                            wire:loading.attr="disabled">
                        <i wire:loading.remove wire:target="deleteResultado" class="fas fa-trash"></i>
                        <span wire:loading.remove wire:target="deleteResultado">Eliminar</span>
                        <i wire:loading wire:target="deleteResultado" class="fas fa-spinner fa-spin"></i>
                        <span wire:loading wire:target="deleteResultado">Eliminando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Gestión de Competencias -->
    @if ($showCompetenciasModal && $selectedResultado)
        <div class="modal-overlay" wire:click="$set('showCompetenciasModal', false)">
            <div class="modal-container modal-xl" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">Competencias - {{ $selectedResultado->nombre }}</h5>
                    <button class="modal-close" wire:click="$set('showCompetenciasModal', false)">✕</button>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    <!-- Sección: Información del Resultado -->
                    <div class="modal-section">
                        <h6 class="section-title">Información del Resultado de Aprendizaje</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Código</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedResultado->codigo }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Nombre</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedResultado->nombre }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Duración</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    @if($selectedResultado->duracion)
                                        {{ number_format($selectedResultado->duracion, 0) }} horas
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Lista de Competencias -->
                    <div class="modal-section">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h6 class="section-title" style="margin-bottom: 0;">Competencias Disponibles</h6>
                            <button class="btn-modal btn-primary" wire:click="refreshCompetencias">
                                <i class="fas fa-sync-alt"></i>
                                Actualizar Lista
                            </button>
                        </div>
                        
                        <!-- Filtro de búsqueda -->
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <input type="text" 
                                   wire:model.live="searchCompetencias" 
                                   class="form-control-erp" 
                                   placeholder="Buscar competencias por código o descripción...">
                        </div>
                        
                        <!-- Tabla de Competencias Disponibles -->
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
                                    @forelse ($this->getCompetenciasDisponibles() as $competencia)
                                        <tr>
                                            <td style="width: 100px; text-align: center; padding-left: 30px;">
                                                <input type="checkbox" 
                                                       wire:model.live="competenciasSeleccionadas" 
                                                       value="{{ $competencia->id }}"
                                                       class="form-check-input">
                                            </td>
                                            <td class="fw-medium">{{ $competencia->codigo }}</td>
                                            <td>{{ Str::limit($competencia->nombre, 80) }}</td>
                                            <td>
                                                <span class="badge-modern {{ $competencia->status ? 'badge-active' : 'badge-inactive' }}">
                                                    {{ $competencia->status ? 'Activa' : 'Inactiva' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($selectedResultado->competencias->contains($competencia->id))
                                                    <button wire:click="desasignarCompetencia({{ $competencia->id }})" 
                                                            class="btn-action btn-danger" 
                                                            title="Desasignar competencia">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                @else
                                                    <button wire:click="asignarCompetencia({{ $competencia->id }})" 
                                                            class="btn-action btn-success" 
                                                            title="Asignar competencia">
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
                                                    <p>No se encontraron competencias disponibles</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Sección: Competencias Asignadas -->
                    <div class="modal-section">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h6 class="section-title" style="margin-bottom: 0;">Competencias Asignadas ({{ $selectedResultado->competencias->count() }})</h6>
                            @if($selectedResultado->competencias->count() > 0)
                                <button class="btn-modal btn-warning" wire:click="desasignarTodas" 
                                        wire:loading.attr="disabled">
                                    <i wire:loading.remove wire:target="desasignarTodas" class="fas fa-minus"></i>
                                    <span wire:loading.remove wire:target="desasignarTodas">Desasignar Todas</span>
                                    <i wire:loading wire:target="desasignarTodas" class="fas fa-spinner fa-spin"></i>
                                    <span wire:loading wire:target="desasignarTodas">Procesando...</span>
                                </button>
                            @endif
                        </div>
                        
                        <!-- Tabla de Competencias Asignadas -->
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
                                    @forelse ($selectedResultado->competencias as $competencia)
                                        <tr>
                                            <td class="fw-medium">{{ $competencia->codigo }}</td>
                                            <td>{{ Str::limit($competencia->nombre, 80) }}</td>
                                            <td>
                                                <span class="badge-modern {{ $competencia->status ? 'badge-active' : 'badge-inactive' }}">
                                                    {{ $competencia->status ? 'Activa' : 'Inactiva' }}
                                                </span>
                                            </td>
                                            <td class="td-actions sticky-actions">
                                                <button wire:click="desasignarCompetencia({{ $competencia->id }})" 
                                                        class="btn-action btn-danger" 
                                                        title="Desasignar competencia">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <div style="color: #6b7280;">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <p>No hay competencias asignadas</p>
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
                                    {{ $selectedResultado->competencias->count() }}
                                </div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Total Competencias</div>
                            </div>
                            <div style="text-align: center; padding: 1rem; background: #f0fdf4; border-radius: 6px;">
                                <div style="font-size: 1.5rem; font-weight: 600; color: var(--success);">
                                    {{ $selectedResultado->competencias->where('status', 1)->count() }}
                                </div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Activas</div>
                            </div>
                            <div style="text-align: center; padding: 1rem; background: #fef2f2; border-radius: 6px;">
                                <div style="font-size: 1.5rem; font-weight: 600; color: var(--danger);">
                                    {{ $selectedResultado->competencias->where('status', 0)->count() }}
                                </div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Inactivas</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="modal-footer">
                    <div style="display: flex; justify-content: space-between; width: 100%;">
                        <div>
                            <button class="btn-modal btn-secondary" wire:click="$set('showCompetenciasModal', false)">
                                <i class="fas fa-times"></i>
                                Cerrar
                            </button>
                        </div>
                        <div style="display: flex; gap: 0.75rem;">
                            @if(count($this->competenciasSeleccionadas ?? []) > 0)
                                <button class="btn-modal btn-success" wire:click="asignarSeleccionadas" 
                                        wire:loading.attr="disabled">
                                    <i wire:loading.remove wire:target="asignarSeleccionadas" class="fas fa-plus"></i>
                                    <span wire:loading.remove wire:target="asignarSeleccionadas">Asignar Seleccionadas ({{ count($this->competenciasSeleccionadas ?? []) }})</span>
                                    <i wire:loading wire:target="asignarSeleccionadas" class="fas fa-spinner fa-spin"></i>
                                    <span wire:loading wire:target="asignarSeleccionadas">Procesando...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Scripts para gestión de competencias - Cargados en el nivel principal -->
<script>
// Funciones globales para gestión de competencias
window.confirmarAsignarCompetencia = function(competenciaId, nombreCompleto) {
    const partes = nombreCompleto.split(' - ');
    const codigo = partes[0] || competenciaId;
    const nombre = partes[1] || nombreCompleto;
    
    // Deshabilitar temporalmente para evitar doble click
    const button = event.target.closest('button');
    if (button) {
        button.disabled = true;
        setTimeout(() => {
            button.disabled = false;
        }, 2000);
    }
    
    showConfirmModal(
        'Asignar competencia',
        '¿Desea asignar esta competencia al resultado de aprendizaje?',
        'success',
        'asignarCompetencia',
        competenciaId,
        codigo,
        nombre
    );
};

window.confirmarDesasignarCompetencia = function(competenciaId, nombreCompleto) {
    const partes = nombreCompleto.split(' - ');
    const codigo = partes[0] || competenciaId;
    const nombre = partes[1] || nombreCompleto;
    
    // Deshabilitar temporalmente para evitar doble click
    const button = event.target.closest('button');
    if (button) {
        button.disabled = true;
        setTimeout(() => {
            button.disabled = false;
        }, 2000);
    }
    
    showConfirmModal(
        'Desasignar competencia',
        '¿Desea quitar esta competencia del resultado de aprendizaje?',
        'danger',
        'desasignarCompetencia',
        competenciaId,
        codigo,
        nombre
    );
};
</script>
