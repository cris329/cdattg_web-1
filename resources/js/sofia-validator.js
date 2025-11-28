// Script para validar registro en SenaSofiaPlus
// Este archivo mantiene compatibilidad hacia atrás, pero ahora usa los módulos refactorizados
const { validarCedula } = require('./sofia-validator/index');

module.exports = { validarCedula };