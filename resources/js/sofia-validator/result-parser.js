/**
 * Parsear resultado de la validación
 */
function parseResult(detectionResult) {
  if (detectionResult.type === 'modal') {
    return parseModalResult(detectionResult.text);
  } else {
    // No hubo modal de error - puede registrarse
    console.error(`✅ No se detectó modal de error (${detectionResult.type}) - usuario puede registrarse`);
    return 'NO_REGISTRADO';
  }
}

/**
 * Parsear resultado del modal
 */
function parseModalResult(modalText) {
  const textoLower = modalText.toLowerCase();
  console.error(`🔍 Procesando modal - Texto completo: "${modalText}"`);

  // Caso 1: Usuario ya existe y requiere cambio de documento
  if (isRequiresChange(textoLower)) {
    return 'REQUIERE_CAMBIO';
  }

  // Caso 2: Usuario ya existe y está registrado correctamente
  if (isAlreadyRegistered(textoLower)) {
    return 'YA_EXISTE';
  }

  // Caso 3: Otro tipo de error
  console.error(`⚠️ Modal con mensaje no reconocido: ${modalText}`);
  return 'DESCONOCIDO';
}

/**
 * Verificar si requiere cambio de documento
 */
function isRequiresChange(textoLower) {
  const hasExists = textoLower.includes('ya existe') || 
                   textoLower.includes('ya cuentas con un registro');
  
  const hasChange = textoLower.includes('actualizar tu documento') ||
                   textoLower.includes('requiere_cambio') ||
                   textoLower.includes('cambiar tu documento') ||
                   textoLower.includes('tarjeta de identidad');
  
  return hasExists && hasChange;
}

/**
 * Verificar si ya está registrado
 */
function isAlreadyRegistered(textoLower) {
  return textoLower.includes('ya existe') ||
         textoLower.includes('ya cuentas con un registro') ||
         textoLower.includes('cuenta registrada') ||
         textoLower.includes('múltiples registros') ||
         textoLower.includes('se encontraron múltiples');
}

module.exports = {
  parseResult
};

