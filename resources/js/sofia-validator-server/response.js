/**
 * Utilidades para respuestas HTTP
 */

/**
 * Enviar respuesta JSON
 */
function sendJson(res, statusCode, data) {
  // Verificar si los headers ya fueron enviados
  if (res.headersSent) {
    console.error('Intento de enviar respuesta después de que los headers ya fueron enviados');
    return;
  }
  
  const payload = JSON.stringify(data);
  res.writeHead(statusCode, {
    'Content-Type': 'application/json; charset=utf-8',
    'Content-Length': Buffer.byteLength(payload),
  });
  res.end(payload);
}

/**
 * Enviar error
 */
function sendError(res, statusCode, message, detail = null) {
  const payload = {
    status: 'error',
    message
  };
  if (detail) {
    payload.detail = detail;
  }
  sendJson(res, statusCode, payload);
}

/**
 * Enviar éxito
 */
function sendSuccess(res, data, statusCode = 200) {
  sendJson(res, statusCode, {
    status: 'ok',
    ...data
  });
}

module.exports = {
  sendJson,
  sendError,
  sendSuccess
};

