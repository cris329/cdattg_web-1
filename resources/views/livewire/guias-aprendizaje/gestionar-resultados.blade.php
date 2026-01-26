<div>
    @if($guia)
        <!-- Header simple -->
        <div class="modal-header-simple">
            <div>
                <h4 class="modal-title">Gestión de Resultados de Aprendizaje</h4>
                <p class="modal-subtitle">
                    <span class="code-pill">Guía {{ $guia->codigo }}</span>
                </p>
            </div>
            <button class="modal-close" wire:click="closeModal">✕</button>
        </div>

        <!-- Tabla única -->
        <div class="section-card">
            <h6 class="section-title">
                Resultados asignados ({{ $resultadosAsignados->count() }})
                @if($resultadosDisponibles->count() > 0)
                    <span class="text-muted">• Disponibles ({{ $resultadosDisponibles->count() }})</span>
                @endif
            </h6>
            <div class="table-wrapper">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th class="column-codigo">Código</th>
                            <th class="column-nombre">Resultado de aprendizaje</th>
                            <th class="column-estado">Estado</th>
                            <th class="column-accion">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resultadosAsignados as $resultado)
                            <tr class="table-row">
                                <td class="cell-codigo">
                                    <span class="code-badge">{{ $resultado->codigo }}</span>
                                </td>
                                <td class="cell-nombre">{{ $resultado->nombre }}</td>
                                <td class="cell-estado">
                                    <span class="status-badge status-assigned">Asignado</span>
                                </td>
                                <td class="cell-accion">
                                    <button onclick="confirmarDesasignarResultado({{ $resultado->id }}, '{{ $resultado->codigo }} - {{ $resultado->nombre }}')" 
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
                                            <span class="empty-title">Sin resultados asignados</span>
                                            <span class="empty-subtitle">Aún no hay resultados asignados a esta guía</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        
                        @foreach($resultadosDisponibles as $resultado)
                            <tr class="table-row">
                                <td class="cell-codigo">
                                    <span class="code-badge">{{ $resultado->codigo }}</span>
                                </td>
                                <td class="cell-nombre">{{ $resultado->nombre }}</td>
                                <td class="cell-estado">
                                    <span class="status-badge status-available">Disponible</span>
                                </td>
                                <td class="cell-accion">
                                    <button onclick="confirmarAsignarResultado({{ $resultado->id }}, '{{ $resultado->codigo }} - {{ $resultado->nombre }}')" 
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

        
        
    @else
        <div class="loading-state">
            <div class="loading-content">
                <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                <p>Cargando información de la guía...</p>
            </div>
        </div>
    @endif
</div>

