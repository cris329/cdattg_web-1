/**
 * Validadores para el servidor
 */

/**
 * Validar cédula
 */
function validateCedula(cedula) {
  if (!cedula || typeof cedula !== 'string' || !cedula.trim()) {
    return {
      valid: false,
      message: 'Debe proporcionar una cédula válida.'
    };
  }
  return { valid: true, value: cedula.trim() };
}

/**
 * Extraer cédula del body JSON
 */
function extractCedulaFromBody(body) {
  try {
    const parsed = JSON.parse(body || '{}');
    return parsed.cedula ?? parsed.documento ?? parsed.identificacion;
  } catch (error) {
    // JSON inválido o body vacío - retornar null para indicar que no se pudo extraer
    console.error('Error parsing JSON body:', error.message);
    return null;
  }
}

/**
 * Extraer cédula de query params
 */
function extractCedulaFromQuery(url) {
  return url.searchParams.get('cedula') ||
         url.searchParams.get('documento') ||
         url.searchParams.get('identificacion');
}

module.exports = {
  validateCedula,
  extractCedulaFromBody,
  extractCedulaFromQuery
};

