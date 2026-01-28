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
                   placeholder="Buscar por nombre, documento, ficha...">
        </div>
        
        <!-- Filtros adicionales -->
        <div class="filters-container">
            <select wire:model.live="statusFilter" class="filter-select">
                <option value="">Todos los estados</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
            
            <select wire:model.live="fichaFilter" class="filter-select">
                <option value="">Todas las fichas</option>
                @foreach($fichas as $ficha)
                    <option value="{{ $ficha->id }}">{{ $ficha->ficha }} - {{ $ficha->programaFormacion->nombre ?? 'Sin programa' }}</option>
                @endforeach
            </select>
            
            <select wire:model.live="programaFilter" class="filter-select">
                <option value="">Todos los programas</option>
                @foreach($programas as $programa)
                    <option value="{{ $programa->id }}">{{ $programa->nombre }}</option>
                @endforeach
            </select>
            
            <select wire:model.live="regionalFilter" class="filter-select">
                <option value="">Todas las regionales</option>
                @foreach($regionales as $regional)
                    <option value="{{ $regional->id }}">{{ $regional->nombre }}</option>
                @endforeach
            </select>
            
            @if ($search || $statusFilter !== '' || $fichaFilter !== '' || $programaFilter !== '' || $regionalFilter !== '')
                <button wire:click="clearFilters" class="btn-clear-filters">
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
        
        <div class="actions-container">
    <button wire:click="openCreateModal" class="btn-primary-modern">
        <i class="fas fa-plus"></i>
        Nuevo Aprendiz
    </button>
