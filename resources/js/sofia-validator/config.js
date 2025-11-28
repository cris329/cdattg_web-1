// Configuración del validador Sofia
module.exports = {
  // URL del formulario
  FORM_URL: 'https://betowa.sena.edu.co/registrarse',
  
  // Configuración de timeouts
  PAGE_TIMEOUT: 30000,
  NAVIGATION_TIMEOUT: 30000,
  MODAL_WAIT_TIMEOUT: 12000,
  
  // Configuración de reintentos
  MAX_RETRIES: 3,
  RETRY_BACKOFF_BASE: 1000,
  RETRY_BACKOFF_MAX: 5000,
  
  // Datos del formulario (hardcoded por ahora)
  FORM_DATA: {
    documentType: 'Cédula de Ciudadanía',
    location: {
      search: 'san jose del gua',
      select: 'SAN JOSÉ DEL GUAVIARE'
    },
    birthDate: {
      year: '2005',
      month: 'Abril',
      day: '9'
    }
  },
  
  // Selectores de modales
  MODAL_SELECTORS: [
    'div[role="dialog"]',
    '.modal',
    '[class*="modal"]',
    '[id*="modal"]',
    '.swal2-popup',
    '.alert',
    '[class*="alert"]',
    '.notification',
    '.toast',
    '.error',
    '[class*="error"]'
  ],
  
  // Indicadores de error en texto
  ERROR_INDICATORS: [
    'ya existe',
    'ya cuentas con un registro',
    'cuenta registrada',
    'documento registrado',
    'usuario ya registrado'
  ],
  
  // Meses en español
  MONTHS: [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
  ]
};

