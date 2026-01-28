/**
 * Script específico para la página de índice de instructores
 */
import { TableActionsHandler } from '../modules/table-actions.js';
// AlertHandler eliminado para evitar duplicación de notificaciones

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar manejador de acciones de tabla
    const tableHandler = new TableActionsHandler('body', {
        deleteSelector: '.formulario-eliminar',
        tooltipSelector: '[data-toggle="tooltip"]',
        alertSelector: '.alert',
        autoHideAlerts: true,
        alertHideDelay: 5000
    });
    
    // AlertHandler eliminado - el sistema global de notificaciones maneja todo
    // const alertHandler = new AlertHandler({
    //     autoHide: true,
    //     hideDelay: 5000,
    //     alertSelector: '.alert'
    // });
    
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Configuración específica para instructores
    console.log('Página de instructores inicializada correctamente');
});
