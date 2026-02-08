@if($showFinalizarModal)
<div class="modal-backdrop" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
    <div class="modal-card" style="background: white; border-radius: 8px; padding: 24px; max-width: 400px; width: 90%; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <h5 class="text-danger mb-3">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Finalizar asistencia
        </h5>

        <p class="mb-4">
            ¿Está seguro que desea finalizar la asistencia?
            <br>
            <small class="text-muted">
                <i class="fas fa-info-circle mr-1"></i>
                No se podrán registrar más ingresos o salidas.
            </small>
        </p>

        @if($asistencia)
            <div class="alert alert-info mb-3">
                <small>
                    <strong>Información de la sesión:</strong><br>
                    Inicio: {{ $asistencia->hora_inicio->format('H:i:s') }}<br>
                    Duración: {{ $asistencia->duracion }}
                </small>
            </div>
        @endif

        <div class="modal-actions d-flex justify-content-between gap-2">
            <button 
                class="btn btn-light flex-fill" 
                wire:click="closeFinalizarModal"
                wire:loading.attr="disabled"
            >
                <i class="fas fa-times mr-1"></i>
                Cancelar
            </button>

            <button 
                class="btn btn-danger flex-fill" 
                wire:click="finalizarAsistencia"
                wire:loading.attr="disabled"
                wire:target="finalizarAsistencia"
            >
                <span wire:loading.remove>
                    <i class="fas fa-check-circle mr-1"></i>
                    Sí, finalizar
                </span>
                <span wire:loading>
                    <i class="fas fa-spinner fa-spin mr-1"></i>
                    Finalizando...
                </span>
            </button>
        </div>
    </div>
</div>
@endif

<style>
.modal-backdrop {
    animation: fadeIn 0.2s ease-in-out;
}

.modal-card {
    animation: slideUp 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { 
        opacity: 0;
        transform: translateY(20px);
    }
    to { 
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