</div>
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

    <div wire:loading wire:target="fichaFilter" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por ficha...
    </div>

    <div wire:loading wire:target="programaFilter" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por programa...
    </div>

    <div wire:loading wire:target="regionalFilter" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por regional...
    </div>

    <div wire:loading wire:target="perPage" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Actualizando resultados...
    </div>

    <!-- Tabla ERP -->
    <div class="table-scroll-wrapper">
        <table class="modern-table">
            <thead>
                <tr>
                    <th class="sortable codigo" wire:click="sortBy('created_at')">
                        #
                        @if ($sortField === 'created_at')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} sort-icon"></i>
                        @endif
                    </th>
                    <th class="sortable nombre" wire:click="sortBy('nombre')">
                        Nombre Completo
                        @if ($sortField === 'nombre')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} sort-icon"></i>
                        @endif
                    </th>
                    <th class="documento">Documento</th>
                    <th class="ficha">Ficha</th>
                    <th class="programa">Programa</th>
                    <th class="regional">Regional</th>
                    <th class="estado">Estado</th>
                    <th class="th-actions sticky-actions">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($aprendices as $aprendiz)
                    <tr>
                        <td class="codigo">
                            <span class="badge-modern badge-primary">{{ ($aprendices->currentPage() - 1) * $aprendices->perPage() + $loop->iteration }}</span>
                        </td>
                        <td class="nombre fw-medium">
                            @if($aprendiz->persona)
                                {{ $aprendiz->persona->primer_nombre }} 
                                {{ $aprendiz->persona->segundo_nombre ?? '' }}
                                {{ $aprendiz->persona->primer_apellido }}
                                {{ $aprendiz->persona->segundo_apellido ?? '' }}
                            @else
                                <span class="text-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Sin datos de persona
                                </span>
                            @endif
                        </td>
                        <td class="documento">
                            {{ $aprendiz->persona?->numero_documento ?? 'N/A' }}
                        </td>
                        <td class="ficha">
                            @if($aprendiz->fichaCaracterizacion)
                                <span class="badge-modern badge-info">{{ $aprendiz->fichaCaracterizacion->ficha }}</span>
                            @else
                                <span class="badge-modern badge-secondary">Sin ficha</span>
                            @endif
                        </td>
                        <td class="programa">
                            @if($aprendiz->fichaCaracterizacion?->programaFormacion)
                                <span class="badge-modern badge-success">{{ $aprendiz->fichaCaracterizacion->programaFormacion->nombre }}</span>
                            @else
                                <span class="badge-modern badge-warning">Sin programa</span>
                            @endif
                        </td>
                        <td class="regional">
                            @if($aprendiz->fichaCaracterizacion?->sede?->regional)
                                <span class="badge-modern badge-primary">{{ $aprendiz->fichaCaracterizacion->sede->regional->nombre }}</span>
                            @else
                                <span class="badge-modern badge-secondary">Sin regional</span>
                            @endif
                        </td>
                        <td class="estado">
                            <button wire:click="toggleStatus({{ $aprendiz->id }})"
                                wire:loading.attr="disabled"
                                class="badge-toggle {{ (int) $aprendiz->estado === 1 ? 'badge-success' : 'badge-danger' }}">
                                <i class="fas fa-sync-alt me-1"></i>
                                {{ (int) $aprendiz->estado === 1 ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        <td class="td-actions sticky-actions">
    <button wire:click="openShowModal({{ $aprendiz->id }})" 
            class="btn-action btn-view">
        <i class="fas fa-eye"></i>
    </button>
    
    <button wire:click="openEditModal({{ $aprendiz->id }})" 
            class="btn-action btn-edit">
        <i class="fas fa-edit"></i>
    </button>
    
    <button wire:click="openDeleteModal({{ $aprendiz->id }})" 
            class="btn-action btn-delete">
        <i class="fas fa-trash"></i>
    </button>
</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-user-graduate"></i>
                                <h3>No hay aprendices registrados</h3>
                                <p>
                                    @if($search || $statusFilter !== '' || $fichaFilter !== '' || $programaFilter !== '' || $regionalFilter !== '')
                                        No se encontraron resultados con los filtros aplicados.
                                    @else
                                        Comienza registrando un nuevo aprendiz en el sistema.
                                    @endif
                                </p>
                                @if(!$search && $statusFilter === '' && $fichaFilter === '' && $programaFilter === '' && $regionalFilter === '')
                                    @can('CREAR APRENDIZ')
                                        <button wire:click="openCreateModal" class="btn-primary">
                                            <i class="fas fa-plus"></i>
                                            Nuevo Aprendiz
                                        </button>
                                    @endcan
                                @endif
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
                    Mostrando {{ $aprendices->firstItem() ?? 0 }} a {{ $aprendices->lastItem() ?? 0 }} 
                    de {{ $aprendices->total() }} resultados
                </div>
                <div class="pagination-links">
                    {{ $aprendices->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>

    <!-- Modal Crear/Editar -->
    @if ($showCreateModal || $showEditModal)
        <div class="modal-overlay" wire:click="closeCreateEditModals">
            <div class="modal-container modal-xl" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $showCreateModal ? 'Crear Aprendiz' : 'Editar Aprendiz' }}
                    </h5>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    <livewire:aprendices.aprendiz-form 
                        :aprendiz="$selectedAprendiz" 
                        :is-edit="$showEditModal" />
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Ver Detalles -->
    @if ($showShowModal && $selectedAprendiz)
        <div class="modal-overlay" wire:click="$set('showShowModal', false)">
            <div class="modal-container modal-lg" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        @if($selectedAprendiz->persona)
                            {{ $selectedAprendiz->persona->primer_nombre }} {{ $selectedAprendiz->persona->primer_apellido }}
                        @else
                            Aprendiz sin datos
                        @endif
                    </h5>
                    <button class="modal-close" wire:click="$set('showShowModal', false)">✕</button>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    <!-- Sección: Información Personal -->
                    <div class="modal-section">
                        <h6 class="section-title">Información Personal</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Nombre Completo</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    @if($selectedAprendiz->persona)
                                        {{ $selectedAprendiz->persona->primer_nombre }} 
                                        {{ $selectedAprendiz->persona->segundo_nombre ?? '' }}
                                        {{ $selectedAprendiz->persona->primer_apellido }}
                                        {{ $selectedAprendiz->persona->segundo_apellido ?? '' }}
                                    @else
                                        <span class="badge-modern badge-secondary">Sin datos</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Documento</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedAprendiz->persona?->numero_documento ?? 'N/A' }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Tipo Documento</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedAprendiz->persona?->tipoDocumento?->nombre ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Información de Formación -->
                    <div class="modal-section">
                        <h6 class="section-title">Información de Formación</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Ficha</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    @if($selectedAprendiz->fichaCaracterizacion)
                                        <span class="badge-modern badge-info">{{ $selectedAprendiz->fichaCaracterizacion->ficha }}</span>
                                    @else
                                        <span class="badge-modern badge-secondary">Sin ficha</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Programa de Formación</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedAprendiz->fichaCaracterizacion?->programaFormacion?->nombre ?? 'N/A' }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Red de Conocimiento</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedAprendiz->fichaCaracterizacion?->programaFormacion?->redConocimiento?->nombre ?? 'N/A' }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Regional</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    @if($selectedAprendiz->fichaCaracterizacion?->programaFormacion?->redConocimiento?->regional)
                                        {{ $selectedAprendiz->fichaCaracterizacion->programaFormacion->redConocimiento->regional->nombre }}
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Sede</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedAprendiz->fichaCaracterizacion?->sede?->sede ?? 'N/A' }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Ambiente</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    @if($selectedAprendiz->fichaCaracterizacion?->ambiente_id)
                                        {{ $selectedAprendiz->fichaCaracterizacion?->ambiente?->title ?? 'N/A' }}
                                    @else
                                        <span style="color: #9ca3af; font-style: italic;">Sin ambiente asignado</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Estado del Aprendiz -->
                    <div class="modal-section">
                        <h6 class="section-title">Estado del Aprendiz</h6>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span class="badge-modern {{ $selectedAprendiz->estado ? 'badge-success' : 'badge-danger' }}">
                                <i class="fas fa-{{ $selectedAprendiz->estado ? 'check' : 'times' }}"></i>
                                {{ $selectedAprendiz->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                            <span style="font-size: 13px; color: #6b7280; font-style: italic;">
                                Este aprendiz {{ $selectedAprendiz->estado ? 'puede' : 'no puede' }} acceder al sistema
                            </span>
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
    @endif

    <!-- Modal Confirmación Eliminación -->
    @if ($showDeleteModal && $selectedAprendiz)
        <div class="modal-overlay" wire:click="closeDeleteModal">
            <div class="modal-container" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Aprendiz</h5>
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
                        <h6 class="section-title">Información del Aprendiz</h6>
                        <div style="display: grid; gap: 8px;">
                            <div><strong>Nombre:</strong> 
                                @if($selectedAprendiz->persona)
                                    {{ $selectedAprendiz->persona->primer_nombre }} {{ $selectedAprendiz->persona->primer_apellido }}
                                @else
                                    Sin datos de persona
                                @endif
                            </div>
                            <div><strong>Documento:</strong> {{ $selectedAprendiz->persona?->numero_documento ?? 'N/A' }}</div>
                            <div><strong>Ficha:</strong> {{ $selectedAprendiz->fichaCaracterizacion?->ficha ?? 'Sin ficha' }}</div>
                        </div>
                    </div>
                    
                    <!-- Mensaje de confirmación -->
                    <div class="modal-section">
                        <p style="margin: 0; color: #6b7280; font-size: 14px;">
                            ¿Está seguro de que desea eliminar este aprendiz?
                        </p>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="modal-footer">
                    <button class="btn-modal btn-secondary" wire:click="closeDeleteModal">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button class="btn-modal btn-danger" wire:click="deleteAprendiz({{ $selectedAprendiz->id }})" 
                            wire:loading.attr="disabled">
                        <i wire:loading.remove wire:target="deleteAprendiz" class="fas fa-trash"></i>
                        <span wire:loading.remove wire:target="deleteAprendiz">Eliminar</span>
                        <i wire:loading wire:target="deleteAprendiz" class="fas fa-spinner fa-spin"></i>
                        <span wire:loading wire:target="deleteAprendiz">Eliminando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
