/**
 * Script específico para la página de índice de aprendices
 */
import { TableActionsHandler } from '../modules/table-actions.js';
import { AlertHandler } from '../modules/alert-handler.js';

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar manejador de acciones de tabla
    const tableHandler = new TableActionsHandler('body', {
        deleteSelector: '.formulario-eliminar',
        tooltipSelector: '[data-toggle="tooltip"]',
        alertSelector: '.alert',
        autoHideAlerts: true,
        alertHideDelay: 5000
    });
    
    // Inicializar manejador de alertas
    const alertHandler = new AlertHandler({
        autoHide: true,
        hideDelay: 5000,
        alertSelector: '.alert'
    });
    
    let searchTimeout;
    
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Búsqueda en tiempo real con debounce
    $('#searchAprendiz').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performAjaxSearch();
        }, 500);
    });

    // Botón de búsqueda
    $('#btnSearch').on('click', function() {
        performAjaxSearch();
    });

    // Función de búsqueda AJAX
    function performAjaxSearch() {
        const searchTerm = $('#searchAprendiz').val();
        const ficha = $('#filterFicha').val();
        const programa = $('#filterPrograma').val();
        const regional = $('#filterRegional').val();
        const estado = $('#filterEstado').val();

        // Construir URL con parámetros
        let url = window.location.pathname + '?';
        const params = [];
        
        if (searchTerm) params.push(`search=${encodeURIComponent(searchTerm)}`);
        if (ficha) params.push(`ficha=${encodeURIComponent(ficha)}`);
        if (programa) params.push(`programa=${encodeURIComponent(programa)}`);
        if (regional) params.push(`regional=${encodeURIComponent(regional)}`);
        if (estado !== '') params.push(`estado=${encodeURIComponent(estado)}`);

        url += params.join('&');

        // Actualizar URL sin recargar página
        history.pushState(null, null, url);

        // Realizar búsqueda via Livewire
        Livewire.find('aprendices.aprendiz-index').set('search', searchTerm);
        Livewire.find('aprendices.aprendiz-index').set('fichaFilter', ficha);
        Livewire.find('aprendices.aprendiz-index').set('programaFilter', programa);
        Livewire.find('aprendices.aprendiz-index').set('regionalFilter', regional);
        Livewire.find('aprendices.aprendiz-index').set('statusFilter', estado);
    }

    // Filtros
    $('#filterFicha, #filterPrograma, #filterRegional, #filterEstado').on('change', function() {
        performAjaxSearch();
    });

    // Limpiar filtros
    $('#btnClearFilters').on('click', function() {
        $('#searchAprendiz').val('');
        $('#filterFicha').val('');
        $('#filterPrograma').val('');
        $('#filterRegional').val('');
        $('#filterEstado').val('');
        
        performAjaxSearch();
    });

    // Paginación AJAX - Desactivado temporalmente para probar Livewire nativo
    // $(document).on('click', '.pagination-links a', function(e) {
    //     e.preventDefault();
    //     console.log('Clic en paginación detectado');
        
    //     const url = $(this).attr('href');
    //     if (!url) return;

    //     // Extraer parámetros de la URL
    //     const urlParams = new URLSearchParams(url.split('?')[1]);
        
    //     // Actualizar variables Livewire usando el método más directo
    //     try {
    //         // Obtener todos los componentes Livewire
    //         const components = Livewire.all();
    //         console.log('Componentes encontrados:', components.length);
            
    //         // Buscar el componente de aprendices
    //         let aprendizComponent = null;
    //         components.forEach(component => {
    //             if (component.name === 'aprendices.aprendiz-index' || 
    //                 component.name === 'aprendiz-index' ||
    //                 component.el?.querySelector('.vista-programas')) {
    //                 aprendizComponent = component;
    //                 console.log('Componente encontrado con nombre:', component.name);
    //             }
    //         });
            
    //         if (aprendizComponent) {
    //             console.log('Actualizando parámetros...');
    //             urlParams.forEach((value, key) => {
    //                 // No sobreescribir perPage con la paginación
    //                 if (key !== 'perPage') {
    //                     console.log(`Set ${key} = ${value}`);
    //                     aprendizComponent.set(key, value);
    //                 } else {
    //                     console.log(`Ignorando ${key} para mantener el valor actual`);
    //                 }
    //             });
    //         } else {
    //             console.log('Componente no encontrado, recargando página');
    //             window.location.href = url;
    //         }
    //     } catch (error) {
    //         console.error('Error:', error);
    //         window.location.href = url;
    //     }

    //     // Actualizar URL
    //     history.pushState(null, null, url);
    // });

    // Botones de acción
    $(document).on('click', '.btn-action', function(e) {
        const button = $(this);
        const action = button.data('action');
        const id = button.data('id');

        if (action === 'delete') {
            e.preventDefault();
            
            if (confirm('¿Está seguro de eliminar este aprendiz?')) {
                // Mostrar loading
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                
                // Ejecutar acción via Livewire
                Livewire.find('aprendices.aprendiz-index').call('delete', id)
                    .then(() => {
                        // La acción se completó
                    })
                    .catch(() => {
                        // Restaurar botón en caso de error
                        button.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                    });
            }
        }
    });

    // Toggle estado
    $(document).on('click', '.badge-toggle', function() {
        const button = $(this);
        const id = button.data('id');
        
        // Mostrar loading
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        // Ejecutar toggle via Livewire
        Livewire.find('aprendices.aprendiz-index').call('toggleStatus', id)
            .then(() => {
                // El estado se actualizó automáticamente
            })
            .catch(() => {
                // Restaurar botón en caso de error
                button.prop('disabled', false);
            });
    });

    // Modal handling
    $(document).on('click', '[data-toggle="modal"]', function(e) {
        e.preventDefault();
        
        const target = $(this).data('target');
        const modal = $(target);
        
        if (modal.length) {
            modal.modal('show');
        }
    });

    // Cerrar modales con Escape
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('.modal.show').modal('hide');
        }
    });

    // Select2 initialization
    function initializeSelect2() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }

    // Inicializar Select2
    initializeSelect2();

    // Re-inicializar después de actualizaciones Livewire
    Livewire.hook('component.updated', () => {
        initializeSelect2();
        $('[data-toggle="tooltip"]').tooltip();
    });

    // Toast notifications
    function showToast(message, type = 'info') {
        const toast = $(`
            <div class="toast-notification toast-${type}">
                <div class="toast-content">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                    <span>${message}</span>
                </div>
                <button type="button" class="toast-close">&times;</button>
            </div>
        `);

        $('body').append(toast);
        
        setTimeout(() => toast.addClass('show'), 100);
        
        setTimeout(() => {
            toast.removeClass('show');
            setTimeout(() => toast.remove(), 300);
        }, 4000);

        toast.find('.toast-close').on('click', function() {
            toast.removeClass('show');
            setTimeout(() => toast.remove(), 300);
        });
    }

    // Livewire event listeners - Comentado para evitar duplicación
    // Livewire.on('notify', function(data) {
    //     showToast(data.message, data.type || 'info');
    // });

    Livewire.on('success', function(message) {
        showToast(message, 'success');
    });

    Livewire.on('error', function(message) {
        showToast(message, 'error');
    });

    console.log('🎓 Aprendices index initialized successfully');
});
