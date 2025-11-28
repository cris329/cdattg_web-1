// Orquestador principal del validador Sofia
const {
  createBrowser,
  createContext,
  configurePage,
  loadFormPage,
  cleanupBrowserResources
} = require('./browser-config');
const { fillForm } = require('./form-filler');
const { detectModal } = require('./modal-detector');
const { parseResult } = require('./result-parser');
const { withRetry } = require('./retry-handler');

/**
 * Validar cédula en SenaSofiaPlus
 */
async function validarCedula(cedula, maxRetries = 3) {
  return await withRetry(async () => {
    let browser = null;
    let context = null;
    let page = null;

    try {
      console.error(`🔄 Validando cédula: ${cedula}`);

      // Configurar navegador
      browser = await createBrowser();
      context = await createContext(browser);
      page = await context.newPage();
      configurePage(page);

      // Cargar página del formulario
      await loadFormPage(page);

      // Llenar formulario
      await fillForm(page, cedula);

      // Detectar resultado
      const detectionResult = await detectModal(page);
      const resultado = parseResult(detectionResult);

      console.error(`✅ RESULTADO FINAL para ${cedula}: ${resultado}`);
      return resultado;
    } finally {
      // Limpiar recursos después de cada intento
      await cleanupBrowserResources(page, context, browser);
    }
  }, maxRetries).catch((error) => {
    console.error(`❌ Error validando cédula ${cedula}:`, error.message);
    return 'ERROR';
  });
}

// Función principal que se ejecuta desde línea de comandos
async function main() {
  const cedula = process.argv[2];

  if (!cedula) {
    process.stdout.write('ERROR: No cedula provided\n');
    process.exit(1);
  }

  try {
    const resultado = await validarCedula(cedula);
    process.stdout.write(resultado + '\n');
  } catch (error) {
    process.stdout.write('ERROR\n');
    process.exit(1);
  }
}

// Ejecutar si se llama directamente
if (require.main === module) {
  main();
}

module.exports = { validarCedula };

