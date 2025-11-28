const { chromium } = require('playwright');
const config = require('./config');

/**
 * Crear y configurar el navegador
 */
async function createBrowser() {
  return await chromium.launch({
    headless: true,
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-dev-shm-usage',
      '--disable-accelerated-2d-canvas',
      '--no-first-run',
      '--no-zygote',
      '--single-process',
      '--disable-gpu'
    ]
  });
}

/**
 * Crear contexto del navegador
 */
async function createContext(browser) {
  return await browser.newContext({
    userAgent: 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    viewport: { width: 1280, height: 720 }
  });
}

/**
 * Configurar página con timeouts
 */
function configurePage(page) {
  page.setDefaultTimeout(config.PAGE_TIMEOUT);
  page.setDefaultNavigationTimeout(config.NAVIGATION_TIMEOUT);
}

/**
 * Cargar la página del formulario
 */
async function loadFormPage(page) {
  console.error(`🔗 Cargando página...`);
  const response = await page.goto(config.FORM_URL, {
    waitUntil: 'networkidle',
    timeout: config.NAVIGATION_TIMEOUT
  });

  if (!response.ok()) {
    throw new Error(`HTTP ${response.status()}: ${response.statusText()}`);
  }

  console.error('✅ Página cargada exitosamente');
  return page;
}

/**
 * Cerrar recursos del navegador
 */
async function cleanupBrowserResources(page, context, browser) {
  if (page) {
    try {
      await page.close();
    } catch (e) {
      console.warn('⚠️ Error cerrando página:', e.message);
    }
  }
  if (context) {
    try {
      await context.close();
    } catch (e) {
      console.warn('⚠️ Error cerrando contexto:', e.message);
    }
  }
  if (browser) {
    try {
      await browser.close();
    } catch (e) {
      console.warn('⚠️ Error cerrando navegador:', e.message);
    }
  }
}

module.exports = {
  createBrowser,
  createContext,
  configurePage,
  loadFormPage,
  cleanupBrowserResources
};

