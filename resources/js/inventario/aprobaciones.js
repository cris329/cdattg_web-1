/**
 * Script para gestión de aprobaciones de productos
 * Incluye funcionalidades para aprobar/rechazar productos individuales y órdenes completas
 */

import Swal from 'sweetalert2';

/**
 * Obtiene el token CSRF del meta tag
 * @returns {string} Token CSRF
 */
function obtenerTokenCSRF() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

/**
 * Crea y envía un formulario POST
 * @param {string} action - URL de acción del formulario
 * @param {Object} data - Datos adicionales como campos ocultos (key-value)
 */
function enviarFormularioPOST(action, data = {}) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = action;

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = obtenerTokenCSRF();
    form.appendChild(csrfInput);

    for (const [key, value] of Object.entries(data)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
}

/**
 * Aprobar un producto individual
 * @param {number} detalleId - ID del detalle de la orden
 * @param {string} nombreProducto - Nombre del producto a aprobar
 */
function aprobarProducto(detalleId, nombreProducto) {
    Swal.fire({
        title: '¿Aprobar producto?',
        html: `¿Está seguro de aprobar este producto?<br><strong>${nombreProducto}</strong><br><br>El stock será descontado automáticamente.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-check"></i> Sí, aprobar',
        cancelButtonText: '<i class="fas fa-times"></i> Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            enviarFormularioPOST(`/inventario/aprobaciones/${detalleId}/aprobar`);
        }
    });
}

/**
 * Rechazar un producto individual
 * @param {number} detalleId - ID del detalle de la orden
 * @param {string} nombreProducto - Nombre del producto a rechazar
 */
function rechazarProducto(detalleId, nombreProducto) {
    mostrarConfirmacionRechazo(`¿Está seguro de rechazar este producto?<br><strong>${nombreProducto}</strong>?`, (motivo) => {
        enviarFormularioPOST(`/inventario/aprobaciones/${detalleId}/rechazar`, {
            motivo_rechazo: motivo
        });
    });
}

/**
 * Aprobar una orden completa con todos sus productos
 * @param {number} ordenId - ID de la orden
 * @param {string} productos - Lista de productos en formato texto
 */
function aprobarOrden(ordenId, productos) {
    Swal.fire({
        title: '¿Aprobar toda la orden?',
        html: `¿Está seguro de aprobar TODOS los productos de esta orden?<br><br><strong>Productos:</strong> ${productos}<br><br>El stock será descontado automáticamente para todos los productos.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-check"></i> Sí, aprobar todo',
        cancelButtonText: '<i class="fas fa-times"></i> Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            enviarFormularioPOST(`/inventario/aprobaciones/orden/${ordenId}/aprobar`);
        }
    });
}

/**
 * Rechazar una orden completa con todos sus productos
 * @param {number} ordenId - ID de la orden
 * @param {string} productos - Lista de productos en formato texto
 */
function rechazarOrden(ordenId, productos) {
    mostrarConfirmacionRechazo(
        `¿Está seguro de rechazar TODOS los productos de esta orden?<br><br><strong>Productos:</strong> ${productos}`,
        (motivo) => {
            enviarFormularioPOST(`/inventario/aprobaciones/orden/${ordenId}/rechazar`, {
                motivo_rechazo: motivo
            });
        },
        'Explique el motivo del rechazo de toda la orden...'
    );
}

/**
 * Muestra confirmación de rechazo con campo de motivo obligatorio
 * @param {string} html - HTML del mensaje de confirmación
 * @param {Function} onConfirm - Callback cuando se confirma (recibe el motivo)
 * @param {string} placeholder - Placeholder para el textarea
 */
function mostrarConfirmacionRechazo(html, onConfirm, placeholder = 'Explique el motivo del rechazo...') {
    Swal.fire({
        title: html.includes('TODOS') ? '¿Rechazar toda la orden?' : '¿Rechazar producto?',
        html: html,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-times"></i> Sí, rechazar' + (html.includes('TODOS') ? ' todo' : ''),
        cancelButtonText: '<i class="fas fa-ban"></i> Cancelar',
        input: 'textarea',
        inputLabel: 'Motivo del rechazo (obligatorio)',
        inputPlaceholder: placeholder,
        inputAttributes: {
            'aria-label': 'Motivo del rechazo',
            'required': 'required'
        },
        inputValidator: (value) => {
            if (!value) {
                return 'Debe indicar el motivo del rechazo';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            onConfirm(result.value);
        }
    });
}

// Exponer funciones para uso en vistas Blade
globalThis.aprobarProducto = aprobarProducto;
globalThis.rechazarProducto = rechazarProducto;
globalThis.aprobarOrden = aprobarOrden;
globalThis.rechazarOrden = rechazarOrden;
