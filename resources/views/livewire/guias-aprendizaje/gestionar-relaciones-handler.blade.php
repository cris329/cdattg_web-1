<div class="gestionar-relaciones-container">
    <!-- Header de Relaciones -->
    <div class="relaciones-header">
        <div class="header-info">
            <h3 class="header-title">
                <i class="fas fa-link"></i>
                Gestión de Resultados de Aprendizaje
            </h3>
            <p class="header-subtitle">
                Administra los resultados asociados a la guía {{ $guia->codigo }}
            </p>
        </div>
        <div class="header-stats">
            <div class="stat-item">
                <span class="stat-number">{{ $resultadosAsignados->count() }}</span>
                <span class="stat-label">Asignados</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">{{ $resultadosDisponibles->count() }}</span>
                <span class="stat-label">Disponibles</span>
            </div>
        </div>
    </div>

    <!-- Contenedor Principal -->
    <div class="relaciones-main">
        <div class="relaciones-grid">
            <!-- Resultados Asignados -->
            <div class="relaciones-panel asignados">
                <div class="panel-header">
                    <h4 class="panel-title">
                        <i class="fas fa-check-circle text-success"></i>
                        Resultados Asignados
                    </h4>
                    <div class="panel-search">
                        <input 
                            type="text" 
                            wire:model.live="searchAsignado" 
                            placeholder="Buscar asignados..."
                            class="search-input-small"
                        >
                    </div>
                </div>
                
                <div class="panel-body">
                    @if($resultadosAsignados->count() > 0)
                        <div class="resultados-list">
                            @foreach($resultadosAsignados as $resultado)
                                <div class="resultado-item asignado">
                                    <div class="resultado-info">
                                        <span class="resultado-codigo">{{ $resultado->codigo }}</span>
                                        <span class="resultado-nombre">{{ $resultado->nombre }}</span>
                                    </div>
                                    <div class="resultado-actions">
                                        <button 
                                            onclick="confirmarDesasociar({{ $resultado->id }}, '{{ $resultado->codigo }} - {{ $resultado->nombre }}')"
                                            class="btn-action btn-remove"
                                            title="Desasociar resultado"
                                        >
                                            <i class="fas fa-unlink"></i>
                                            Quitar
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-inbox empty-icon"></i>
                            <p>No hay resultados asignados</p>
                            <small>Busca en la lista de disponibles y asigna los resultados necesarios</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Resultados Disponibles -->
            <div class="relaciones-panel disponibles">
                <div class="panel-header">
                    <h4 class="panel-title">
                        <i class="fas fa-plus-circle text-primary"></i>
                        Resultados Disponibles
                    </h4>
                    <div class="panel-search">
                        <input 
                            type="text" 
                            wire:model.live="searchDisponible" 
                            placeholder="Buscar disponibles..."
                            class="search-input-small"
                        >
                    </div>
                </div>
                
                <div class="panel-body">
                    @if($resultadosDisponibles->count() > 0)
                        <div class="resultados-list">
                            @foreach($resultadosDisponibles as $resultado)
                                <div class="resultado-item disponible">
                                    <div class="resultado-info">
                                        <span class="resultado-codigo">{{ $resultado->codigo }}</span>
                                        <span class="resultado-nombre">{{ $resultado->nombre }}</span>
                                    </div>
                                    <div class="resultado-actions">
                                        <button 
                                            onclick="confirmarAsociar({{ $resultado->id }}, '{{ $resultado->codigo }} - {{ $resultado->nombre }}')"
                                            class="btn-action btn-add"
                                            title="Asignar resultado"
                                        >
                                            <i class="fas fa-link"></i>
                                            Asignar
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-search empty-icon"></i>
                            <p>No hay resultados disponibles</p>
                            <small>Todos los resultados ya están asignados a esta guía</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen y Acciones -->
    <div class="relaciones-footer">
        <div class="footer-info">
            <div class="info-text">
                <i class="fas fa-info-circle"></i>
                <span>Los resultados de aprendizaje definen las competencias que se abordarán en esta guía.</span>
            </div>
        </div>
        <div class="footer-actions">
            <a href="{{ route('guias-aprendizaje.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Volver a Guías
            </a>
            @if($resultadosAsignados->count() > 0)
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i>
                    Imprimir Resumen
                </button>
            @endif
        </div>
    </div>
