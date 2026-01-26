// Funciones específicas para Guías de Aprendizaje

// Confirmar eliminación de guía
window.confirmarEliminarGuia = function(guiaId, nombreGuia) {
    const partes = nombreGuia.split(' - ');
    const codigo = partes[0] || guiaId;
    const nombre = partes[1] || nombreGuia;
    
    showConfirmModal(
        'Eliminar Guía de Aprendizaje',
        '¿Desea eliminar esta guía de aprendizaje? Esta acción no se puede deshacer.',
        'danger',
        'eliminarGuia',
        guiaId,
        codigo,
        nombre
    );
};

// Confirmar cambio de estado
window.confirmarCambiarEstado = function(guiaId, nombreGuia, nuevoEstado) {
    const partes = nombreGuia.split(' - ');
    const codigo = partes[0] || guiaId;
    const nombre = partes[1] || nombreGuia;
    const accion = nuevoEstado ? 'activar' : 'desactivar';
    
    showConfirmModal(
        `${accion.charAt(0).toUpperCase() + accion.slice(1)} Guía`,
        `¿Desea ${accion} esta guía de aprendizaje?`,
        nuevoEstado ? 'success' : 'warning',
        'cambiarEstado',
        { id: guiaId, estado: nuevoEstado },
        codigo,
        nombre
    );
};

// Confirmar gestión de resultados
window.confirmarGestionarResultados = function(guiaId, nombreGuia) {
    const partes = nombreGuia.split(' - ');
    const codigo = partes[0] || guiaId;
    const nombre = partes[1] || nombreGuia;
    
    showConfirmModal(
        'Gestionar Resultados',
        '¿Desea gestionar los resultados de aprendizaje de esta guía?',
        'info',
        'gestionarResultados',
        guiaId,
        codigo,
        nombre
    );
};
