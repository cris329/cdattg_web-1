// ===== GLOBAL NOTIFICATIONS SYSTEM =====

// Sistema de notificaciones global para todo el proyecto
console.log('🚀 Global notifications script loaded');

// Variable para evitar duplicación de listeners
let listenerSetup = false;

// Función global para mostrar notificaciones
function showGlobalNotify(message, type = 'success') {
    console.log('showGlobalNotify called:', message, type);
    
    // 🔥 SANITIZAR MENSAJE - Evitar HTML injection
    if (typeof message !== 'string') {
        message = String(message);
    }
    
    // Eliminar cualquier etiqueta HTML
    message = message.replace(/<[^>]*>/g, '').trim();
    
    // Limitar longitud para evitar desbordamiento
    if (message.length > 200) {
        message = message.substring(0, 200) + '...';
    }
    
    const container = document.getElementById('notify-container');
    console.log('Container found:', !!container);
    
    if (!container) {
        console.error('Notify container not found');
        return;
    }

    const div = document.createElement('div');
    div.className = `notify notify-${type}`;
    div.textContent = message; // 🔥 USAR textContent EN VEZ DE innerHTML

    console.log('Created notify element:', div);
    container.appendChild(div);
    console.log('Notify appended to container');

    // Animación de entrada rápida y sutil
    setTimeout(() => {
        div.style.opacity = '1';
        div.style.transform = 'translateX(0)';
    }, 50);

    // Auto-eliminar después de 2.5 segundos
    setTimeout(() => {
        div.style.opacity = '0';
        div.style.transform = 'translateX(100%)';
        setTimeout(() => div.remove(), 200);
    }, 2500);
}

// Configurar listener para Livewire 3 - SOLO UN LISTENER
function setupLivewireListener() {
    if (listenerSetup) {
        console.log('⚠️ Listener already setup, skipping');
        return;
    }
    
    console.log('🔥 Setting up Livewire notify listener');
    
    // Bandera para evitar duplicaciones
    let lastNotifyTime = 0;
    const NOTIFY_DEBOUNCE_TIME = 100; // 100ms
    
    // Configurar nuevo listener (Livewire 3 no tiene .off)
    Livewire.on('notify', (data) => {
        const currentTime = Date.now();
        
        // Evitar duplicaciones rápidas
        if (currentTime - lastNotifyTime < NOTIFY_DEBOUNCE_TIME) {
            console.log('🚫 Duplicate notify prevented');
            return;
        }
        lastNotifyTime = currentTime;
        
        console.log('📢 Livewire notify received:', data);
        console.log('Data type:', typeof data);
        console.log('Data keys:', data ? Object.keys(data) : 'null');
        
        // Extraer mensaje y tipo del evento
        let message, type;
        
        if (typeof data === 'string') {
            // Si es string, puede venir como JSON string
            try {
                const parsed = JSON.parse(data);
                message = parsed.message || parsed.detail?.message || 'Notificación';
                type = parsed.type || parsed.detail?.type || 'success';
            } catch (e) {
                console.log('JSON parse error:', e);
                message = 'Notificación';
                type = 'success';
            }
        } else if (data && typeof data === 'object') {
            // Si es objeto - intentar múltiples formas de acceder al mensaje
            message = data.message || data.detail?.message || data[0]?.message || 'Notificación';
            type = data.type || data.detail?.type || data[0]?.type || 'success';
            console.log('Object message extraction attempts:', {
                'data.message': data.message,
                'data.detail?.message': data.detail?.message,
                'data[0]?.message': data[0]?.message,
                'final': message
            });
        } else {
            // Fallback
            console.log('Using fallback - data type:', typeof data);
            message = 'Notificación';
            type = 'success';
        }
        
        console.log('Final message:', message, 'Final type:', type);
        showGlobalNotify(message, type);
    });
    
    listenerSetup = true;
    console.log('Livewire listener setup complete');
}

// Configurar listener cuando Livewire esté listo
if (window.Livewire) {
    console.log('⚡ Livewire already initialized - setting up listener');
    setupLivewireListener();
} else {
    console.log('🔄 Livewire not initialized yet, waiting for livewire:init');
    document.addEventListener('livewire:init', () => {
        console.log('🔥 Livewire initialized - setting up listener');
        setupLivewireListener();
    });
}

// También hacer la función disponible globalmente para uso manual
window.showNotify = showGlobalNotify;

// Función helper para tipos comunes
window.showSuccess = (message) => showGlobalNotify(message, 'success');
window.showError = (message) => showGlobalNotify(message, 'error');
window.showWarning = (message) => showGlobalNotify(message, 'warning');

// Test manual - puedes usar esto en la consola para probar
window.testNotify = () => {
    console.log('🧪 Testing notification system...');
    showGlobalNotify('Esta es una prueba', 'success');
};
