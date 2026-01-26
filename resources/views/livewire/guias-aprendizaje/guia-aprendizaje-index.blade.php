<div>
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
    <div wire:loading wire:target="search" class="loading-indicator">
        <i class="fas fa-spinner fa-spin"></i>
        Buscando...
    </div>

    <div wire:loading wire:target="statusFilter" class="loading-indicator">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por estado...
    </div>

    <div wire:loading wire:target="resultadoFilter" class="loading-indicator">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por resultado...
    </div>

    <div wire:loading wire:target="perPage" class="loading-indicator">
        <i class="fas fa-spinner fa-spin"></i>
        Actualizando resultados...
    </div>

    <div wire:loading wire:target="page" class="loading-indicator">
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
                                <button onclick="confirmarEliminarGuia({{ $guia->id }}, '{{ $guia->codigo }} - {{ $guia->nombre }}')" 
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
            <div class="modal-container" wire:click.stop>
                
                <!-- Header Simple Unificado -->
                <div class="modal-header-simple">
                    <div>
                        <h4 class="modal-title">{{ $selectedGuia->codigo }} - {{ $selectedGuia->nombre }}</h4>
                        <p class="modal-subtitle">
                            Guía de aprendizaje del SENA
                        </p>
                    </div>

                    <button class="modal-close" wire:click="$set('showShowModal', false)">✕</button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <div class="modal-content-wrapper">
                        
                        <!-- Sección: Información General -->
                        <div class="section-card">
                            <h6 class="section-title">Información General</h6>
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">Código</div>
                                    <div class="info-value">{{ $selectedGuia->codigo }}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Nombre</div>
                                    <div class="info-value">{{ $selectedGuia->nombre }}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Duración</div>
                                    <div class="info-value">
                                        {{ $selectedGuia->duracion_horas ?? 0 }} horas / {{ $selectedGuia->duracion_meses ?? 0 }} meses
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Programa</div>
                                    <div class="info-value">
                                        @if($selectedGuia->programaFormacion)
                                            {{ $selectedGuia->programaFormacion->codigo }} - {{ Str::limit($selectedGuia->programaFormacion->nombre, 30) }}
                                        @else
                                            Sin programa asignado
                                        @endif
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Estado</div>
                                    <div class="info-value">
                                        <span class="badge-status {{ $selectedGuia->status ? 'badge-active' : 'badge-inactive' }}">
                                            <i class="fas fa-{{ $selectedGuia->status ? 'check' : 'times' }} me-1"></i>
                                            {{ $selectedGuia->status ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sección: Descripción -->
                        <div class="section-card">
                            <h6 class="section-title">Descripción</h6>
                            <div class="description-content">
                                @if($selectedGuia->descripcion)
                                    <p>{{ $selectedGuia->descripcion }}</p>
                                @else
                                    <p class="text-muted">Sin descripción registrada</p>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Sección: Resultados de Aprendizaje -->
                        <div class="section-card">
                            <h6 class="section-title">Resultados de Aprendizaje ({{ $selectedGuia->resultadosAprendizaje->count() }})</h6>
                            @if ($selectedGuia->resultadosAprendizaje->count() > 0)
                                <div class="programs-list">
                                    @foreach ($selectedGuia->resultadosAprendizaje->take(5) as $resultado)
                                        <div class="program-item">
                                            <span class="program-code">{{ $resultado->codigo }}</span>
                                            <span class="program-name">{{ $resultado->nombre }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                @if ($selectedGuia->resultadosAprendizaje->count() > 5)
                                    <div class="text-muted small mt-2">
                                        ... y {{ $selectedGuia->resultadosAprendizaje->count() - 5 }} más
                                    </div>
                                @endif
                            @else
                                <p class="text-muted">No hay resultados de aprendizaje asociados a esta guía.</p>
                            @endif
                        </div>
                        
                        <!-- Sección: Actividades -->
                        <div class="section-card">
                            <h6 class="section-title">Actividades ({{ $selectedGuia->actividades->count() }})</h6>
                            @if ($selectedGuia->actividades->count() > 0)
                                <div class="results-summary">
                                    <div class="summary-item">
                                        <span class="summary-value">{{ $selectedGuia->actividades->count() }}</span>
                                        <span class="summary-label">actividades creadas</span>
                                    </div>
                                </div>
                                
                                <div class="results-list-small">
                                    @foreach($selectedGuia->actividades->take(3) as $actividad)
                                        <div class="result-item">
                                            <div class="result-code">{{ $actividad->codigo ?? 'N/A' }}</div>
                                            <div class="result-name">{{ Str::limit($actividad->nombre ?? 'Sin nombre', 40) }}</div>
                                        </div>
                                    @endforeach
                                </div>
                                @if ($selectedGuia->actividades->count() > 3)
                                    <div class="text-muted small mt-2">
                                        ... y {{ $selectedGuia->actividades->count() - 3 }} más
                                    </div>
                                @endif
                            @else
                                <div class="empty-section">
                                    <div class="empty-icon">
                                        <i class="fas fa-tasks"></i>
                                    </div>
                                    <p class="empty-text">No hay actividades asociadas</p>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Sección: Metodología y Evaluación -->
                        <div class="section-card">
                            <h6 class="section-title">Metodología y Evaluación</h6>
                            <div class="methodology-grid">
                                <div class="methodology-item">
                                    <div class="methodology-label">Objetivo General</div>
                                    <div class="methodology-value">
                                        @if($selectedGuia->objetivo_general)
                                            <p>{{ $selectedGuia->objetivo_general }}</p>
                                        @else
                                            <p class="text-muted">No definido</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="methodology-item">
                                    <div class="methodology-label">Metodología</div>
                                    <div class="methodology-value">
                                        @if($selectedGuia->metodologia)
                                            <p>{{ $selectedGuia->metodologia }}</p>
                                        @else
                                            <p class="text-muted">No definida</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="methodology-item">
                                    <div class="methodology-label">Sistema de Evaluación</div>
                                    <div class="methodology-value">
                                        @if($selectedGuia->evaluacion)
                                            <p>{{ $selectedGuia->evaluacion }}</p>
                                        @else
                                            <p class="text-muted">No definido</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sección: Acciones -->
                        <div class="section-card section-actions">
                            <h6 class="section-title">Acciones</h6>
                            <div class="quick-actions">
                                @can('EDITAR GUIA APRENDIZAJE')
                                    <button wire:click="openEditModal({{ $selectedGuia->id }})" 
                                            class="btn btn-warning">
                                        <i class="fas fa-edit me-2"></i>
                                        Editar guía
                                    </button>
                                @endcan
                                <button wire:click="toggleStatus({{ $selectedGuia->id }})" 
                                        wire:loading.attr="disabled"
                                        class="btn btn-{{ $selectedGuia->status ? 'danger' : 'success' }}">
                                    <i class="fas fa-{{ $selectedGuia->status ? 'times' : 'check' }} me-2"></i>
                                    {{ $selectedGuia->status ? 'Desactivar' : 'Activar' }}
                                </button>
                                @can('GESTIONAR RESULTADOS GUIA')
                                    <button wire:click="openGestionarResultados({{ $selectedGuia->id }})" 
                                            class="btn btn-primary">
                                        <i class="fas fa-link me-2"></i>
                                        Gestionar resultados
                                    </button>
                                @endcan
                            </div>
                        </div>
                        
                        <!-- Auditoría -->
                        <div class="audit-block">
                            <div class="audit-label">CREACIÓN</div>
                            <div class="audit-info">
                                <div class="audit-user">{{ $selectedGuia->userCreate->name ?? 'Sistema' }}</div>
                                <div class="audit-date">{{ $selectedGuia->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                        @if($selectedGuia->userEdit)
                            <div class="audit-block">
                                <div class="audit-label">Última modificación</div>
                                <div class="audit-info">
                                    <div class="audit-user">{{ $selectedGuia->userEdit->name }}</div>
                                    <div class="audit-date">{{ $selectedGuia->updated_at->format('d/m/Y H:i') }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Crear -->
    @if($showCreateModal)
        <div class="modal-erp-container">
            <div class="modal-overlay" wire:click="closeCreateModal">
                <div class="modal-container" wire:click.stop>
                    <div class="modal-header-simple">
                        <h2 class="modal-title">Crear Nueva Guía</h2>
                        <p class="modal-subtitle">
                            <i class="fas fa-plus-circle"></i>
                            Completa los datos para crear una nueva guía de aprendizaje
                        </p>
                        <button class="modal-close" wire:click="closeCreateModal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="modal-content-wrapper">
                            <livewire:guias-aprendizaje.guia-aprendizaje-form key="create-form" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Editar -->
    @if($showEditModal && $selectedGuia)
        <div class="modal-erp-container">
            <div class="modal-overlay" wire:click="closeEditModal">
                <div class="modal-container" wire:click.stop>
                    <div class="modal-header-simple">
                        <h2 class="modal-title">Editar Guía</h2>
                        <p class="modal-subtitle">
                            <i class="fas fa-edit"></i>
                            Modifica los datos de la guía {{ $selectedGuia->codigo }}
                        </p>
                        <button class="modal-close" wire:click="closeEditModal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="modal-content-wrapper">
                            <livewire:guias-aprendizaje.guia-aprendizaje-form :guia="$selectedGuia" :key="'edit-' . $selectedGuia->id" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Gestionar Resultados -->
    <div class="modal-erp-container" wire:ignore.self>
        <div class="modal-overlay" wire:ignore style="display: none;" id="gestionarResultadosModal">
            <div class="modal-container modal-large" wire:ignore>
                <livewire:guias-aprendizaje.gestionar-resultados />
            </div>
        </div>
    </div>

    </div>

    @push('styles')
    <style>
        .modal-large {
            max-width: 1200px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-container.modal-large {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }
    </style>
    @endpush

    <script>
        document.addEventListener('livewire:init', () => {
            // Escuchar evento para abrir modal de gestión de resultados
            Livewire.on('showGestionarResultadosModal', () => {
                const modal = document.getElementById('gestionarResultadosModal');
                if (modal) {
                    modal.style.display = 'flex';
                }
            });

            // Escuchar evento para cerrar modal de gestión de resultados
            Livewire.on('closeGestionarResultadosModal', () => {
                const modal = document.getElementById('gestionarResultadosModal');
                if (modal) {
                    modal.style.display = 'none';
                }
            });

            // Cerrar modal al hacer clic en el overlay
            document.addEventListener('click', (e) => {
                const modal = document.getElementById('gestionarResultadosModal');
                if (modal && e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</div>
