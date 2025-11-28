const config = require('./config');

/**
 * Llenar el formulario completo
 */
async function fillForm(page, cedula) {
  await selectDocumentType(page);
  await fillCedula(page, cedula);
  await selectLocation(page);
  await selectBirthDate(page);
  await acceptTerms(page);
  await submitForm(page);
}

/**
 * Seleccionar tipo de documento
 */
async function selectDocumentType(page) {
  console.error('📋 Seleccionando tipo de documento...');
  await page.getByRole('form', { name: 'Crear cuenta en Betowa' })
    .getByRole('button').first().click();
  await page.getByRole('option', { name: config.FORM_DATA.documentType }).click();
}

/**
 * Llenar campo de cédula
 */
async function fillCedula(page, cedula) {
  console.error(`🧾 Llenando cédula: ${cedula}`);
  await page.getByRole('textbox').fill(cedula);
}

/**
 * Seleccionar ubicación
 */
async function selectLocation(page) {
  console.error('📍 Seleccionando ubicación...');
  await page.getByRole('button', { name: 'Seleccionar ubicación' }).click();
  await page.getByRole('textbox', { name: 'Buscar ciudad...' })
    .fill(config.FORM_DATA.location.search);
  await page.getByRole('button', { name: config.FORM_DATA.location.select }).click();
}

/**
 * Seleccionar fecha de nacimiento
 */
async function selectBirthDate(page) {
  console.error('📅 Seleccionando fecha de nacimiento...');
  await page.getByRole('button', { name: 'placeholder' }).click();
  await page.getByRole('button', { name: '2025' }).click();
  await page.getByRole('button', { name: config.FORM_DATA.birthDate.year }).click();
  
  await selectMonth(page);
  await page.getByRole('button', { name: config.FORM_DATA.birthDate.month }).click();
  await page.getByRole('button', { name: config.FORM_DATA.birthDate.day, exact: true }).click();
}

/**
 * Seleccionar mes (con detección dinámica)
 */
async function selectMonth(page) {
  console.error('🔍 Detectando mes actual en el selector...');
  const mesActual = await page.locator('button[name*="mes"], button[class*="month"], [class*="month"] button')
    .first()
    .innerText()
    .catch(() => null);
  
  if (mesActual) {
    console.error(`📆 Mes actual detectado: ${mesActual}`);
    await page.getByRole('button', { name: mesActual }).click();
  } else {
    console.error('⚠️ No se pudo detectar el mes actual, usando fallback');
    await selectMonthFallback(page);
  }
}

/**
 * Fallback para seleccionar mes
 */
async function selectMonthFallback(page) {
  for (const mes of config.MONTHS) {
    const existe = await page.getByRole('button', { name: mes }).count().catch(() => 0);
    if (existe > 0) {
      console.error(`📆 Usando mes fallback: ${mes}`);
      await page.getByRole('button', { name: mes }).click();
      break;
    }
  }
}

/**
 * Aceptar términos
 */
async function acceptTerms(page) {
  console.error('✅ Aceptando términos...');
  await page.getByRole('checkbox', { name: /Acepto Términos de uso/ }).check();
}

/**
 * Enviar formulario
 */
async function submitForm(page) {
  console.error('📤 Enviando formulario...');
  await page.getByRole('button', { name: 'Continuar →' }).click();
}

module.exports = {
  fillForm
};

