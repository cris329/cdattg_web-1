/**
 * Gestor de días y horarios para programas complementarios
 * 
 * Este módulo maneja la selección de días de la semana y horarios
 * individuales para cada día en el formulario de programas complementarios.
 * 
 * @module complementarios/dias-horarios
 */

/**
 * Días por defecto (fallback). En producción se deben inyectar desde backend
 * usando IDs de `parametros_temas` (tema DIAS).
 *
 * @constant {Array<{id:number,nombre:string}>}
 */
const DIAS_SEMANA_FALLBACK = [
    { id: 0, nombre: 'Lunes' },
    { id: 0, nombre: 'Martes' },
    { id: 0, nombre: 'Miércoles' },
    { id: 0, nombre: 'Jueves' },
    { id: 0, nombre: 'Viernes' },
    { id: 0, nombre: 'Sábado' },
    { id: 0, nombre: 'Domingo' }
];

/**
 * Horarios predefinidos disponibles
 * @constant {Object}
 */
const HORARIOS_PREDEFINIDOS = {
    '00': { inicio: null, fin: null, label: 'Sin formación' },
    '1': { inicio: '07:00', fin: '13:00', label: '07:00 - 13:00' },
    '2': { inicio: '08:00', fin: '12:00', label: '08:00 - 12:00' },
    '3': { inicio: '08:00', fin: '16:00', label: '08:00 - 16:00' },
    '4': { inicio: '14:00', fin: '18:00', label: '14:00 - 18:00' },
    '5': { inicio: '18:00', fin: '23:00', label: '18:00 - 23:00' },
    'custom': { inicio: '', fin: '', label: 'Personalizado' }
};

/**
 * Clase para gestionar días y horarios
 */
class DiasHorariosManager {
    /**
     * @param {string} containerId - ID del contenedor donde se renderizará el componente
     * @param {Array<Object>} diasExistentes - Días existentes para edición (opcional)
     */
    constructor(containerId, diasExistentes = [], diasSemana = null) {
        this.containerId = containerId;
        this.container = document.getElementById(containerId);
        this.diasExistentes = this.mapearDiasExistentes(diasExistentes);
        this.diasSemana = this.normalizarDiasSemana(diasSemana);
        
        if (!this.container) {
            console.error(`No se encontró el contenedor con ID: ${containerId}`);
            return;
        }
        
        this.inicializar();
    }

    /**
     * Normaliza la lista de días recibida del backend.
     *
     * @param {Array<{id:number,nombre:string}>|null} diasSemana
     * @returns {Array<{id:number,nombre:string}>}
     */
    normalizarDiasSemana(diasSemana) {
        if (!Array.isArray(diasSemana) || diasSemana.length === 0) {
            return DIAS_SEMANA_FALLBACK;
        }

        return diasSemana
            .filter((d) => d && Number.isInteger(d.id) && typeof d.nombre === 'string')
            .map((d) => ({ id: d.id, nombre: d.nombre }));
    }

    /**
     * Mapea los días existentes a un formato más fácil de usar
     * @param {Array<Object>} diasExistentes - Array de días con dia_id, hora_inicio, hora_fin
     * @returns {Object} Mapa de dia_id a horario
     */
    mapearDiasExistentes(diasExistentes) {
        const mapa = {};
        
        if (!Array.isArray(diasExistentes)) {
            return mapa;
        }
        
        diasExistentes.forEach(dia => {
            if (dia.dia_id) {
                mapa[dia.dia_id] = {
                    hora_inicio: dia.hora_inicio || null,
                    hora_fin: dia.hora_fin || null
                };
            }
        });
        
        return mapa;
    }

    /**
     * Determina qué opción de horario corresponde a un horario dado
     * @param {string|null} horaInicio - Hora de inicio
     * @param {string|null} horaFin - Hora de fin
     * @returns {string} Clave del horario predefinido o 'custom'
     */
    determinarOpcionHorario(horaInicio, horaFin) {
        if (!horaInicio || !horaFin) {
            return '00';
        }
        
        // Normalizar formato de hora (remover segundos si existen)
        const inicio = horaInicio.substring(0, 5);
        const fin = horaFin.substring(0, 5);
        
        // Buscar en horarios predefinidos
        for (const [key, horario] of Object.entries(HORARIOS_PREDEFINIDOS)) {
            if (key === '00' || key === 'custom') {
                continue;
            }
            
            if (horario.inicio === inicio && horario.fin === fin) {
                return key;
            }
        }
        
        return 'custom';
    }

