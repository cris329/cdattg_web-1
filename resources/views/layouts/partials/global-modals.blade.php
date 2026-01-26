{{-- Global Modals Container - Cargado UNA SOLA VEZ --}}
<div id="globalConfirmModal">
    <div class="modal-confirm">
        <div class="modal-confirm-icon success">
            <i class="fas fa-link"></i>
        </div>
        <h5 class="modal-confirm-title">Confirmar acción</h5>
        <p class="modal-confirm-text">¿Desea realizar esta acción?</p>
        <div class="modal-confirm-item">
            <span class="code-pill">38362</span>
            <span class="tag">Competencia</span>
            <span>Modelado de los artefactos del software</span>
        </div>
        <div class="modal-confirm-actions">
            <button type="button" class="btn btn-light" onclick="closeConfirmModal()">Cancelar</button>
            <button type="button" class="btn btn-primary btn-confirm" onclick="confirmAction()">Confirmar</button>
        </div>
    </div>
</div>

<style>
/* Estilos del modal global */
#globalConfirmModal {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background-color: rgba(0, 0, 0, 0.5) !important;
    display: none !important;
    align-items: center !important;
    justify-content: center !important;
    z-index: 9999 !important;
}

#globalConfirmModal.show {
    display: flex !important;
}

.modal-confirm {
    background: white !important;
    border-radius: 8px !important;
    padding: 24px !important;
    text-align: center !important;
    max-width: 400px !important;
    width: 90% !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15) !important;
}

.modal-confirm-icon {
    width: 48px !important;
    height: 48px !important;
    margin: 0 auto 12px !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 20px !important;
}

.modal-confirm-icon.success {
    background: #e0f2fe !important;
    color: #0284c7 !important;
}

.modal-confirm-icon.danger {
    background: #fee2e2 !important;
    color: #b91c1c !important;
}

.modal-confirm-title {
    font-size: 18px !important;
    font-weight: 600 !important;
    margin-bottom: 6px !important;
    color: #1f2937 !important;
}

.modal-confirm-text {
    font-size: 14px !important;
    color: #6b7280 !important;
    margin-bottom: 16px !important;
    line-height: 1.4 !important;
}

.modal-confirm-item {
    background: #f9fafb !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 6px !important;
    padding: 10px !important;
    font-size: 14px !important;
    display: flex !important;
    gap: 8px !important;
    align-items: center !important;
    justify-content: center !important;
    margin-bottom: 12px !important;
}

.code-pill {
    background: #e5e7eb !important;
    color: #374151 !important;
    padding: 2px 6px !important;
    border-radius: 4px !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    min-width: 50px !important;
    text-align: center !important;
}

.tag {
    background: #f3f4f6 !important;
    color: #6b7280 !important;
    padding: 2px 6px !important;
    border-radius: 4px !important;
    font-size: 11px !important;
    font-weight: 500 !important;
    text-transform: uppercase !important;
}

.modal-confirm-actions {
    display: flex !important;
    justify-content: center !important;
    gap: 12px !important;
}

.modal-confirm-actions button {
    padding: 8px 16px !important;
    border-radius: 6px !important;
    font-weight: 500 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    border: 1px solid !important;
}

.btn-light {
    background: #f9fafb !important;
    color: #374151 !important;
    border-color: #d1d5db !important;
}

.btn-light:hover {
    background: #f3f4f6 !important;
}

.btn-primary {
    background: #0284c7 !important;
    color: white !important;
    border-color: #0284c7 !important;
}

.btn-primary:hover {
    background: #0369a1 !important;
}

.btn-danger {
    background: #dc2626 !important;
    color: white !important;
    border-color: #dc2626 !important;
}

.btn-danger:hover {
    background: #b91c1c !important;
}

/* Animaciones */
.modal-confirm {
    animation: modalSlideIn 0.3s ease-out !important;
}

@keyframes modalSlideIn {
    from {
        opacity: 0 !important;
        transform: translateY(-20px) scale(0.95) !important;
    }
    to {
        opacity: 1 !important;
        transform: translateY(0) scale(1) !important;
    }
}
</style>