@push('styles')
<link href="{{ asset('css/guias-aprendizaje.css') }}" rel="stylesheet">
<style>
/* Stats Simple - usando clases del sistema */
.stats-simple {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 1.5rem;
    background: var(--bg-light);
    border-bottom: 1px solid var(--border);
    font-size: 0.9rem;
    color: var(--text);
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

/* Action Buttons - Diseño Profesional Outline */
.action-btn,
button.action-btn,
.btn.action-btn,
.modern-table button,
.modern-table .action-btn {
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    padding: 0.625rem 1.25rem !important;
    border: 2px solid !important;
    border-radius: 6px !important;
    font-size: 0.8rem !important;
    font-weight: 500 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    text-decoration: none !important;
    background: transparent !important;
    box-shadow: none !important;
    letter-spacing: 0.025em !important;
    line-height: 1 !important;
    white-space: nowrap !important;
}

.action-btn::before {
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

.action-btn:hover::before {
    left: 100%;
}

.action-btn i {
    font-size: 0.875rem !important;
    transition: transform 0.2s ease;
    position: relative;
    z-index: 2;
}

.action-btn:hover i {
    transform: scale(1.1);
}

.action-btn span {
    position: relative;
    z-index: 2;
}

/* Desasignar Button - Outline Red */
.action-btn.action-remove,
button.action-btn.action-remove,
.btn.action-btn.action-remove,
.modern-table button.action-remove,
.modern-table .action-btn.action-remove {
    color: #dc2626 !important;
    border-color: #dc2626 !important;
}

.action-btn.action-remove:hover,
button.action-btn.action-remove:hover,
.btn.action-btn.action-remove:hover,
.modern-table button.action-remove:hover,
.modern-table .action-btn.action-remove:hover {
    background-color: #fef2f2 !important;
    color: #dc2626 !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 2px 4px rgba(220, 38, 38, 0.1);
}

.action-btn.action-remove:active,
button.action-btn.action-remove:active,
.btn.action-btn.action-remove:active,
.modern-table button.action-remove:active,
.modern-table .action-btn.action-remove:active {
    background-color: #f8d7da !important;
    color: #dc2626 !important;
    transform: translateY(0) !important;
    box-shadow: 0 1px 2px rgba(220, 38, 38, 0.1);
}

/* Asignar Button - Outline Green */
.action-btn.action-add,
button.action-btn.action-add,
.btn.action-btn.action-add,
.modern-table button.action-add,
.modern-table .action-btn.action-add {
    color: #059669 !important;
    border-color: #059669 !important;
}

.action-btn.action-add:hover,
button.action-btn.action-add:hover,
.btn.action-btn.action-add:hover,
.modern-table button.action-add:hover,
.modern-table .action-btn.action-add:hover {
    background-color: #f0fdf4 !important;
    color: #059669 !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 2px 4px rgba(5, 150, 21, 0.1);
}

.action-btn.action-add:active,
button.action-btn.action-add:active,
.btn.action-btn.action-add:active,
.modern-table button.action-add:active,
.modern-table .action-btn.action-add:active {
    background-color: #d1fae5 !important;
    color: #059669 !important;
    transform: translateY(0) !important;
    box-shadow: 0 1px 2px rgba(5, 150, 21, 0.1);
}

/* Loading State */
.action-btn.loading {
    pointer-events: none;
    opacity: 0.7;
}

.action-btn.loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Force Override - Ensure Outline Style */
.cell-accion button,
.cell-accion .action-btn,
td.cell-accion button,
td.cell-accion .action-btn,
.modern-table td button,
.modern-table td .action-btn,
tr td button,
tr td .action-btn,
table button,
table .action-btn,
button.action-btn,
.action-btn {
    background: transparent !important;
    border: 2px solid !important;
    color: inherit !important;
    box-shadow: none !important;
}

/* ULTRA FORCE - Botón Asignar Verde */
.cell-accion button.action-add,
.cell-accion .action-btn.action-add,
td.cell-accion button.action-add,
td.cell-accion .action-btn.action-add,
.modern-table td button.action-add,
.modern-table td .action-btn.action-add,
tr td button.action-add,
tr td .action-btn.action-add,
table button.action-add,
table .action-btn.action-add,
button.action-btn.action-add,
.action-btn.action-add {
    color: #059669 !important;
    border-color: #059669 !important;
    background: transparent !important;
}

/* ULTRA FORCE - Botón Desasignar Rojo */
.cell-accion button.action-remove,
.cell-accion .action-btn.action-remove,
td.cell-accion button.action-remove,
td.cell-accion .action-btn.action-remove,
.modern-table td button.action-remove,
.modern-table td .action-btn.action-remove,
tr td button.action-remove,
tr td .action-btn.action-remove,
table button.action-remove,
table .action-btn.action-remove,
button.action-btn.action-remove,
.action-btn.action-remove {
    color: #dc2626 !important;
    border-color: #dc2626 !important;
    background: transparent !important;
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

/* Responsive */
@media (max-width: 768px) {
    .modern-table {
        font-size: 0.8rem;
    }
    
    .modern-table thead th,
    .modern-table tbody td {
        padding: 0.75rem 0.5rem;
    }
    
    .action-btn {
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
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

<!-- Scripts optimizados - sin logs innecesarios -->
<script>
// Funciones globales para gestión de resultados
window.confirmarDesasignarResultado = function(resultadoId, nombreCompleto) {
    const partes = nombreCompleto.split(' - ');
    const codigo = partes[0] || resultadoId;
    const nombre = partes[1] || nombreCompleto;
    
    // Deshabilitar temporalmente para evitar doble click
    const button = event.target.closest('button');
    if (button) {
        button.disabled = true;
        setTimeout(() => {
            button.disabled = false;
        }, 2000);
    }
    
    if (typeof showConfirmModal === 'function') {
        showConfirmModal(
            'Desasignar resultado',
            '¿Desea quitar este resultado de la guía?',
            'danger',
            'desasignarResultado',
            resultadoId,
            codigo,
            nombre
        );
    } else {
        // Fallback simple
        if (confirm('¿Desea desasignar este resultado?')) {
            Livewire.dispatch('confirmAction', {
                action: 'desasignarResultado',
                params: resultadoId
            });
        }
    }
};

window.confirmarAsignarResultado = function(resultadoId, nombreCompleto) {
    const partes = nombreCompleto.split(' - ');
    const codigo = partes[0] || resultadoId;
    const nombre = partes[1] || nombreCompleto;
    
    // Deshabilitar temporalmente para evitar doble click
    const button = event.target.closest('button');
    if (button) {
        button.disabled = true;
        setTimeout(() => {
            button.disabled = false;
        }, 2000);
    }
    
    if (typeof showConfirmModal === 'function') {
        showConfirmModal(
            'Asignar resultado',
            '¿Desea asignar este resultado a la guía?',
            'success',
            'asignarResultado',
            resultadoId,
            codigo,
            nombre
        );
    } else {
        // Fallback simple
        if (confirm('¿Desea asignar este resultado?')) {
            Livewire.dispatch('confirmAction', {
                action: 'asignarResultado',
                params: resultadoId
            });
        }
    }
};

// Escuchar respuesta de la modal global
document.addEventListener('livewire:init', () => {
    Livewire.on('confirmAction', (data) => {
        if (data.action === 'desasignarResultado') {
            @this.desasignarResultado(data.params);
        } else if (data.action === 'asignarResultado') {
            @this.asignarResultadoDirecto(data.params);
        }
    });
});
</script>
