const config = require('./config');

/**
 * Ejecutar función con reintentos
 */
async function withRetry(fn, maxRetries = config.MAX_RETRIES) {
  for (let attempt = 1; attempt <= maxRetries; attempt++) {
    try {
      return await fn();
    } catch (error) {
      console.error(`❌ Error en intento ${attempt}/${maxRetries}:`, error.message);

      // Si es el último intento, devolver error
      if (attempt === maxRetries) {
        console.error('💥 Todos los intentos fallaron');
        throw error;
      }

      // Esperar antes del siguiente intento (backoff exponencial)
      const waitTime = calculateBackoff(attempt);
      console.error(`⏳ Esperando ${waitTime}ms antes del siguiente intento...`);
      await new Promise(resolve => setTimeout(resolve, waitTime));
    }
  }
}

/**
 * Calcular tiempo de espera para backoff exponencial
 */
function calculateBackoff(attempt) {
  const waitTime = config.RETRY_BACKOFF_BASE * Math.pow(2, attempt - 1);
  return Math.min(waitTime, config.RETRY_BACKOFF_MAX);
}

module.exports = {
  withRetry
};

