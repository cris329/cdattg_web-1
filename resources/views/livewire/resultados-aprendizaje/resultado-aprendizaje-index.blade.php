<style>
/* ===== ESTILOS MINIMALISTAS - COMPETENCIAS ===== */

:root {
    --primary: #1e3a8a;
    --primary-light: #3b82f6;
    --bg-light: #f8fafc;
    --border: #e2e8f0;
    --text: #374151;
    --text-muted: #64748b;
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    --shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* Layout */
.content-wrapper {
    margin-left: 250px !important;
    background: var(--bg-light);
    min-height: auto;
    display: flex;
    flex-direction: column;
    padding: 0 0.5rem;
}

.main-sidebar {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    height: 100vh !important;
    overflow-y: auto !important;
    z-index: 1000 !important;
}

/* ===== HEADER ADMINISTRATIVO COMPACTO ===== */
.admin-header .col-md-8 {
    padding: 0 0.5rem;
}

.admin-header .col-md-4 {
    padding: 0 0.5rem;
}

.admin-header {
    background: white;
    border-bottom: 1px solid var(--border);
    padding: 1.25rem 0;
    margin-bottom: 1rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    width: 100%;
    flex-shrink: 0;
}

.admin-header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 0.5rem;
}

.admin-header-left {
    display: flex;
    align-items: center;
}

.admin-header-text {
    flex: 1;
}

.admin-header-icon {
    width: 40px;
    height: 40px;
    background: var(--primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.admin-header-content {
    flex: 1;
}

.admin-header-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 0.125rem;
    line-height: 1.2;
}

.admin-header-subtitle {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-bottom: 0;
    line-height: 1.3;
}

.admin-breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0;
}

.breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0;
    font-size: 0.875rem;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "/";
    color: var(--text-muted);
}

.breadcrumb-item.active {
    color: var(--text);
    font-weight: 500;
}

/* ===== MAIN CARD ===== */
.main-card {
    background: white;
    border-radius: 8px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    overflow: hidden;
}

/* ===== BARRA DE HERRAMIENTAS ===== */
.toolbar {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    box-shadow: var(--shadow);
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.search-container {
    position: relative;
    flex: 1;
    min-width: 250px;
}

.search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 0.875rem;
}

.search-input {
    width: 100%;
    padding: 0.625rem 1rem 0.625rem 2.5rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    background: white;
}

