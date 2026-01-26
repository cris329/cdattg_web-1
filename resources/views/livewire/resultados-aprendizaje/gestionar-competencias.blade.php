<div class="gestion-container">
    @if($resultado)
        <!-- Header simple con información -->
        <div class="gestion-header">
            <div class="gestion-info">
                <h3 class="gestion-title">
                    {{ $resultado->codigo }} - {{ $resultado->nombre }}
                </h3>
                <div class="gestion-stats">
                    <span class="stat-item">
                        <strong>{{ $asignados->count() }}</strong> asignados
                    </span>
                    <span class="stat-separator">•</span>
                    <span class="stat-item">
                        <strong>{{ $disponibles->count() }}</strong> disponibles
                    </span>
                </div>
            </div>
        </div>

        <!-- Contenedor de tablas -->
        <div class="tables-container">
            <!-- Tabla de asignados -->
            <div class="table-section">
                <h4 class="section-title">Asignados</h4>
                <div class="table-wrapper">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th class="column-codigo">Código</th>
                                <th class="column-nombre">Competencia</th>
                                <th class="column-estado">Estado</th>
                                <th class="column-accion">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($asignados as $item)
                                <tr class="table-row">
                                    <td class="cell-codigo">
                                        <span class="code-badge">{{ $item->codigo }}</span>
                                    </td>
                                    <td class="cell-nombre">{{ $item->nombre }}</td>
                                    <td class="cell-estado">
                                        <span class="status-badge status-assigned">Asignado</span>
                                    </td>
                                    <td class="cell-accion">
                                        <button onclick="confirmarDesasignarCompetencia({{ $item->id }}, '{{ $item->codigo }} - {{ $item->nombre }}')" 
                                                class="btn-action btn-desasignar">
                                            <i class="fas fa-times"></i>
                                            <span>Desasignar</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr class="table-row">
                                    <td colspan="4" class="cell-empty">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-inbox"></i>
                                            </div>
                                            <div class="empty-text">
                                                <span class="empty-title">Sin competencias asignadas</span>
                                                <span class="empty-subtitle">No hay competencias asignadas a este resultado</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tabla de disponibles -->
            @if($disponibles->count() > 0)
                <div class="table-section">
                    <h4 class="section-title">Disponibles para asignar</h4>
                    <div class="table-wrapper">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th class="column-codigo">Código</th>
                                    <th class="column-nombre">Competencia</th>
                                    <th class="column-estado">Estado</th>
                                    <th class="column-accion">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($disponibles as $item)
                                    <tr class="table-row">
                                        <td class="cell-codigo">
                                            <span class="code-badge">{{ $item->codigo }}</span>
                                        </td>
                                        <td class="cell-nombre">{{ $item->nombre }}</td>
                                        <td class="cell-estado">
                                            <span class="status-badge status-available">Disponible</span>
                                        </td>
                                        <td class="cell-accion">
                                            <button onclick="confirmarAsignarCompetencia({{ $item->id }}, '{{ $item->codigo }} - {{ $item->nombre }}')" 
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
                </div>
            @endif
        </div>

    @else
        <div class="loading-state">
            <div class="loading-content">
                <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                <p>Cargando información del resultado...</p>
            </div>
        </div>
    @endif
</div>

@push('styles')
<link href="{{ asset('css/guias-aprendizaje.css') }}" rel="stylesheet">
<style>
/* Estilos específicos para gestión de competencias */

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

.gestion-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #495057;
    margin: 0;
    line-height: 1.2;
}

.gestion-stats {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.875rem;
    color: #6c757d;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.stat-separator {
    color: #dee2e6;
}

/* Contenedor de tablas */
.tables-container {
    display: flex;
    flex-direction: column;
    gap: 2rem;
    padding: 1.5rem;
}

.table-section {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.section-title {
    background: #f8f9fa;
    padding: 1rem 1.5rem;
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #495057;
    border-bottom: 1px solid #dee2e6;
}

/* Table Wrapper */
.table-wrapper {
    overflow-x: auto;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Modern Table */
.modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: white;
    font-size: 0.875rem;
}

/* Table Headers */
.modern-table thead th {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1rem;
    font-weight: 600;
    color: #495057;
    text-align: left;
    border-bottom: 2px solid #dee2e6;
    position: sticky;
    top: 0;
    z-index: 10;
}

.modern-table thead th.column-codigo {
    width: 120px;
    min-width: 100px;
}

.modern-table thead th.column-nombre {
    min-width: 300px;
}

.modern-table thead th.column-estado {
    width: 120px;
    min-width: 100px;
}

.modern-table thead th.column-accion {
    width: 140px;
    min-width: 120px;
    text-align: center;
}

/* Table Rows */
.modern-table tbody tr {
    border-bottom: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.modern-table tbody tr:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

/* Table Cells */
.modern-table tbody td {
    padding: 1rem;
    vertical-align: middle;
    border-right: 1px solid #e9ecef;
}

.modern-table tbody td:last-child {
    border-right: none;
}

/* Cell-specific styles */
.cell-codigo {
    font-family: 'Courier New', monospace;
    font-weight: 600;
}

.cell-nombre {
    color: #495057;
    line-height: 1.4;
}

.cell-estado {
    text-align: center;
}

.cell-accion {
    text-align: center;
}

.cell-empty {
    padding: 2rem;
    text-align: center;
}

/* Code Badge */
.code-badge {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    font-family: 'Courier New', monospace;
    display: inline-block;
    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
}

/* Status Badges */
.status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    transition: all 0.2s ease;
}

.status-badge.status-assigned {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
}

.status-badge.status-available {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
    box-shadow: 0 2px 4px rgba(23, 162, 184, 0.3);
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
    font-size: 2.5rem;
    color: #6c757d;
    margin-bottom: 1rem;
}

.empty-text {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.empty-title {
    font-weight: 600;
    color: #495057;
    font-size: 1rem;
}

.empty-subtitle {
    color: #6c757d;
    font-size: 0.875rem;
}

/* Loading State */
.loading-state {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.loading-content {
    text-align: center;
    color: #6c757d;
}

/* Responsive */
@media (max-width: 768px) {
    .gestion-header {
        padding: 1rem;
    }
    
    .gestion-title {
        font-size: 1.125rem;
    }
    
    .tables-container {
        padding: 1rem;
        gap: 1rem;
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
