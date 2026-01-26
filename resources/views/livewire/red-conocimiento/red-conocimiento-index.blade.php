<div>
    <!-- Barra de herramientas moderna -->
    <div class="toolbar">
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   class="search-input" 
                   placeholder="Buscar por nombre, regional...">
        </div>
        
        <!-- Filtros adicionales -->
        <div class="filters-container">
            <select wire:model.live="statusFilter" class="filter-select">
                <option value="">Todos los estados</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
            
            <select wire:model.live="regionalFilter" class="filter-select">
                <option value="">Todas las regionales</option>
                @foreach ($this->regionales as $regional)
                    <option value="{{ $regional->id }}">{{ $regional->nombre }}</option>
                @endforeach
            </select>
            
            @if ($search || $statusFilter !== '' || $regionalFilter !== '')
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
        
        @can('CREAR RED CONOCIMIENTO')
            <button wire:click="openCreateModal" class="btn-primary-modern">
                <i class="fas fa-plus"></i>
                Nueva Red
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

    <div wire:loading wire:target="regionalFilter" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por regional...
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
                    <th class="sortable nombre" wire:click="sortBy('nombre')">
                        Nombre
                        @if ($sortField === 'nombre')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} sort-icon"></i>
                        @endif
                    </th>
                    <th class="regional">Regional</th>
                    <th class="sortable programas-count" wire:click="sortBy('created_at')">
                        Programas
                        @if ($sortField === 'created_at')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} sort-icon"></i>
                        @endif
                    </th>
                    <th class="estado">Estado</th>
                    <th class="th-actions sticky-actions">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($redes as $red)
                    <tr>
                        <td class="nombre fw-medium">{{ $red->nombre }}</td>
                        <td class="regional">
                            @if ($red->regional)
                                <span class="badge-modern badge-info">{{ $red->regional->nombre }}</span>
                            @else
                                <span class="badge-modern badge-secondary">Sin asignar</span>
                            @endif
                        </td>
                        <td class="programas-count">
                            <span class="badge-modern badge-primary">{{ $red->programasFormacion->count() }}</span>
                        </td>
                        <td class="estado">
                            <button
                                wire:click="toggleStatus({{ $red->id }})"
                                wire:loading.attr="disabled"
                                class="badge-toggle {{ $red->status ? 'badge-success' : 'badge-danger' }}"
                                title="Cambiar estado">
                                <i class="fas fa-sync-alt me-1"></i>
                                {{ $red->status ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        <td class="td-actions sticky-actions">
                            @can('VER RED CONOCIMIENTO')
                                <button wire:click="openShowModal({{ $red->id }})" 
                                        class="btn-action btn-view" 
                                        title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @endcan
                            @can('EDITAR RED CONOCIMIENTO')
                                <button wire:click="openEditModal({{ $red->id }})" 
                                        class="btn-action btn-edit" 
                                        title="editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @endcan
                            @can('ELIMINAR RED CONOCIMIENTO')
                                <button wire:click="confirmDelete({{ $red->id }})" 
                                        class="btn-action btn-delete" 
                                        title="Eliminar red">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-network-wired"></i>
                                </div>
                                <h3>Aún no hay redes de conocimiento</h3>
                                <p>Comienza creando tu primera red de conocimiento para organizar los programas del SENA.</p>
                                <div class="action-hint">Acción recomendada</div>
                                @can('CREAR RED CONOCIMIENTO')
                                    <button wire:click="openCreateModal" class="btn-primary-modern">
                                        <i class="fas fa-plus"></i>
                                        Crear Primera Red
                                    </button>
                                @endcan
                                <div class="action-hint">Tardarás menos de 2 minutos</div>
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
                    Mostrando {{ $redes->firstItem() ?? 0 }} a {{ $redes->lastItem() ?? 0 }} 
                    de {{ $redes->total() }} resultados
                </div>
                <div class="pagination-links">
                    {{ $redes->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>

    <!-- Modal Confirmación Eliminación -->
    @if ($showDeleteModal && $selectedRed)
        <div class="modal-overlay" wire:click="closeDeleteModal">
            <div class="modal-container" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Red de Conocimiento</h5>
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
                        <h6 class="section-title">Información de la Red</h6>
                        <div style="display: grid; gap: 8px;">
                            <div><strong>Nombre:</strong> {{ $selectedRed->nombre }}</div>
                            <div><strong>Regional:</strong> {{ $selectedRed->regional->nombre ?? 'Sin asignar' }}</div>
                            <div><strong>Programas asociados:</strong> {{ $selectedRed->programasFormacion->count() }}</div>
                        </div>
                    </div>
                    
                    <!-- Mensaje de confirmación -->
                    <div class="modal-section">
                        <p style="margin: 0; color: #6b7280; font-size: 14px;">
                            ¿Está seguro de que desea eliminar esta red de conocimiento?
                        </p>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="modal-footer">
                    <button class="btn-modal btn-secondary" wire:click="closeDeleteModal">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button class="btn-modal btn-danger" wire:click="deleteRed({{ $selectedRed->id }})" 
                            wire:loading.attr="disabled">
                        <i wire:loading.remove wire:target="deleteRed" class="fas fa-trash"></i>
                        <span wire:loading.remove wire:target="deleteRed">Eliminar</span>
                        <i wire:loading wire:target="deleteRed" class="fas fa-spinner fa-spin"></i>
                        <span wire:loading wire:target="deleteRed">Eliminando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Crear/Editar -->
    @if ($showCreateModal || $showEditModal)
        <div class="modal-overlay" wire:click="closeModals">
            <div class="modal-container modal-lg" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $showCreateModal ? 'Crear Red de Conocimiento' : 'Editar Red de Conocimiento' }}
                    </h5>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    @if ($showCreateModal)
                        <livewire:red-conocimiento.red-conocimiento-form />
                    @endif
                    @if ($showEditModal && $selectedRed)
                        <livewire:red-conocimiento.red-conocimiento-form :redId="$selectedRed->id" />
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Ver Detalles -->
    @if ($showShowModal && $selectedRed)
        <div class="modal-overlay" wire:click="closeShowModal">
            <div class="modal-container modal-lg" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">{{ $selectedRed->nombre }}</h5>
                    <button class="modal-close" wire:click="closeShowModal">✕</button>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    <!-- Sección: Información General -->
                    <div class="modal-section">
                        <h6 class="section-title">Información General</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Nombre</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedRed->nombre }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Regional</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedRed->regional->nombre ?? '' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Programas Asociados -->
                    <div class="modal-section">
                        <h6 class="section-title">Programas Asociados ({{ $selectedRed->programasFormacion->count() }})</h6>
                        @if ($selectedRed->programasFormacion->count() > 0)
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                @foreach ($selectedRed->programasFormacion->take(5) as $programa)
                                    <div style="display: flex; align-items: center; gap: 12px; padding: 8px 0; border-bottom: 1px solid #f1f5f9;">
                                        <span style="font-family: monospace; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 600; color: #374151; min-width: 60px; text-align: center;">
                                            {{ $programa->codigo }}
                                        </span>
                                        <span style="font-size: 13px; color: #4b5563; flex: 1;">
                                            {{ $programa->nombre }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                            @if ($selectedRed->programasFormacion->count() > 5)
                                <div style="color: #6b7280; font-size: 12px; margin-top: 8px;">
                                    ... y {{ $selectedRed->programasFormacion->count() - 5 }} más
                                </div>
                            @endif
                        @else
                            <div class="modal-alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <span>No hay programas asociados a esta red.</span>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Sección: Estado de la Red -->
                    <div class="modal-section">
                        <h6 class="section-title">Estado de la Red</h6>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span class="badge-status {{ $selectedRed->status ? 'badge-active' : 'badge-inactive' }}" style="padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; text-transform: uppercase;">
                                <i class="fas fa-{{ $selectedRed->status ? 'check' : 'times' }}" style="margin-right: 4px;"></i>
                                {{ $selectedRed->status ? 'Activo' : 'Inactivo' }}
                            </span>
                            <span style="font-size: 13px; color: #6b7280; font-style: italic;">
                                Esta red {{ $selectedRed->status ? 'puede' : 'no puede' }} ser usada en nuevos programas
                            </span>
                        </div>
                    </div>
                    
                    <!-- Sección: Acciones -->
                    <div class="modal-section">
                        <h6 class="section-title">Acciones</h6>
                        <div style="display: flex; gap: 12px;">
                            @can('EDITAR RED CONOCIMIENTO')
                                <button class="btn-modal btn-primary" wire:click="openEditModal({{ $selectedRed->id }})">
                                    <i class="fas fa-edit"></i>
                                    Editar Red
                                </button>
                            @endcan
                            <button class="btn-modal {{ $selectedRed->status ? 'btn-danger' : 'btn-success' }}" 
                                    wire:click="toggleStatus({{ $selectedRed->id }})" 
                                    wire:loading.attr="disabled">
                                <i wire:loading.remove wire:target="toggleStatus" class="fas fa-sync-alt"></i>
                                <span wire:loading.remove wire:target="toggleStatus">
                                    {{ $selectedRed->status ? 'Desactivar Red' : 'Activar Red' }}
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
                                <div style="font-size: 13px; color: #374151; font-weight: 500;">{{ $selectedRed->userCreated->name ?? 'Sistema' }}</div>
                                <div style="font-size: 12px; color: #6b7280;">{{ $selectedRed->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #9ca3af; text-transform: uppercase; margin-bottom: 4px;">Última edición</div>
                                <div style="font-size: 13px; color: #374151; font-weight: 500;">{{ $selectedRed->userEdited->name ?? 'Sin edición' }}</div>
                                <div style="font-size: 12px; color: #6b7280;">{{ $selectedRed->updated_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="modal-footer">
                    <button class="btn-modal btn-secondary" wire:click="closeShowModal">
                        <i class="fas fa-times"></i>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
