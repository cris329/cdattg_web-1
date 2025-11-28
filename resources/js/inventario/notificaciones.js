/**
 * Script para gestión de notificaciones
 * Incluye funcionalidades de marcar como leída, eliminar y vaciar todas
 */

// Funciones auxiliares para notificaciones
function mostrarNotificacionExito(mensaje) {
    Swal.fire({
        icon: 'success',
        title: '¡Listo!',
        text: mensaje,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
    });
}

function mostrarNotificacionError(mensaje) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: mensaje,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });
}

function obtenerTokenCSRF() {
    return $('meta[name="csrf-token"]').attr('content');
}

function marcarNotificacionLeidaUI(button) {
    button.closest('.list-group-item').removeClass('list-group-item-light');
    button.replaceWith('<span class="badge badge-success mb-1" title="Leída"><i class="fas fa-check"></i></span>');
}

function marcarNotificacionComoLeida(notificationId, onSuccess, onError) {
    $.ajax({
        url: `/inventario/notificaciones/${notificationId}/read`,
        method: 'POST',
        data: {
            _token: obtenerTokenCSRF()
        },
        success: onSuccess,
        error: onError
    });
}

function manejarMarcarLeidaSuccess(response, button) {
    if (response.success) {
        marcarNotificacionLeidaUI(button);
        mostrarNotificacionExito('Notificación marcada como leída');
    }
}

function manejarMarcarLeidaError() {
    mostrarNotificacionError('No se pudo marcar la notificación como leída');
}

function redirigirAUrl(url) {
    globalThis.location.href = url;
}

function manejarAbrirNotificacionSuccess(response, targetUrl) {
    if (response.success) {
        redirigirAUrl(targetUrl);
        return;
    }
    mostrarNotificacionError('No se pudo marcar la notificación como leída');
}

function manejarAbrirNotificacionError() {
    mostrarNotificacionError('No se pudo marcar la notificación como leída');
}

function procesarAbrirNotificacion(targetUrl, notificationId) {
    const onSuccess = function(response) {
        manejarAbrirNotificacionSuccess(response, targetUrl);
    };
    
    marcarNotificacionComoLeida(notificationId, onSuccess, manejarAbrirNotificacionError);
}

function eliminarNotificacion(notificationId, listItem) {
    $.ajax({
        url: `/inventario/notificaciones/${notificationId}`,
        method: 'DELETE',
        data: {
            _token: obtenerTokenCSRF()
        },
        success: function() {
            manejarEliminarNotificacionSuccess(listItem);
        },
        error: function() {
            mostrarNotificacionError('No se pudo eliminar la notificación');
        }
    });
}

function manejarEliminarNotificacionSuccess(listItem) {
    listItem.fadeOut(300, function() {
        $(this).remove();
        
        if ($('.list-group-item').length === 0) {
            globalThis.location.reload();
        }
    });
    
    mostrarNotificacionExito('La notificación ha sido eliminada');
}

function confirmarEliminarNotificacion(notificationId, listItem) {
    Swal.fire({
        title: '¿Eliminar notificación?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            eliminarNotificacion(notificationId, listItem);
        }
    });
}

function mostrarSinNotificaciones() {
    Swal.fire({
        icon: 'info',
        title: 'Sin notificaciones',
        text: 'No hay notificaciones para eliminar',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000
    });
}

function vaciarTodasNotificaciones() {
    return $.ajax({
        url: '/inventario/notificaciones/vaciar-todas',
        method: 'DELETE',
        data: {
            _token: obtenerTokenCSRF()
        }
    }).then(response => {
        return response;
    }).catch(error => {
        Swal.showValidationMessage(`Error: ${error.statusText}`);
    });
}

function manejarVaciarTodasSuccess(result) {
    Swal.fire({
        icon: 'success',
        title: '¡Listo!',
        text: `${result.value.deleted} notificación(es) eliminada(s)`,
        confirmButtonText: 'Aceptar'
    }).then(() => {
        globalThis.location.reload();
    });
}

function confirmarVaciarTodas(totalNotifications) {
    Swal.fire({
        title: '¿Vaciar todas las notificaciones?',
        html: `Se eliminarán <strong>${totalNotifications}</strong> notificación(es).<br>Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, vaciar todo',
        cancelButtonText: 'Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: vaciarTodasNotificaciones,
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            manejarVaciarTodasSuccess(result);
        }
    });
}

function mostrarTodoAlDia() {
    Swal.fire({
        icon: 'info',
        title: 'Todo al día',
        text: 'No hay notificaciones sin leer',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000
    });
}

function marcarTodasComoLeidas() {
    $.ajax({
        url: '/inventario/notificaciones/read-all',
        method: 'POST',
        data: {
            _token: obtenerTokenCSRF()
        },
        success: function() {
            Swal.fire({
                icon: 'success',
                title: '¡Listo!',
                text: 'Todas las notificaciones marcadas como leídas',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                globalThis.location.reload();
            });
        },
        error: function() {
            mostrarNotificacionError('No se pudieron marcar las notificaciones');
        }
    });
}

function confirmarMarcarTodasLeidas(unreadCount) {
    Swal.fire({
        title: '¿Marcar todas como leídas?',
        text: `Se marcarán ${unreadCount} notificación(es) como leídas`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, marcar todas',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            marcarTodasComoLeidas();
        }
    });
}

$(document).ready(function() {
    // Marcar notificación como leída
    $('.mark-read').on('click', function() {
        const notificationId = $(this).data('id');
        const button = $(this);
        
        const onSuccess = function(response) {
            manejarMarcarLeidaSuccess(response, button);
        };
        
        marcarNotificacionComoLeida(notificationId, onSuccess, manejarMarcarLeidaError);
    });

    // Abrir recurso relacionado con la notificación
    $('.open-notification').on('click', function(event) {
        event.preventDefault();
        const targetUrl = $(this).data('url');
        const notificationId = $(this).data('id');
        const isUnread = $(this).data('unread') === true || $(this).data('unread') === 'true';

        if (!targetUrl) {
            return;
        }

        if (!notificationId || !isUnread) {
            redirigirAUrl(targetUrl);
            return;
        }

        procesarAbrirNotificacion(targetUrl, notificationId);
    });

    // Eliminar notificación individual
    $('.delete-notification').on('click', function() {
        const notificationId = $(this).data('id');
        const listItem = $(this).closest('.list-group-item');
        confirmarEliminarNotificacion(notificationId, listItem);
    });

    // Vaciar todas las notificaciones
    $('#vaciar-notificaciones').on('click', function() {
        const totalNotifications = $('.list-group-item').length;
        
        if (totalNotifications === 0) {
            mostrarSinNotificaciones();
            return;
        }
        
        confirmarVaciarTodas(totalNotifications);
    });

    // Marcar todas como leídas
    $('#marcar-todas-leidas').on('click', function() {
        const unreadCount = $('.list-group-item-light').length;
        
        if (unreadCount === 0) {
            mostrarTodoAlDia();
            return;
        }
        
        confirmarMarcarTodasLeidas(unreadCount);
    });
});