.search-input:focus {
    outline: none;
    border-color: var(--primary-light);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.filters-container {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-select {
    padding: 0.625rem 1rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 0.875rem;
    background: white;
    cursor: pointer;
    transition: all 0.2s ease;
}

.filter-select:focus {
    outline: none;
    border-color: var(--primary-light);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn-clear-filters {
    padding: 0.625rem;
    background: var(--danger);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-clear-filters:hover {
    background: #dc2626;
    transform: translateY(-1px);
}

.results-selector {
    display: flex;
    align-items: center;
}

.results-select {
    padding: 0.625rem 1rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 0.875rem;
    background: white;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary-modern {
    padding: 0.625rem 1.25rem;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary-modern:hover {
    background: var(--primary-light);
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

/* ===== INDICADORES DE CARGA ===== */
.loading-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 6px;
    color: #0369a1;
    font-size: 0.875rem;
    margin-bottom: 1rem;
}

/* ===== TABLA MODERNA ===== */
.table-scroll-wrapper {
    overflow-x: auto;
    background: white;
    border-radius: 8px;
    box-shadow: var(--shadow);
}

.modern-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.modern-table thead {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.modern-table th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: var(--text);
    border-bottom: 2px solid var(--border);
    white-space: nowrap;
}

.modern-table th.sortable {
    cursor: pointer;
    user-select: none;
    transition: all 0.2s ease;
}

.modern-table th.sortable:hover {
    background: rgba(59, 130, 246, 0.05);
}

.sort-icon {
    margin-left: 0.5rem;
    font-size: 0.75rem;
    color: var(--primary-light);
}

.modern-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}

.modern-table tbody tr {
    transition: all 0.2s ease;
}

.modern-table tbody tr:hover {
    background: rgba(59, 130, 246, 0.02);
}

/* Columnas específicas */
.modern-table .codigo {
    width: 120px;
    font-weight: 600;
    color: var(--primary);
}

.modern-table .nombre {
    min-width: 250px;
}

.modern-table .duracion {
    width: 100px;
    text-align: center;
}

.modern-table .competencias,
.modern-table .guias {
    width: 100px;
    text-align: center;
}

.modern-table .estado {
    width: 120px;
    text-align: center;
}

.modern-table .th-actions,
.modern-table .td-actions {
    width: 200px;
    text-align: center;
}

.sticky-actions {
    position: sticky;
    right: 0;
    background: white;
    z-index: 10;
}

/* ===== BADGES MODERNOS ===== */
.badge-modern {
    display: inline-block;
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 12px;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.badge-primary {
    background: var(--primary-light);
    color: white;
}

.badge-info {
    background: var(--info);
    color: white;
}

.badge-warning {
    background: var(--warning);
    color: white;
}

.badge-secondary {
    background: var(--text-muted);
    color: white;
}

/* ===== BOTONES DE ACCIÓN ===== */
.btn-action {
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

.btn-action::before {
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

.btn-action:hover::before {
    left: 100%;
}

.btn-action i {
    font-size: 0.75rem;
    transition: transform 0.2s ease;
    position: relative;
    z-index: 2;
}

.btn-action:hover i {
    transform: scale(1.1);
}

.btn-action span {
    position: relative;
    z-index: 2;
}

/* Botón Ver (Azul medio) */
.btn-view {
    color: #3b82f6;
    border-color: #3b82f6;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(59, 130, 246, 0.02));
}

.btn-view:hover {
    color: #2563eb;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05));
    border-color: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.1);
}

.btn-view:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(59, 130, 246, 0.1);
}

/* Botón Editar (Naranja medio) */
.btn-edit {
    color: #f59e0b;
    border-color: #f59e0b;
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.05), rgba(245, 158, 11, 0.02));
}

.btn-edit:hover {
    color: #d97706;
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.05));
    border-color: #d97706;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(245, 158, 11, 0.1);
}

.btn-edit:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(245, 158, 11, 0.1);
}

/* Botón Gestionar Competencias (Púrpura medio) */
.btn-competencias {
    color: #8b5cf6;
    border-color: #8b5cf6;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.05), rgba(139, 92, 246, 0.02));
}

.btn-competencias:hover {
    color: #7c3aed;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(139, 92, 246, 0.05));
    border-color: #7c3aed;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(139, 92, 246, 0.1);
}

.btn-competencias:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(139, 92, 246, 0.1);
}

/* Botón Eliminar (Rojo medio) */
.btn-delete {
    color: #ef4444;
    border-color: #ef4444;
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), rgba(239, 68, 68, 0.02));
}

.btn-delete:hover {
    color: #dc2626;
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05));
    border-color: #dc2626;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(239, 68, 68, 0.1);
}

.btn-delete:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(239, 68, 68, 0.1);
}

/* ===== BOTÓN DE ESTADO SIMPLE ===== */
.badge-toggle {
    width: auto;
    height: auto;
    border-radius: 4px;
    border: none !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-size: 0.75rem;
    font-weight: 500;
    line-height: 1;
    padding: 0.375rem 0.75rem;
    margin: 0;
    gap: 0.375rem;
    white-space: nowrap;
    cursor: pointer;
    text-decoration: none;
    box-shadow: none !important;
}

.badge-toggle i {
    font-size: 0.7rem;
    transition: none;
}

.badge-toggle:hover i {
    transform: none;
}

.badge-toggle:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
}

/* Badge Toggle - Estado Activo (Verde simple) */
.badge-toggle.badge-success {
    color: white !important;
    background: #10b981 !important;
    border: none !important;
    box-shadow: none !important;
}

.badge-toggle.badge-success:hover {
    background: #059669 !important;
    transform: none;
    box-shadow: none !important;
}

.badge-toggle.badge-success:active {
    background: #047857 !important;
    transform: none;
    box-shadow: none !important;
}

/* Badge Toggle - Estado Inactivo (Gris simple) */
.badge-toggle.badge-danger {
    color: white !important;
    background: #6b7280 !important;
    border: none !important;
    box-shadow: none !important;
}

.badge-toggle.badge-danger:hover {
    background: #4b5563 !important;
    transform: none;
    box-shadow: none !important;
}

.badge-toggle.badge-danger:active {
    background: #374151 !important;
    transform: none;
    box-shadow: none !important;
}

