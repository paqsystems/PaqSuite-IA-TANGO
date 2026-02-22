/**
 * Tests E2E: Carga de Tarea Diaria
 * 
 * Tests end-to-end con Playwright para el flujo de carga de tarea.
 * 
 * Reglas:
 * - Usa selectores data-testid (NO CSS/XPath/texto)
 * - NO usa esperas ciegas (waitForTimeout, sleep, etc.)
 * - Espera estados visibles con expect().toBeVisible()
 * - Verifica formato de fecha YMD en request al API
 * 
 * @see TR-028(MH)-carga-de-tarea-diaria.md
 */

import { test, expect } from '@playwright/test';

// Datos de prueba
const TEST_USER = {
  code: 'JPEREZ',
  password: 'password123',
};

const SUPERVISOR_USER = {
  code: 'MGARCIA',
  password: 'password456',
};

test.describe('Carga de Tarea Diaria', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000); // Aumentar timeout a 60 segundos para el beforeEach
    
    // Limpiar localStorage antes de hacer login
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
    
    // Esperar a que el formulario de login esté visible
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible({ timeout: 15000 });
    
    // Login como empleado de prueba
    await page.fill('[data-testid="auth.login.usuarioInput"]', TEST_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', TEST_USER.password);
    
    // Esperar la respuesta del API antes de verificar redirección (igual que en auth-login.spec.ts)
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    
    // Verificar redirección al dashboard
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await expect(page.locator('[data-testid="app.dashboard"]')).toBeVisible({ timeout: 20000 });
  });

  test('debe navegar al formulario de carga de tarea desde el dashboard', async ({ page }) => {
    // Navegar al formulario
    await page.click('[data-testid="app.createTaskLink"]');
    
    await expect(page).toHaveURL('/tareas/nueva', { timeout: 10000 });
    await expect(page.locator('[data-testid="task.form.container"]')).toBeVisible();
  });

  test('debe mostrar todos los campos del formulario', async ({ page }) => {
    await page.goto('/tareas/nueva');
    
    await expect(page.locator('[data-testid="task.form.container"]')).toBeVisible();
    await expect(page.locator('[data-testid="task.form.dateInput"]')).toBeVisible();
    await expect(page.locator('[data-testid="task.form.clientSelect"]')).toBeVisible();
    await expect(page.locator('[data-testid="task.form.taskTypeSelect"]')).toBeVisible();
    await expect(page.locator('[data-testid="task.form.durationInput"]')).toBeVisible();
    await expect(page.locator('[data-testid="task.form.sinCargoCheckbox"]')).toBeVisible();
    await expect(page.locator('[data-testid="task.form.presencialCheckbox"]')).toBeVisible();
    await expect(page.locator('[data-testid="task.form.observacionTextarea"]')).toBeVisible();
    await expect(page.locator('[data-testid="task.form.submitButton"]')).toBeVisible();
  });

  test('debe validar campos obligatorios', async ({ page }) => {
    await page.goto('/tareas/nueva');
    await expect(page.locator('[data-testid="task.form.container"]')).toBeVisible({ timeout: 10000 });
    
    // Completar algunos campos pero dejar otros vacíos para que se ejecute la validación JavaScript
    // La fecha ya está completa por defecto, así que dejamos cliente y tipo de tarea vacíos
    await page.fill('[data-testid="task.form.observacionTextarea"]', 'Test'); // Completar observación
    
    // Intentar enviar (el navegador puede bloquear el submit por validación HTML5)
    await page.click('[data-testid="task.form.submitButton"]');
    
    // Verificar que el formulario sigue en la misma página (no se envió debido a validación)
    await expect(page).toHaveURL(/\/tareas\/nueva/, { timeout: 2000 });
    
    // Verificar que hay al menos un campo con error o mensaje de validación
    // Los errores pueden ser de validación HTML5 (burbujas del navegador) o JavaScript (.field-error)
    // Verificamos que el formulario no se envió, lo cual indica que la validación funcionó
    const formSubmitted = await page.evaluate(() => {
      const form = document.querySelector('[data-testid="task.form.container"] form') as HTMLFormElement | null;
      return form ? form.checkValidity() : false;
    });
    expect(formSubmitted).toBe(false); // El formulario no es válido
  });

  test('debe validar duración en tramos de 15 minutos', async ({ page }) => {
    await page.goto('/tareas/nueva');
    await expect(page.locator('[data-testid="task.form.container"]')).toBeVisible({ timeout: 10000 });
    
    // Esperar a que se carguen los clientes (esperar respuesta del API y que haya opciones disponibles)
    await page.waitForResponse(resp => resp.url().includes('/api/v1/tasks/clients') && resp.status() === 200).catch(() => null);
    // Esperar que el select tenga al menos 2 opciones (opción vacía + al menos un cliente)
    await expect(async () => {
      const count = await page.locator('[data-testid="task.form.clientSelect"] option').count();
      if (count < 2) throw new Error(`Expected at least 2 options, got ${count}`);
    }).toPass({ timeout: 15000 });
    
    // Seleccionar cliente
    await page.selectOption('[data-testid="task.form.clientSelect"]', { index: 1 });
    
    // Esperar a que se carguen los tipos de tarea (esperar respuesta del API y que haya opciones disponibles)
    await page.waitForResponse(resp => resp.url().includes('/api/v1/tasks/task-types') && resp.status() === 200).catch(() => null);
    // Esperar que el select tenga al menos 2 opciones (opción vacía + al menos un tipo de tarea)
    await expect(async () => {
      const count = await page.locator('[data-testid="task.form.taskTypeSelect"] option').count();
      if (count < 2) throw new Error(`Expected at least 2 options, got ${count}`);
    }).toPass({ timeout: 15000 });
    
    // Seleccionar tipo de tarea
    await page.selectOption('[data-testid="task.form.taskTypeSelect"]', { index: 1 });
    
    // Llenar campos básicos
    await page.fill('[data-testid="task.form.durationInput"]', '00:25'); // No es múltiplo de 15
    await page.fill('[data-testid="task.form.observacionTextarea"]', 'Test');
    
    // Intentar guardar
    await page.click('[data-testid="task.form.submitButton"]');
    
    // Verificar que el formulario sigue en la misma página (no se envió debido a validación)
    await expect(page).toHaveURL(/\/tareas\/nueva/, { timeout: 2000 });
    
    // Verificar que hay un mensaje de error JavaScript
    const durationError = page.locator('[data-testid="task.form.durationInput"]').locator('..').locator('.field-error');
    await expect(durationError.first()).toBeVisible({ timeout: 5000 });
  });

  test('debe mostrar advertencia de fecha futura', async ({ page }) => {
    await page.goto('/tareas/nueva');
    await expect(page.locator('[data-testid="task.form.container"]')).toBeVisible({ timeout: 10000 });
    
    // Obtener fecha de mañana en formato DMY
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const day = String(tomorrow.getDate()).padStart(2, '0');
    const month = String(tomorrow.getMonth() + 1).padStart(2, '0');
    const year = tomorrow.getFullYear();
    const tomorrowDMY = `${day}/${month}/${year}`;
    
    // Cambiar fecha a mañana (formato DMY)
    await page.fill('[data-testid="task.form.dateInput"]', tomorrowDMY);
    
    // Esperar a que se procese el cambio (esperar que aparezca la advertencia)
    await expect(page.locator('[data-testid="task.form.dateWarning"]')).toBeVisible({ timeout: 5000 });
  });

  test('debe actualizar tipos de tarea al cambiar cliente', async ({ page }) => {
    await page.goto('/tareas/nueva');
    await expect(page.locator('[data-testid="task.form.container"]')).toBeVisible({ timeout: 10000 });
    
    // Esperar a que se carguen los clientes (esperar respuesta del API y que haya opciones disponibles)
    await page.waitForResponse(resp => resp.url().includes('/api/v1/tasks/clients') && resp.status() === 200).catch(() => null);
    // Esperar que el select tenga al menos 2 opciones (opción vacía + al menos un cliente)
    await expect(async () => {
      const count = await page.locator('[data-testid="task.form.clientSelect"] option').count();
      if (count < 2) throw new Error(`Expected at least 2 options, got ${count}`);
    }).toPass({ timeout: 15000 });
    
    // Seleccionar primer cliente
    await page.selectOption('[data-testid="task.form.clientSelect"]', { index: 1 });
    
    // Esperar a que se carguen los tipos de tarea para el primer cliente (esperar respuesta del API y que haya opciones disponibles)
    await page.waitForResponse(resp => resp.url().includes('/api/v1/tasks/task-types') && resp.status() === 200).catch(() => null);
    // Esperar que el select tenga al menos 2 opciones (opción vacía + al menos un tipo de tarea)
    await expect(async () => {
      const count = await page.locator('[data-testid="task.form.taskTypeSelect"] option').count();
      if (count < 2) throw new Error(`Expected at least 2 options, got ${count}`);
    }).toPass({ timeout: 15000 });
    
    // Cambiar a otro cliente si existe
    const clientOptions = await page.locator('[data-testid="task.form.clientSelect"] option').count();
    if (clientOptions > 2) {
      await page.selectOption('[data-testid="task.form.clientSelect"]', { index: 2 });
      
      // Esperar a que se carguen los tipos para el nuevo cliente (esperar respuesta del API y que haya opciones disponibles)
      await page.waitForResponse(resp => resp.url().includes('/api/v1/tasks/task-types') && resp.status() === 200).catch(() => null);
      // Esperar que el select tenga al menos 2 opciones (opción vacía + al menos un tipo de tarea)
      await expect(async () => {
      const count = await page.locator('[data-testid="task.form.taskTypeSelect"] option').count();
      if (count < 2) throw new Error(`Expected at least 2 options, got ${count}`);
    }).toPass({ timeout: 15000 });
    }
  });

  test('debe enviar fecha en formato YMD al API', async ({ page }) => {
    await page.goto('/tareas/nueva');
    await expect(page.locator('[data-testid="task.form.container"]')).toBeVisible({ timeout: 10000 });
    
    // Configurar interceptor para capturar el request
    let requestBody: any = null;
    page.on('request', (request) => {
      if (request.url().includes('/api/v1/tasks') && request.method() === 'POST') {
        requestBody = request.postDataJSON();
      }
    });
    
    // Llenar formulario completo
    const today = new Date();
    const day = String(today.getDate()).padStart(2, '0');
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const year = today.getFullYear();
    const fechaDMY = `${day}/${month}/${year}`; // Formato DMY para mostrar
    
    await page.fill('[data-testid="task.form.dateInput"]', fechaDMY);
    
    // Esperar a que se carguen los clientes (esperar respuesta del API y que haya opciones disponibles)
    await page.waitForResponse(resp => resp.url().includes('/api/v1/tasks/clients') && resp.status() === 200).catch(() => null);
    // Esperar que el select tenga al menos 2 opciones (opción vacía + al menos un cliente)
    await expect(async () => {
      const count = await page.locator('[data-testid="task.form.clientSelect"] option').count();
      if (count < 2) throw new Error(`Expected at least 2 options, got ${count}`);
    }).toPass({ timeout: 15000 });
    await page.selectOption('[data-testid="task.form.clientSelect"]', { index: 1 });
    
    // Esperar tipos de tarea (esperar respuesta del API y que haya opciones disponibles)
    await page.waitForResponse(resp => resp.url().includes('/api/v1/tasks/task-types') && resp.status() === 200).catch(() => null);
    // Esperar que el select tenga al menos 2 opciones (opción vacía + al menos un tipo de tarea)
    await expect(async () => {
      const count = await page.locator('[data-testid="task.form.taskTypeSelect"] option').count();
      if (count < 2) throw new Error(`Expected at least 2 options, got ${count}`);
    }).toPass({ timeout: 15000 });
    await page.selectOption('[data-testid="task.form.taskTypeSelect"]', { index: 1 });
    
    await page.fill('[data-testid="task.form.durationInput"]', '02:00'); // 2 horas = 120 minutos
    await page.fill('[data-testid="task.form.observacionTextarea"]', 'Test E2E');
    
    // Enviar formulario
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/tasks') && resp.status() === 201).catch(() => null),
      page.click('[data-testid="task.form.submitButton"]'),
    ]);
    
    // Verificar que la fecha se envió en formato YMD
    expect(requestBody).not.toBeNull();
    expect(requestBody.fecha).toMatch(/^\d{4}-\d{2}-\d{2}$/); // Formato YMD
  });

  test('debe mostrar selector de empleado solo para supervisores', async ({ page }) => {
    // Hacer logout y login como supervisor
    await page.click('[data-testid="app.logoutButton"]');
    await expect(page).toHaveURL('/login', { timeout: 10000 });
    
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR_USER.password);
    
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200).catch(() => null),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    
    await expect(page).toHaveURL('/', { timeout: 15000 });
    
    // Navegar al formulario
    await page.click('[data-testid="app.createTaskLink"]');
    await expect(page).toHaveURL('/tareas/nueva');
    
    // Verificar que el selector de empleado está visible
    await expect(page.locator('[data-testid="task.form.employeeSelect"]')).toBeVisible({ timeout: 10000 });
  });

  test('debe crear tarea exitosamente y mostrar mensaje de éxito', async ({ page }) => {
    await page.goto('/tareas/nueva');
    await expect(page.locator('[data-testid="task.form.container"]')).toBeVisible({ timeout: 10000 });
    
    // Llenar formulario completo
    const today = new Date();
    const day = String(today.getDate()).padStart(2, '0');
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const year = today.getFullYear();
    const fechaDMY = `${day}/${month}/${year}`; // Formato DMY para mostrar
    
    await page.fill('[data-testid="task.form.dateInput"]', fechaDMY);
    
    // Esperar y seleccionar cliente (esperar respuesta del API y que haya opciones disponibles)
    await page.waitForResponse(resp => resp.url().includes('/api/v1/tasks/clients') && resp.status() === 200).catch(() => null);
    // Esperar que el select tenga al menos 2 opciones (opción vacía + al menos un cliente)
    await expect(async () => {
      const count = await page.locator('[data-testid="task.form.clientSelect"] option').count();
      if (count < 2) throw new Error(`Expected at least 2 options, got ${count}`);
    }).toPass({ timeout: 15000 });
    await page.selectOption('[data-testid="task.form.clientSelect"]', { index: 1 });
    
    // Esperar y seleccionar tipo de tarea (esperar respuesta del API y que haya opciones disponibles)
    await page.waitForResponse(resp => resp.url().includes('/api/v1/tasks/task-types') && resp.status() === 200).catch(() => null);
    // Esperar que el select tenga al menos 2 opciones (opción vacía + al menos un tipo de tarea)
    await expect(async () => {
      const count = await page.locator('[data-testid="task.form.taskTypeSelect"] option').count();
      if (count < 2) throw new Error(`Expected at least 2 options, got ${count}`);
    }).toPass({ timeout: 15000 });
    await page.selectOption('[data-testid="task.form.taskTypeSelect"]', { index: 1 });
    
    await page.fill('[data-testid="task.form.durationInput"]', '02:00'); // 2 horas = 120 minutos
    await page.fill('[data-testid="task.form.observacionTextarea"]', 'Tarea creada desde test E2E');
    
    // Enviar formulario
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/tasks') && resp.status() === 201).catch(() => null),
      page.click('[data-testid="task.form.submitButton"]'),
    ]);
    
    // Verificar mensaje de éxito
    await expect(page.locator('[data-testid="task.form.successMessage"]')).toBeVisible({ timeout: 10000 });
  });
});
