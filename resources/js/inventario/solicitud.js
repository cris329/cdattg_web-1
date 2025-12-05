/**
 * solicitud.js - Funcionalidad para carga de datos del carrito y control de formulario de solicitud
 */

// Esperar a que el DOM esté listo
document.addEventListener('DOMContentLoaded', function () {
    cargarDatosCarrito();
    setupFormSubmit();
});

/**
 * Cargar datos del carrito desde sessionStorage
 */
function cargarDatosCarrito() {
    const carritoDataString = obtenerDatosCarrito();
    if (!carritoDataString) {
        setupFechaDevolucionToggle();
        return;
    }

    try {
        const data = JSON.parse(carritoDataString);
        aplicarDatosCarrito(data);
        
        // Solo mostrar alerta si viene desde el carrito
        if (debeCargarDesdeCarrito()) {
            mostrarAlertaCarritoCargado(data);
            // Limpiar el parámetro de la URL sin recargar
            const url = new URL(window.location);
            url.searchParams.delete('desde_carrito');
            window.history.replaceState({}, '', url);
        }
    } catch (error) {
        manejarErrorCargaCarrito(error);
    }

    setupFechaDevolucionToggle();
}

function debeCargarDesdeCarrito() {
    const urlParams = new URLSearchParams(globalThis.location.search);
    return urlParams.get('desde_carrito') === 'true';
}

function obtenerDatosCarrito() {
    return sessionStorage.getItem('carrito_data');
}

function aplicarDatosCarrito(data) {
    actualizarTotalesCarrito(data);
    mostrarResumenCarrito();
    renderizarItemsCarrito(data);
    inyectarHiddenCarrito(data);
}

function actualizarTotalesCarrito(data) {
    const totalProductosEl = document.getElementById('carrito-total-productos');
    const totalItemsEl = document.getElementById('carrito-total-items');

    if (totalProductosEl) totalProductosEl.textContent = data.totalProductos || 0;
    if (totalItemsEl) totalItemsEl.textContent = data.totalItems || 0;
}

function mostrarResumenCarrito() {
    const carritoResumenStatsEl = document.getElementById('carrito-resumen-stats');
    if (carritoResumenStatsEl) carritoResumenStatsEl.style.display = 'grid';
}

function renderizarItemsCarrito(data) {
    const carritoItemsTbodyEl = document.getElementById('carrito-items-tbody');
    if (!carritoItemsTbodyEl || !Array.isArray(data.items) || data.items.length === 0) {
        return;
    }

    const html = data.items.map(item => {
        const cantidad = item.quantity || item.cantidad || 1;
        return `
            <tr>
                <td><strong>${item.name || 'Producto'}</strong></td>
                <td class="text-center">
                    <span class="badge badge-primary">${cantidad}</span>
                </td>
            </tr>
        `;
    }).join('');

    carritoItemsTbodyEl.innerHTML = html;

    const carritoItemsCardEl = document.getElementById('carrito-items-card');
    if (carritoItemsCardEl) carritoItemsCardEl.classList.remove('d-none');
}

function inyectarHiddenCarrito(data) {
    const form = document.querySelector('#form-solicitud');
    if (!form) return;

    const itemsProcesados = (data.items || []).map(item => ({
        ...item,
        quantity: item.quantity || item.cantidad || 1
    }));

    const inputHidden = document.createElement('input');
    inputHidden.type = 'hidden';
    inputHidden.name = 'carrito';
    inputHidden.value = JSON.stringify(itemsProcesados);
    form.appendChild(inputHidden);
}

function mostrarAlertaCarritoCargado(data) {
    if (typeof Swal === 'undefined') return;

    Swal.fire({
        icon: 'success',
        title: 'Carrito cargado',
        html: `Se han cargado <strong>${data.totalProductos} producto(s)</strong> con <strong>${data.totalItems} ítems</strong><br><small class="text-muted">Completa los datos faltantes</small>`,
        timer: 3000,
        showConfirmButton: false
    });
}

function manejarErrorCargaCarrito(error) {
    console.error('Error al cargar datos del carrito:', error);
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al cargar los datos del carrito: ' + error.message
        });
    }
}

/**
 * Mostrar u ocultar fecha de devolución según el tipo
 */
function setupFechaDevolucionToggle() {
    const tipo = document.getElementById('tipo');
    const grupoFecha = document.getElementById('grupo-fecha-devolucion');
    const fechaDevolucion = document.getElementById('fecha_devolucion');

    function updateFechaEntregaVisibility() {
        if (!tipo || !grupoFecha || !fechaDevolucion) return;

        // Si el tipo es PRÉSTAMO (id=44)
        if (tipo.value === '44' || tipo.value.toLowerCase() === 'prestamo') {
            grupoFecha.classList.remove('d-none');
            fechaDevolucion.setAttribute('required', 'required');
        } else {
            grupoFecha.classList.add('d-none');
            fechaDevolucion.removeAttribute('required');
            fechaDevolucion.value = '';
        }
    }

    if (tipo) {
        tipo.addEventListener('change', updateFechaEntregaVisibility);
        updateFechaEntregaVisibility();
    }
}

/**
 * Configurar el evento submit del formulario
 */
function setupFormSubmit() {
    const form = document.getElementById('form-solicitud');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        // Obtener datos del carrito desde sessionStorage
        const carritoDataString = sessionStorage.getItem('carrito_data');
        let carritoItems = [];
        
        if (carritoDataString) {
            try {
                const data = JSON.parse(carritoDataString);
                carritoItems = data.items || [];
            } catch (error) {
                console.error('Error al parsear carrito_data:', error);
            }
        }
        
        // Asignar al campo hidden
        const carritoInput = document.getElementById('carrito');
        if (carritoInput) {
            carritoInput.value = JSON.stringify(carritoItems);
        }
        
        // NO limpiar aquí - se limpiará después del éxito en el servidor
    });
}
