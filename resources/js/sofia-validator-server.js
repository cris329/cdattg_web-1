const http = require('http');
const { validarCedula } = require('./sofia-validator');
const Router = require('./sofia-validator-server/router');
const { validateCedula, extractCedulaFromBody, extractCedulaFromQuery } = require('./sofia-validator-server/validators');
const { sendError, sendSuccess } = require('./sofia-validator-server/response');

const PORT = process.env.PORT || 3000;
const MAX_BODY_SIZE = parseInt(process.env.MAX_BODY_SIZE || '1048576', 10); // 1MB por defecto

/**
 * Manejar validación de cédula
 */
async function handleValidation(cedula) {
  const validation = validateCedula(cedula);
  if (!validation.valid) {
    return { statusCode: 400, payload: { status: 'error', message: validation.message } };
  }

  try {
    const resultado = await validarCedula(validation.value);
    return {
      statusCode: 200,
      payload: {
        status: 'ok',
        cedula: validation.value,
        resultado,
      },
    };
  } catch (error) {
    return {
      statusCode: 500,
      payload: {
        status: 'error',
        message: 'Ocurrió un error interno al validar la cédula.',
        detail: error.message,
      },
    };
  }
}

/**
 * Leer body de request POST
 */
function readBody(req) {
  return new Promise((resolve, reject) => {
    let body = '';
    req.on('data', chunk => {
      body += chunk;
      if (body.length > MAX_BODY_SIZE) {
        req.destroy(new Error('Payload demasiado grande.'));
        reject(new Error('Payload demasiado grande.'));
      }
    });
    req.on('end', () => resolve(body));
    req.on('error', reject);
  });
}

// Configurar router
const router = new Router();

// Ruta POST /validate
router.register('POST', '/validate', async (req, res) => {
  try {
    const body = await readBody(req);
    const cedula = extractCedulaFromBody(body);
    
    if (!cedula) {
      sendError(res, 400, 'El cuerpo de la solicitud debe ser JSON válido.');
      return;
    }

    const { statusCode, payload } = await handleValidation(cedula);
    if (statusCode === 200) {
      sendSuccess(res, payload);
    } else {
      sendError(res, statusCode, payload.message, payload.detail);
    }
  } catch (error) {
    sendError(res, 400, error.message);
  }
});

// Ruta GET /validate
router.register('GET', '/validate', async (req, res, url) => {
  const cedula = extractCedulaFromQuery(url);
  const { statusCode, payload } = await handleValidation(cedula);
  
  if (statusCode === 200) {
    sendSuccess(res, payload);
  } else {
    sendError(res, statusCode, payload.message, payload.detail);
  }
});

// Ruta GET /health
router.register('GET', '/health', async (req, res) => {
  sendSuccess(res, { status: 'ok' });
});

// Crear servidor
const server = http.createServer(async (req, res) => {
  try {
    const handled = await router.handle(req, res);
    if (!handled) {
      sendError(res, 404, 'Ruta no encontrada.');
    }
  } catch (error) {
    sendError(res, 500, 'Error inesperado en el servidor.', error.message);
  }
});

server.listen(PORT, () => {
  console.error(`🚀 Servidor Playwright escuchando en puerto ${PORT}`);
});