<script>
// Sistema de modales profesional ERP - CARGADO UNA SOLA VEZ
if (typeof modalConfig === 'undefined') {
    let modalConfig = null;
    
    function showConfirmModal(title, message, type, action, params, codigo, nombre) {
        modalConfig = {action, params};
        
        const modal = document.getElementById('globalConfirmModal');
        const titleEl = modal.querySelector('.modal-confirm-title');
        const messageEl = modal.querySelector('.modal-confirm-text');
        const iconEl = modal.querySelector('.modal-confirm-icon');
        const codeEl = modal.querySelector('.code-pill');
        const tagEl = modal.querySelector('.tag');
        const nameEl = modal.querySelector('.modal-confirm-item span:last-child');
        const confirmBtn = modal.querySelector('.btn-confirm');
        
        // Configurar título y mensaje
        titleEl.textContent = title;
        messageEl.textContent = message;
        
        // Configurar icono según tipo
        iconEl.className = 'modal-confirm-icon';
        switch(type) {
            case 'danger':
                iconEl.classList.add('danger');
                iconEl.innerHTML = '<i class="fas fa-unlink"></i>';
                break;
            case 'info':
            case 'success':
                iconEl.classList.add('success');
                iconEl.innerHTML = '<i class="fas fa-link"></i>';
                break;
            default:
                iconEl.classList.add('success');
                iconEl.innerHTML = '<i class="fas fa-check"></i>';
        }
        
        // Configurar información de la competencia
        if (codeEl) codeEl.textContent = codigo;
        if (tagEl) tagEl.textContent = 'Competencia';
        if (nameEl) nameEl.textContent = nombre;
        
        // Configurar botón
        if (confirmBtn) {
            confirmBtn.className = 'btn btn-' + type;
            confirmBtn.textContent = type === 'danger' ? 'Quitar' : 'Asignar';
        } else {
            const altConfirmBtn = modal.querySelector('button[onclick*="confirmAction"]');
            if (altConfirmBtn) {
                altConfirmBtn.className = 'btn btn-' + type;
                altConfirmBtn.textContent = type === 'danger' ? 'Quitar' : 'Asignar';
            }
        }
        
        // 🔥 LIMPIAR OVERLAYS ANTES DE MOSTRAR
        document.body.classList.remove('modal-open');
        document.querySelectorAll('.modal-backdrop').forEach(e => e.remove());
        document.body.style.overflow = '';
        
        // Mostrar modal
        modal.style.display = 'block';
        modal.classList.add('show');
    }
    
    function closeConfirmModal() {
        const modal = document.getElementById('globalConfirmModal');
        modal.style.display = 'none';
        modal.classList.remove('show');
        modalConfig = null;
        
        // 🔥 LIMPIAR OVERLAYS AL CERRAR
        document.body.classList.remove('modal-open');
        document.querySelectorAll('.modal-backdrop').forEach(e => e.remove());
        document.body.style.overflow = '';
    }
    
    function confirmAction() {
        if (!modalConfig) return;
        
        // Enviar evento a Livewire
        Livewire.dispatch('confirmAction', {
            action: modalConfig.action,
            params: modalConfig.params
        });
        
        closeConfirmModal();
    }
    
    // Hacer las funciones globales
    window.showConfirmModal = showConfirmModal;
    window.closeConfirmModal = closeConfirmModal;
    window.confirmAction = confirmAction;
}

// Métodos para confirmación
window.confirmarAsociar = function(competenciaId, nombreCompetencia) {
    const partes = nombreCompetencia.split(' - ');
    const codigo = partes[0] || competenciaId;
    const nombre = partes[1] || nombreCompetencia;
    
    showConfirmModal(
        'Asignar competencia',
        '¿Desea asignar esta competencia al programa?',
        'info',
        'asignarCompetencia',
        competenciaId,
        codigo,
        nombre
    );
};

window.confirmarDesasociar = function(competenciaId, nombreCompetencia) {
    const partes = nombreCompetencia.split(' - ');
    const codigo = partes[0] || competenciaId;
    const nombre = partes[1] || nombreCompetencia;
    
    showConfirmModal(
        'Desasociar competencia',
        '¿Desea quitar esta competencia del programa?',
        'danger',
        'desasociarCompetencia',
        competenciaId,
        codigo,
        nombre
    );
};

// Event listeners - CARGADOS UNA SOLA VEZ
document.addEventListener('DOMContentLoaded', function() {
    // Cerrar modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeConfirmModal();
        }
    });
    
    // Cerrar modal al hacer clic fuera
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('globalConfirmModal');
        if (modal && e.target === modal) {
            closeConfirmModal();
        }
    });
    
    // 🔥 EVENT LISTENER GLOBAL PARA LIMPIAR OVERLAYS
    document.addEventListener('close-modal', function() {
        document.body.classList.remove('modal-open');
        document.querySelectorAll('.modal-backdrop').forEach(e => e.remove());
        document.body.style.overflow = '';
    });
});

// Función de prueba
window.testModal = function() {
    console.log('🧪 Testing modal...');
    showConfirmModal(
        'Modal de Prueba',
        'Este es un modal de prueba para verificar que el sistema funciona correctamente.',
        'info',
        'testAction',
        {testParam: 'testValue'}
    );
};
</script>
