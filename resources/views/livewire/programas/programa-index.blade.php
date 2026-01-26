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
            
            <select wire:model.live="redConocimientoFilter" class="filter-select">
                <option value="">Todas las redes</option>
                @foreach($redesConocimiento as $red)
                    <option value="{{ $red->id }}">{{ $red->nombre }}</option>
                @endforeach
            </select>
            
            <select wire:model.live="nivelFilter" class="filter-select">
                <option value="">Todos los niveles</option>
                @foreach($nivelesFormacion as $nivel)
                    <option value="{{ $nivel->id }}">{{ $nivel->name }}</option>
                @endforeach
            </select>
            
            @if ($search || $statusFilter !== '' || $redConocimientoFilter !== '' || $nivelFilter !== '')
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
        
        @can('programa.create')
            <button wire:click="openCreateModal" class="btn-primary-modern">
                <i class="fas fa-plus"></i>
                Nuevo Programa
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

    <div wire:loading wire:target="redConocimientoFilter" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por red...
    </div>

    <div wire:loading wire:target="nivelFilter" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por nivel...
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
                    <th class="red">Red de Conocimiento</th>
                    <th class="nivel">Nivel</th>
                    <th class="sortable horas-total" wire:click="sortBy('horas_totales')">
                        Horas Totales
                        @if ($sortField === 'horas_totales')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} sort-icon"></i>
                        @endif
                    </th>
                    <th class="estado">Estado</th>
                    <th class="th-actions sticky-actions">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($programas as $programa)
                    <tr>
                        <td class="codigo">
                            <span class="badge-modern badge-primary">{{ $programa->codigo }}</span>
                        </td>
                        <td class="nombre fw-medium">{{ $programa->nombre }}</td>
                        <td class="red">
                            @if ($programa->redConocimiento)
                                <span class="badge-modern badge-info">{{ $programa->redConocimiento->nombre }}</span>
                            @else
                                <span class="badge-modern badge-secondary">Sin asignar</span>
                            @endif
                        </td>
                        <td class="nivel">
                            @if ($programa->nivelFormacion)
                                <span class="badge-modern badge-success">{{ $programa->nivelFormacion->name }}</span>
                            @else
                                <span class="badge-modern badge-warning">Sin asignar</span>
                            @endif
                        </td>
                        <td class="horas-total">
                            <span class="badge-modern badge-primary">{{ $programa->horas_totales }}h</span>
                        </td>
                        <td class="estado">
                            <button
                                wire:click="toggleStatus({{ $programa->id }})"
                                wire:loading.attr="disabled"
                                class="badge-toggle {{ (int) $programa->status === 1 ? 'badge-success' : 'badge-danger' }}"
                                title="Cambiar estado">
                                <i class="fas fa-sync-alt me-1"></i>
                                {{ (int) $programa->status === 1 ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        <td class="td-actions sticky-actions">
                            @can('programa.show')
                                <button wire:click="openShowModal({{ $programa->id }})" 
                                        class="btn-action btn-view" 
                                        title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @endcan
                            @can('programa.edit')
                                <button wire:click="openEditModal({{ $programa->id }})" 
                                        class="btn-action btn-edit" 
                                        title="editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @endcan
                            @can('programa.delete')
                                <button wire:click="confirmDelete({{ $programa->id }})" 
                                        class="btn-action btn-delete" 
                                        title="Eliminar programa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <h3>Aún no hay programas de formación</h3>
                                <p>Comienza creando tu primer programa de formación para gestionar la oferta educativa del SENA.</p>
                                <div class="action-hint">Acción recomendada</div>
                                @can('programa.create')
                                    <button wire:click="openCreateModal" class="btn-primary-modern">
                                        <i class="fas fa-plus"></i>
                                        Crear Primer Programa
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
                    Mostrando {{ $programas->firstItem() ?? 0 }} a {{ $programas->lastItem() ?? 0 }} 
                    de {{ $programas->total() }} resultados
                </div>
                <div class="pagination-links">
                    {{ $programas->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>

    <!-- Modal Confirmación Eliminación -->
    @if ($showDeleteModal && $selectedPrograma)
        <div class="modal-overlay" wire:click="closeDeleteModal">
            <div class="modal-container" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Programa</h5>
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
                        <h6 class="section-title">Información del Programa</h6>
                        <div style="display: grid; gap: 8px;">
                            <div><strong>Código:</strong> {{ $selectedPrograma->codigo }}</div>
                            <div><strong>Nombre:</strong> {{ $selectedPrograma->nombre }}</div>
                            <div><strong>Red de Conocimiento:</strong> {{ $selectedPrograma->redConocimiento->nombre ?? 'Sin asignar' }}</div>
                        </div>
                    </div>
                    
                    <!-- Mensaje de confirmación -->
                    <div class="modal-section">
                        <p style="margin: 0; color: #6b7280; font-size: 14px;">
                            ¿Está seguro de que desea eliminar este programa?
                        </p>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="modal-footer">
                    <button class="btn-modal btn-secondary" wire:click="closeDeleteModal">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button class="btn-modal btn-danger" wire:click="deletePrograma({{ $selectedPrograma->id }})" 
                            wire:loading.attr="disabled">
                        <i wire:loading.remove wire:target="deletePrograma" class="fas fa-trash"></i>
                        <span wire:loading.remove wire:target="deletePrograma">Eliminar</span>
                        <i wire:loading wire:target="deletePrograma" class="fas fa-spinner fa-spin"></i>
                        <span wire:loading wire:target="deletePrograma">Eliminando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Crear/Editar -->
    @if ($showCreateModal || $showEditModal)
        <div class="modal-overlay" wire:click="closeCreateEditModals">
            <div class="modal-container modal-lg" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $showCreateModal ? 'Crear Programa' : 'Editar Programa' }}
                    </h5>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    @if ($showCreateModal)
                        <livewire:programas.programa-form />
                    @endif
                    @if ($showEditModal && $selectedPrograma)
                        <livewire:programas.programa-form :programaId="$selectedPrograma->id" />
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Ver Detalles -->
    @if ($showShowModal && $selectedPrograma)
        <div class="modal-overlay" wire:click="$set('showShowModal', false)">
            <div class="modal-container modal-lg" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">{{ $selectedPrograma->nombre }}</h5>
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
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedPrograma->codigo }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Red de Conocimiento</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedPrograma->redConocimiento->nombre ?? '' }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Nivel de Formación</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedPrograma->nivelFormacion->name ?? '' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Distribución de Horas -->
                    <div class="modal-section">
                        <h6 class="section-title">Distribución de Horas</h6>
                        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 16px;">⏱</span>
                                <div>
                                    <div style="font-size: 12px; color: #6b7280; text-transform: uppercase;">Total</div>
                                    <div style="font-size: 16px; font-weight: 600; color: #1f2937;">{{ $selectedPrograma->horas_totales }}h</div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 16px;">📘</span>
                                <div>
                                    <div style="font-size: 12px; color: #6b7280; text-transform: uppercase;">Lectiva</div>
                                    <div style="font-size: 16px; font-weight: 600; color: #1f2937;">{{ $selectedPrograma->horas_etapa_lectiva }}h</div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 16px;">🏭</span>
                                <div>
                                    <div style="font-size: 12px; color: #6b7280; text-transform: uppercase;">Productiva</div>
                                    <div style="font-size: 16px; font-weight: 600; color: #1f2937;">{{ $selectedPrograma->horas_etapa_productiva }}h</div>
                                </div>
                            </div>
                        </div>
                        @if (($selectedPrograma->horas_etapa_lectiva + $selectedPrograma->horas_etapa_productiva) == $selectedPrograma->horas_totales)
                            <div style="margin-top: 8px; color: #10b981; font-size: 13px;">
                                ✓ Distribución válida
                            </div>
                        @endif
                    </div>
                    
                    <!-- Sección: Estado del Programa -->
                    <div class="modal-section">
                        <h6 class="section-title">Estado del Programa</h6>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span class="badge-status {{ (int) $selectedPrograma->status === 1 ? 'badge-active' : 'badge-inactive' }}" style="padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; text-transform: uppercase;">
                                <i class="fas fa-{{ (int) $selectedPrograma->status === 1 ? 'check' : 'times' }}" style="margin-right: 4px;"></i>
                                {{ (int) $selectedPrograma->status === 1 ? 'Activo' : 'Inactivo' }}
                            </span>
                            <span style="font-size: 13px; color: #6b7280; font-style: italic;">
                                Este programa {{ (int) $selectedPrograma->status === 1 ? 'puede' : 'no puede' }} ser usado en fichas de formación activas
                            </span>
                        </div>
                    </div>
                    
                    <!-- Sección: Acciones -->
                    <div class="modal-section">
                        <h6 class="section-title">Acciones</h6>
                        <div style="display: flex; gap: 12px;">
                            @can('programa.edit')
                                <button class="btn-modal btn-primary" wire:click="openEditModal({{ $selectedPrograma->id }})">
                                    <i class="fas fa-edit"></i>
                                    Editar Programa
                                </button>
                            @endcan
                            <button class="btn-modal {{ (int) $selectedPrograma->status === 1 ? 'btn-danger' : 'btn-success' }}" 
                                    wire:click="toggleStatus({{ $selectedPrograma->id }})" 
                                    wire:loading.attr="disabled">
                                <i wire:loading.remove wire:target="toggleStatus" class="fas fa-sync-alt"></i>
                                <span wire:loading.remove wire:target="toggleStatus">
                                    {{ (int) $selectedPrograma->status === 1 ? 'Desactivar Programa' : 'Activar Programa' }}
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
                                <div style="font-size: 13px; color: #374151; font-weight: 500;">{{ $selectedPrograma->userCreated->name ?? 'Sistema' }}</div>
                                <div style="font-size: 12px; color: #6b7280;">{{ $selectedPrograma->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #9ca3af; text-transform: uppercase; margin-bottom: 4px;">Última edición</div>
                                <div style="font-size: 13px; color: #374151; font-weight: 500;">{{ $selectedPrograma->userEdited->name ?? 'Sin edición' }}</div>
                                <div style="font-size: 12px; color: #6b7280;">{{ $selectedPrograma->updated_at->format('d/m/Y H:i') }}</div>
                            </div>
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
</div>