    /**
     * Inicializa el componente
     */
    inicializar() {
        this.renderizar();
        this.agregarEventListeners();
        this.cargarDiasExistentes();
    }

    /**
     * Renderiza la interfaz de días y horarios
     */
    renderizar() {
        let html = '<div class="dias-horarios-wrapper">';
        
        this.diasSemana.forEach(dia => {
            const diaExistente = this.diasExistentes[dia.id];
            const opcionSeleccionada = diaExistente 
                ? this.determinarOpcionHorario(diaExistente.hora_inicio, diaExistente.hora_fin)
                : '00';
            
            const mostrarCustom = opcionSeleccionada === 'custom';
            const valorInicio = mostrarCustom && diaExistente ? diaExistente.hora_inicio : '';
            const valorFin = mostrarCustom && diaExistente ? diaExistente.hora_fin : '';
            
            html += `
                <div class="form-row mb-3 dia-horario-row" data-dia-id="${dia.id}">
                    <div class="col-md-3">
                        <label class="form-label font-weight-semibold">${dia.nombre}</label>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control horario-select" 
                                data-dia-id="${dia.id}" 
                                name="horario_${dia.id}">
                            <option value="00" ${opcionSeleccionada === '00' ? 'selected' : ''}>Sin formación</option>
                            <option value="1" ${opcionSeleccionada === '1' ? 'selected' : ''}>07:00 - 13:00</option>
                            <option value="2" ${opcionSeleccionada === '2' ? 'selected' : ''}>08:00 - 12:00</option>
                            <option value="3" ${opcionSeleccionada === '3' ? 'selected' : ''}>08:00 - 16:00</option>
                            <option value="4" ${opcionSeleccionada === '4' ? 'selected' : ''}>14:00 - 18:00</option>
                            <option value="5" ${opcionSeleccionada === '5' ? 'selected' : ''}>18:00 - 23:00</option>
                            <option value="custom" ${opcionSeleccionada === 'custom' ? 'selected' : ''}>Personalizado</option>
                        </select>
                    </div>
                    <div class="col-md-5 custom-time-container" style="display: ${mostrarCustom ? 'block' : 'none'};">
                        <div class="input-group">
                            <input type="time" 
                                   class="form-control custom-inicio" 
                                   data-dia-id="${dia.id}"
                                   value="${valorInicio}"
                                   placeholder="Inicio">
                            <input type="time" 
                                   class="form-control custom-fin" 
                                   data-dia-id="${dia.id}"
                                   value="${valorFin}"
                                   placeholder="Fin">
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        this.container.innerHTML = html;
    }

    /**
     * Agrega los event listeners necesarios
     */
    agregarEventListeners() {
        // Listeners para selects de horario
        this.container.querySelectorAll('.horario-select').forEach(select => {
            select.addEventListener('change', (e) => {
                this.manejarCambioHorario(e.target);
            });
        });
        
        // Listeners para campos de tiempo personalizado
        this.container.querySelectorAll('.custom-inicio, .custom-fin').forEach(input => {
            input.addEventListener('change', () => {
                this.actualizarDiasJson();
            });
        });
    }

    /**
     * Maneja el cambio de selección de horario
     * @param {HTMLSelectElement} select - Elemento select que cambió
     */
    manejarCambioHorario(select) {
        const diaId = parseInt(select.dataset.diaId);
        const valor = select.value;
        const row = select.closest('.dia-horario-row');
        const customContainer = row.querySelector('.custom-time-container');
        const inicioInput = row.querySelector('.custom-inicio');
        const finInput = row.querySelector('.custom-fin');
        
        if (valor === 'custom') {
            customContainer.style.display = 'block';
            // Si no hay valores, establecer valores por defecto
            if (!inicioInput.value) {
                inicioInput.value = '08:00';
            }
            if (!finInput.value) {
                finInput.value = '16:00';
            }
        } else {
            customContainer.style.display = 'none';
            
            // Si no es "Sin formación", establecer valores del horario predefinido
            if (valor !== '00') {
                const horario = HORARIOS_PREDEFINIDOS[valor];
                if (horario && horario.inicio && horario.fin) {
                    inicioInput.value = horario.inicio;
                    finInput.value = horario.fin;
                }
            }
        }
        
        this.actualizarDiasJson();
    }

    /**
     * Carga los días existentes en los campos
     */
    cargarDiasExistentes() {
        if (Object.keys(this.diasExistentes).length === 0) {
            return;
        }
        
        this.diasSemana.forEach(dia => {
            const diaExistente = this.diasExistentes[dia.id];
            if (!diaExistente) {
                return;
            }
            
            const row = this.container.querySelector(`[data-dia-id="${dia.id}"]`);
            if (!row) {
                return;
            }
            
            const select = row.querySelector('.horario-select');
            const opcion = this.determinarOpcionHorario(
                diaExistente.hora_inicio,
                diaExistente.hora_fin
            );
            
            if (select) {
                select.value = opcion;
                this.manejarCambioHorario(select);
            }
        });
    }

    /**
     * Actualiza el campo oculto con los datos de días en formato JSON
     */
    actualizarDiasJson() {
        const dias = this.obtenerDiasSeleccionados();
        const jsonField = document.getElementById('dias_json');
        
        if (jsonField) {
            jsonField.value = JSON.stringify(dias);
        }
    }

    /**
     * Obtiene los días seleccionados en el formato esperado por Laravel
     * @returns {Array<Object>} Array de días con dia_id, hora_inicio, hora_fin
     */
    obtenerDiasSeleccionados() {
        const dias = [];
        
        this.diasSemana.forEach(dia => {
            const row = this.container.querySelector(`[data-dia-id="${dia.id}"]`);
            if (!row) {
                return;
            }
            
            const select = row.querySelector('.horario-select');
            if (!select) {
                return;
            }
            
            const valor = select.value;
            
            // Si es "Sin formación", no agregar este día
            if (valor === '00') {
                return;
            }
            
            let horaInicio;
            let horaFin;
            
            if (valor === 'custom') {
                const inicioInput = row.querySelector('.custom-inicio');
                const finInput = row.querySelector('.custom-fin');
                
                horaInicio = inicioInput ? inicioInput.value : null;
                horaFin = finInput ? finInput.value : null;
                
                // Si no está completo, no agregar
                if (!horaInicio || !horaFin) {
                    return;
                }
            } else {
                const horario = HORARIOS_PREDEFINIDOS[valor];
                if (horario && horario.inicio && horario.fin) {
                    horaInicio = horario.inicio;
                    horaFin = horario.fin;
                } else {
                    return;
                }
            }
            
            dias.push({
                dia_id: dia.id,
                hora_inicio: horaInicio,
                hora_fin: horaFin
            });
        });
        
        return dias;
    }

    /**
     * Prepara los datos para el envío del formulario
     * Agrega campos ocultos al formulario con los datos en el formato esperado por Laravel
     * @param {HTMLFormElement} form - Formulario al que se agregarán los campos
     */
    prepararEnvioFormulario(form) {
        // Eliminar campos ocultos previos si existen
        form.querySelectorAll('input[name^="dias["]').forEach(input => {
            input.remove();
        });
        
        const dias = this.obtenerDiasSeleccionados();
        
        dias.forEach((dia, index) => {
            const inputDiaId = document.createElement('input');
            inputDiaId.type = 'hidden';
            inputDiaId.name = `dias[${index}][dia_id]`;
            inputDiaId.value = dia.dia_id;
            
            const inputInicio = document.createElement('input');
            inputInicio.type = 'hidden';
            inputInicio.name = `dias[${index}][hora_inicio]`;
            inputInicio.value = dia.hora_inicio;
            
            const inputFin = document.createElement('input');
            inputFin.type = 'hidden';
            inputFin.name = `dias[${index}][hora_fin]`;
            inputFin.value = dia.hora_fin;
            
            form.appendChild(inputDiaId);
            form.appendChild(inputInicio);
            form.appendChild(inputFin);
        });
    }
}

// Exportar para uso global
if (typeof window !== 'undefined') {
    window.DiasHorariosManager = DiasHorariosManager;
    window.DIAS_SEMANA_FALLBACK = DIAS_SEMANA_FALLBACK;
    window.HORARIOS_PREDEFINIDOS = HORARIOS_PREDEFINIDOS;
}