</div>

<style>
/* Estilos específicos para gestión de relaciones */
.gestionar-relaciones-container {
    padding: 20px;
    background: #f8fafc;
    min-height: 400px;
}

.relaciones-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding: 16px 20px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.header-title {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 8px;
}

.header-subtitle {
    margin: 4px 0 0 0;
    font-size: 14px;
    color: #6b7280;
}

.header-stats {
    display: flex;
    gap: 24px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
}

.stat-label {
    display: block;
    font-size: 12px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.relaciones-main {
    margin-bottom: 20px;
}

.relaciones-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    min-height: 400px;
}

.relaciones-panel {
    background: white;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.relaciones-panel.asignados {
    border-left: 4px solid #10b981;
}

.relaciones-panel.disponibles {
    border-left: 4px solid #3b82f6;
}

.panel-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
}

.panel-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 8px;
}

.panel-search {
    flex: 1;
    max-width: 200px;
}

.search-input-small {
    width: 100%;
    padding: 6px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 13px;
    transition: all 0.2s ease;
}

.search-input-small:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.panel-body {
    flex: 1;
    padding: 16px 20px;
    overflow-y: auto;
    max-height: 400px;
}

.resultados-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.resultado-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.resultado-item.asignado {
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.resultado-item.asignado:hover {
    background: #dcfce7;
    border-color: #86efac;
}

.resultado-item.disponible {
    background: #f8fafc;
    border-color: #e5e7eb;
}

.resultado-item.disponible:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.resultado-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.resultado-codigo {
    font-family: 'Courier New', monospace;
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    background: #e5e7eb;
    padding: 2px 6px;
    border-radius: 4px;
    display: inline-block;
    min-width: 60px;
    text-align: center;
}

.resultado-nombre {
    font-size: 14px;
    color: #1f2937;
    line-height: 1.4;
}

.resultado-actions {
    margin-left: 12px;
}

.btn-action {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    border: 1px solid;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    white-space: nowrap;
}

.btn-add {
    background: #eff6ff;
    color: #1d4ed8;
    border-color: #3b82f6;
}

.btn-add:hover {
    background: #1d4ed8;
    color: white;
}

.btn-remove {
    background: #fef2f2;
    color: #dc2626;
    border-color: #ef4444;
}

.btn-remove:hover {
    background: #dc2626;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6b7280;
}

.empty-icon {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state p {
    margin: 0 0 8px 0;
    font-weight: 500;
    font-size: 16px;
}

.empty-state small {
    font-size: 13px;
    opacity: 0.8;
}

.relaciones-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.footer-info {
    flex: 1;
}

.info-text {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #6b7280;
}

.footer-actions {
    display: flex;
    gap: 12px;
}

.btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.btn-secondary {
    background: #f9fafb;
    color: #374151;
    border-color: #d1d5db;
}

.btn-secondary:hover {
    background: #f3f4f6;
    color: #111827;
}

.btn-primary {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.btn-primary:hover {
    background: #2563eb;
}

/* Responsive */
@media (max-width: 768px) {
    .relaciones-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .relaciones-header {
        flex-direction: column;
        gap: 16px;
        text-align: center;
    }
    
    .header-stats {
        justify-content: center;
    }
    
    .relaciones-footer {
        flex-direction: column;
        gap: 16px;
        text-align: center;
    }
    
    .footer-actions {
        justify-content: center;
    }
    
    .resultado-item {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
    
    .resultado-actions {
        margin-left: 0;
        justify-content: center;
    }
}
</style>
