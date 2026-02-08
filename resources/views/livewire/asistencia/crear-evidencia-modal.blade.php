<div>
    <!-- Modal para crear evidencia -->
    @if($showModalEvidencia)
        <div class="modal-backdrop">
            <div class="modal-card">
                <div class="modal-header">
                    <h6 class="modal-title">
                        <i class="fas fa-file-alt mr-2"></i>
                        Registrar Evidencia
                    </h6>
                    <button wire:click="closeModalEvidencia" class="modal-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nombreEvidencia" class="form-label">
                            Nombre de la evidencia
                        </label>
                        <input type="text" 
                               id="nombreEvidencia"
                               class="form-control" 
                               placeholder="Ej: Clase PHP - Variables y Tipos de Datos, Práctica CSS - Flexbox, Evaluación JavaScript - Arrays..."
                               wire:model.defer="nombreEvidencia">
                        <small class="form-text text-muted">
                            Describe la actividad o clase que se va a registrar
                        </small>
                        @error('nombreEvidencia')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-info-circle mr-1"></i>
                            Información de la ficha
                        </label>
                        <div class="alert alert-info">
                            <div class="mb-1">
                                <strong>Ficha:</strong> {{ $selectedFicha->ficha ?? '' }}
                            </div>
                            <div class="mb-1">
                                <strong>Programa:</strong> {{ $selectedFicha->programaFormacion->nombre ?? '' }}
                            </div>
                            <div>
                                <strong>Instructor:</strong> {{ $selectedFicha->instructor->persona->primer_nombre ?? '' }} {{ $selectedFicha->instructor->persona->primer_apellido ?? '' }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button wire:click="closeModalEvidencia" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i>
                        Cancelar
                    </button>
                    <button wire:click="crearEvidencia" class="btn btn-primary">
                        <i class="fas fa-check mr-1"></i>
                        Crear y Continuar
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Estilos para el modal -->
    <style>
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .modal-card {
        background: white;
        border-radius: 14px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .modal-close {
        background: none;
        border: none;
        color: #6b7280;
        font-size: 1.25rem;
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 0.375rem;
        transition: all 0.2s;
    }

    .modal-close:hover {
        background: #f3f4f6;
        color: #374151;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        padding: 1.5rem;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .form-control {
        display: block;
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        transition: border-color 0.15s;
    }

    .form-control:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .form-text {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }

    .alert {
        padding: 0.75rem;
        border-radius: 0.375rem;
        margin-bottom: 1rem;
    }

    .alert-info {
        background: #dbeafe;
        border: 1px solid #3b82f6;
        color: #1e40af;
    }

    .alert-info div {
        margin-bottom: 0.25rem;
    }

    .alert-info div:last-child {
        margin-bottom: 0;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border: 1px solid transparent;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary {
        background: #2563eb;
        color: white;
        border-color: #2563eb;
    }

    .btn-primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
        border-color: #6b7280;
    }

    .btn-secondary:hover {
        background: #4b5563;
        border-color: #4b5563;
    }

    .text-danger {
        color: #dc2626;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .mr-1 {
        margin-right: 0.25rem;
    }

    .mr-2 {
        margin-right: 0.5rem;
    }

    .mb-1 {
        margin-bottom: 0.25rem;
    }

    .mt-1 {
        margin-top: 0.25rem;
    }
    </style>
</div>
