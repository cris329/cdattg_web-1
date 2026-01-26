/**
 * Script específico para la página de índice de guías de aprendizaje
 * Basado en la arquitectura de resultados_aprendizaje-index.js
 */
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Guías de Aprendizaje - Página inicializada');

    // Métodos para confirmación de resultados de aprendizaje
    window.confirmarAsociar = function(resultadoId, nombreResultado) {
        // Extraer código y nombre del texto "CÓDIGO - NOMBRE"
        const partes = nombreResultado.split(' - ');
        const codigo = partes[0] || resultadoId;
        const nombre = partes[1] || nombreResultado;
        
        showConfirmModal(
            'Asignar resultado',
            '¿Desea asignar este resultado a la guía?',
            'info',
            'asignarResultado',
            resultadoId,
            codigo,
            nombre
        );
    };

    window.confirmarDesasociar = function(resultadoId, nombreResultado) {
        // Extraer código y nombre del texto "CÓDIGO - NOMBRE"
        const partes = nombreResultado.split(' - ');
        const codigo = partes[0] || resultadoId;
        const nombre = partes[1] || nombreResultado;
        
        showConfirmModal(
            'Desasociar resultado',
            '¿Desea quitar este resultado de la guía?',
            'danger',
            'desasociarResultado',
            resultadoId,
            codigo,
            nombre
        );
    };

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

    // Inicialización de tooltips si existen
    if (typeof $ !== 'undefined' && $.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
    }

    // Auto-focus en campos de búsqueda si existen
    const searchInputs = document.querySelectorAll('input[wire\\:model][placeholder*="Buscar"]');
    if (searchInputs.length > 0) {
        searchInputs[0].focus();
    }

    // Manejo de alertas de sesión
    const sessionAlerts = document.querySelectorAll('.alert');
    sessionAlerts.forEach(alert => {
        if (alert.classList.contains('alert-success') || alert.classList.contains('alert-danger')) {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        }
    });

    console.log('✅ Guías de Aprendizaje - Todos los sistemas listos');
});