/* ===== EMPTY STATES ===== */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 2rem;
    text-align: center;
    background: white;
    border-radius: 8px;
    box-shadow: var(--shadow);
}

.empty-state-icon {
    margin-bottom: 1rem;
    color: var(--text-muted);
}

.empty-state-icon i {
    font-size: 3rem;
}

.empty-state h3 {
    margin: 0 0 0.5rem 0;
    color: var(--text);
    font-weight: 600;
}

.empty-state p {
    margin: 0 0 1.5rem 0;
    color: var(--text-muted);
    font-size: 0.875rem;
}

.action-hint {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin: 0.25rem 0;
}

/* ===== MODAL GESTIÓN ===== */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    max-width: 90vw;
    max-height: 90vh;
    overflow: hidden;
}

.modal-lg {
    width: 900px;
}

.modal-header-simple {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
    border-bottom: 1px solid var(--border);
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.modal-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text);
}

.modal-subtitle {
    margin: 0.25rem 0 0 0;
    color: var(--text-muted);
    font-size: 0.875rem;
}

.code-pill {
    background: var(--primary-light);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-muted);
    padding: 0.5rem;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.modal-close:hover {
    background: rgba(0, 0, 0, 0.1);
    color: var(--text);
}

.modal-body {
    padding: 0;
    max-height: 70vh;
    overflow-y: auto;
}

