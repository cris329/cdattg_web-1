import Swal from 'sweetalert2';

function setupLimpiarHistorialOrdenes() {
    const button = document.getElementById('btn-limpiar-historial-ordenes');
    if (!button) {
        return;
    }

    button.addEventListener('click', () => {
        if (typeof Swal === 'undefined') {
            const confirmMessage = '¿Estás seguro de que deseas limpiar el historial de órdenes devueltas?';
            if (!window.confirm(confirmMessage)) {
                return;
            }

            const filasFallback = document.querySelectorAll('tr[data-estado="DEVUELTO"]');
            filasFallback.forEach((fila) => {
                fila.remove();
            });

            return;
        }

        Swal.fire({
            title: '¿Limpiar historial de órdenes?',
            text: 'Se ocultarán de esta lista las órdenes que ya están completamente devueltas.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#eb3349',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, limpiar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            const filasDevueltas = document.querySelectorAll('tr[data-estado="DEVUELTO"]');
            filasDevueltas.forEach((fila) => {
                fila.remove();
            });
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupLimpiarHistorialOrdenes);
} else {
    setupLimpiarHistorialOrdenes();
}



