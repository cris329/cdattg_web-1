const config = require('./config');

/**
 * Detectar modales en la página
 */
async function detectModal(page) {
  // Esperar un poco para que aparezca cualquier modal
  await page.waitForTimeout(config.MODAL_WAIT_TIMEOUT);
  console.error('⏳ Esperando respuesta del servidor...');

  // Buscar modal con múltiples selectores
  const modalText = await findModalBySelectors(page);
  if (modalText) {
    return { type: 'modal', text: modalText };
  }

  // Verificar texto de error en la página
  const errorText = await findErrorInPageText(page);
  if (errorText) {
    return { type: 'modal', text: `Error detectado: ${errorText}` };
  }

  // Verificar otros indicadores
  return await checkFormState(page);
}

/**
 * Buscar modal usando múltiples selectores
 */
async function findModalBySelectors(page) {
  for (const selector of config.MODAL_SELECTORS) {
    try {
      const elements = await page.locator(selector).all();
      for (const element of elements) {
        const isVisible = await element.isVisible().catch(() => false);
        if (isVisible) {
          const text = await element.innerText().catch(() => '');
          if (text && text.trim().length > 0) {
            console.error(`💬 Modal encontrado con selector ${selector}:\n${text}`);
            return text;
          }
        }
      }
    } catch (error) {
      // Ignorar errores de selector
      continue;
    }
  }
  return null;
}

/**
 * Buscar indicadores de error en el texto de la página
 */
async function findErrorInPageText(page) {
  const pageText = await page.innerText().catch(() => '');
  console.error(`📄 Texto completo de la página:\n${pageText}`);

  for (const indicator of config.ERROR_INDICATORS) {
    if (pageText.toLowerCase().includes(indicator)) {
      console.error(`⚠️ Texto de error encontrado en página: "${indicator}"`);
      return indicator;
    }
  }
  return null;
}

/**
 * Verificar estado del formulario
 */
async function checkFormState(page) {
  const hasEmailField = await page.locator('input[type="email"], input[name*="email"], input[placeholder*="correo"], input[placeholder*="email"]')
    .count()
    .catch(() => 0);
  
  const hasPasswordField = await page.locator('input[type="password"], input[name*="password"], input[placeholder*="contraseña"]')
    .count()
    .catch(() => 0);
  
  const hasContinueButton = await page.locator('button[name*="continuar"], button[name*="Continuar"], button[name*="siguiente"]')
    .count()
    .catch(() => 0);

  console.error(`🔍 Estado del formulario - Email: ${hasEmailField}, Password: ${hasPasswordField}, Continue: ${hasContinueButton}`);

  if (hasEmailField > 0 || hasPasswordField > 0) {
    console.error('📧 Campos de registro detectados - puede continuar');
    return { type: 'form_elements' };
  } else if (hasContinueButton === 0) {
    console.error("✅ Botón 'Continuar' desapareció - proceso avanzó");
    return { type: 'button_gone' };
  } else {
    console.error('⏰ Timeout alcanzado sin cambios claros');
    return { type: 'timeout' };
  }
}

module.exports = {
  detectModal
};