.modal-content-wrapper {
    min-height: 400px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .toolbar {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .search-container {
        min-width: auto;
    }
    
    .filters-container {
        flex-wrap: wrap;
    }
    
    .modern-table {
        font-size: 0.8rem;
    }
    
    .modern-table th,
    .modern-table td {
        padding: 0.75rem 0.5rem;
    }
    
    .btn-action {
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
        min-width: 28px;
        height: 28px;
    }
    
    .btn-action i {
        font-size: 0.7rem;
    }
    
    .modal-lg {
        width: 95vw;
        margin: 1rem;
    }
}
</style>

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
    <div wire:loading wire:target="search" class="loading-indicator">
        <i class="fas fa-spinner fa-spin"></i>
        Buscando...
    </div>

    <div wire:loading wire:target="statusFilter" class="loading-indicator">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por estado...
    </div>

    <div wire:loading wire:target="competenciaFilter" class="loading-indicator">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por competencia...
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
                    <th class="competencias">Competencias</th>
                    <th class="guias">Guías</th>
                    <th class="estado">Estado</th>
                    <th class="th-actions sticky-actions">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($resultados as $resultado)
                    <tr>
                        <td class="codigo fw-medium">{{ $resultado->codigo }}</td>
                        <td class="nombre">{{ Str::limit($resultado->nombre, 50) }}</td>
                        <td class="duracion">
                            @if($resultado->duracion)
                                <span class="badge-modern badge-info">{{ $this->formatearHoras($resultado->duracion) }}h</span>
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
                                class="badge-toggle {{ $resultado->status ? 'badge-success' : 'badge-danger' }}"
                                title="Cambiar estado">
                                <i class="fas fa-sync-alt me-1"></i>
                                {{ $resultado->status ? 'Activo' : 'Inactivo' }}
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
                            @can('EDITAR RESULTADO APRENDIZAJE')
                                <button wire:click="openEditModal({{ $resultado->id }})" 
                                        class="btn-action btn-edit" 
                                        title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @endcan
                            @can('GESTIONAR COMPETENCIAS RESULTADO APRENDIZAJE')
                                <button wire:click="openCompetenciasModal({{ $resultado->id }})" 
                                        class="btn-action btn-competencias" 
                                        title="Gestionar competencias">
                                    <i class="fas fa-link"></i>
                                </button>
                            @endcan
                            @can('ELIMINAR RESULTADO APRENDIZAJE')
                                <button wire:click="confirmDelete({{ $resultado->id }})" 
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
                            @if($resultados->total() > 0)
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    <h3>No hay resultados en esta página</h3>
                                    <p>Esta página está vacía. Intenta con otra página o ajusta los filtros.</p>
                                    <div class="action-hint">Total: {{ $resultados->total() }} resultados</div>
                                    <div class="action-hint">Resultados por página: {{ $resultados->perPage() }}</div>
                                </div>
                            @else
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <h3>Aún no hay resultados de aprendizaje</h3>
                                    <p>Comienza creando tu primer resultado de aprendizaje para organizar las guías del SENA.</p>
                                    <div class="action-hint">Acción recomendada</div>
                                    @can('CREAR RESULTADO APRENDIZAJE')
                                        <button wire:click="openCreateModal" class="btn-primary-modern">
                                            <i class="fas fa-plus"></i>
                                            Crear Primer Resultado
                                        </button>
                                    @endcan
                                    <div class="action-hint">Tardarás menos de 2 minutos</div>
                                </div>
                            @endif
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
                
                <!-- Header Simple Unificado -->
                <div class="modal-header-simple">
                    <div>
                        <h4 class="modal-title">{{ $showEditModal ? 'Editar Resultado de Aprendizaje' : 'Nuevo Resultado de Aprendizaje' }}</h4>
                        <p class="modal-subtitle">
                            {{ $showEditModal ? 'Modifica los datos del resultado' : 'Completa los datos para crear un nuevo resultado' }}
                        </p>
                    </div>

                    <button class="modal-close" wire:click="$set('showCreateModal', false); $set('showEditModal', false)">✕</button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <div class="modal-content-wrapper">
                        <livewire:resultados-aprendizaje.resultado-aprendizaje-form 
                            :is-edit="$showEditModal" 
                            :resultado-id="$selectedResultado?->id" />
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Ver Detalles -->
    @if ($showShowModal && $selectedResultado)
        <div class="modal-overlay" wire:click="$set('showShowModal', false)">
            <div class="modal-container" wire:click.stop>
                
                <!-- Header Simple Unificado -->
                <div class="modal-header-simple">
                    <div>
                        <h4 class="modal-title">{{ $selectedResultado->codigo }} - {{ $selectedResultado->nombre }}</h4>
                        <p class="modal-subtitle">
                            Resultado de aprendizaje del SENA
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
                                    <div class="info-value">{{ $selectedResultado->codigo }}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Nombre</div>
                                    <div class="info-value">{{ $selectedResultado->nombre }}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Duración</div>
                                    <div class="info-value">
                                        @if($selectedResultado->duracion)
                                            {{ $this->formatearHoras($selectedResultado->duracion) }} horas
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Estado</div>
                                    <div class="info-value">
                                        <span class="badge-status {{ $selectedResultado->status ? 'badge-active' : 'badge-inactive' }}">
                                            <i class="fas fa-{{ $selectedResultado->status ? 'check' : 'times' }} me-1"></i>
                                            {{ $selectedResultado->status ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sección: Competencias Asociadas -->
                        <div class="section-card">
                            <h6 class="section-title">Competencias Asociadas ({{ $selectedResultado->competencias->count() }})</h6>
                            @if ($selectedResultado->competencias->count() > 0)
                                <div class="programs-list">
                                    @foreach ($selectedResultado->competencias->take(5) as $competencia)
                                        <div class="program-item">
                                            <span class="program-code">{{ $competencia->codigo }}</span>
                                            <span class="program-name">{{ $competencia->nombre }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                @if ($selectedResultado->competencias->count() > 5)
                                    <div class="text-muted small mt-2">
                                        ... y {{ $selectedResultado->competencias->count() - 5 }} más
                                    </div>
                                @endif
                            @else
                                <p class="text-muted">No hay competencias asociadas a este resultado.</p>
                            @endif
                        </div>
                        
                        <!-- Sección: Guías de Aprendizaje -->
                        <div class="section-card">
                            <h6 class="section-title">Guías de Aprendizaje ({{ $selectedResultado->guiasAprendizaje->count() }})</h6>
                            @if ($selectedResultado->guiasAprendizaje->count() > 0)
                                <div class="results-summary">
                                    <div class="summary-item">
                                        <span class="summary-value">{{ $selectedResultado->guiasAprendizaje->count() }}</span>
                                        <span class="summary-label">guías creadas</span>
                                    </div>
                                </div>
                                
                                <div class="results-list-small">
                                    @foreach($selectedResultado->guiasAprendizaje->take(3) as $guia)
                                        <div class="result-item">
                                            <div class="result-code">{{ $guia->codigo ?? 'N/A' }}</div>
                                            <div class="result-name">{{ Str::limit($guia->nombre ?? 'Sin nombre', 40) }}</div>
                                        </div>
                                    @endforeach
                                </div>
                                @if ($selectedResultado->guiasAprendizaje->count() > 3)
                                    <div class="text-muted small mt-2">
                                        ... y {{ $selectedResultado->guiasAprendizaje->count() - 3 }} más
                                    </div>
                                @endif
                            @else
                                <div class="empty-section">
                                    <div class="empty-icon">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <p class="empty-text">No hay guías de aprendizaje asociadas</p>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Sección: Acciones -->
                        <div class="section-card section-actions">
                            <h6 class="section-title">Acciones</h6>
                            <div class="quick-actions">
                                @can('EDITAR RESULTADO APRENDIZAJE')
                                    <button wire:click="openEditModal({{ $selectedResultado->id }})" 
                                            class="btn btn-warning">
                                        <i class="fas fa-edit me-2"></i>
                                        Editar resultado
                                    </button>
                                @endcan
                                <button wire:click="toggleStatus({{ $selectedResultado->id }})" 
                                        wire:loading.attr="disabled"
                                        class="btn btn-{{ $selectedResultado->status ? 'danger' : 'success' }}">
                                    <i class="fas fa-{{ $selectedResultado->status ? 'times' : 'check' }} me-2"></i>
                                    {{ $selectedResultado->status ? 'Desactivar' : 'Activar' }}
                                </button>
                                <button wire:click="openCompetenciasModal({{ $selectedResultado->id }})" 
                                        class="btn btn-primary">
                                    <i class="fas fa-link me-2"></i>
                                    Gestionar competencias
                                </button>
                            </div>
                        </div>
                                @if($selectedResultado->userEdit)
                                    <div class="audit-block">
                                        <div class="audit-label">Última modificación</div>
                                        <div class="audit-info">
                                            <div class="audit-user">{{ $selectedResultado->userEdit->name }}</div>
                                            <div class="audit-date">{{ $selectedResultado->updated_at->format('d/m/Y H:i') }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Confirmación Eliminación -->
    @if ($showDeleteModal && $selectedResultado)
        <div class="modal-overlay" wire:click="$set('showDeleteModal', false)">
            <div class="modal-container modal-sm" wire:click.stop>
                
                <!-- Header Simple Unificado -->
                <div class="modal-header-simple">
                    <div>
                        <h4 class="modal-title text-danger">Eliminar resultado de aprendizaje</h4>
                        <p class="modal-subtitle">Esta acción no se puede deshacer</p>
                    </div>

                    <button class="modal-close" wire:click="$set('showDeleteModal', false)">✕</button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <div class="modal-content-wrapper">
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <div>
                                <strong>¿Está seguro de que desea eliminar este resultado?</strong>
                                <p class="mb-0 mt-1">
                                    Código: <strong>{{ $selectedResultado->codigo }}</strong><br>
                                    Nombre: <strong>{{ $selectedResultado->nombre }}</strong>
                                </p>
                            </div>
                        </div>
                        
                        @if($selectedResultado->guiasAprendizaje->count() > 0)
                            <div class="alert alert-danger mt-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>No se puede eliminar.</strong> Este resultado tiene {{ $selectedResultado->guiasAprendizaje->count() }} guía(s) asociada(s).
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer-erp">
                    <div class="footer-actions">
                        <button wire:click="$set('showDeleteModal', false)" class="btn-erp btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </button>
                        @if($selectedResultado->guiasAprendizaje->count() == 0)
                            <button wire:click="deleteResultado({{ $selectedResultado->id }})" 
                                    class="btn-erp btn-danger">
                                <i class="fas fa-trash"></i>
                                Eliminar
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Gestión de Competencias -->
    @if ($showCompetenciasModal && $selectedResultado)
        <div class="modal-overlay" wire:click="$set('showCompetenciasModal', false)">
            <div class="modal-container modal-lg" wire:click.stop>
                
                <!-- Header Simple -->
                <div class="modal-header-simple">
                    <div>
                        <h4 class="modal-title">Gestión de Competencias</h4>
                        <p class="modal-subtitle">
                            <span class="code-pill">{{ $selectedResultado->codigo }}</span>
                        </p>
                    </div>
                    <button class="modal-close" wire:click="$set('showCompetenciasModal', false)">✕</button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <div class="modal-content-wrapper">
                        <livewire:resultados-aprendizaje.gestionar-competencias :resultadoId="$selectedResultado->id" />
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
