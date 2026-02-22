/**
 * Tests E2E: Proceso Masivo de Tareas — TR-039 a TR-043
 *
 * TR-039: Supervisor accede a /tareas/proceso-masivo; empleado redirigido.
 * TR-040: Filtros (fecha, cliente, empleado, estado); Aplicar Filtros; total; error 1305 si fecha_desde > fecha_hasta.
 * TR-041: Checkboxes por fila; Seleccionar todos; Deseleccionar todos; contador.
 * TR-042: Botón Procesar; confirmación; mensaje éxito; lista actualizada.
 * TR-043: Botón Procesar deshabilitado sin selección.
 *
 * @see docs/hu-tareas/TR-039(SH)-acceso-al-proceso-masivo-de-tareas.md
 * @see docs/hu-tareas/TR-040(SH)-filtrado-de-tareas-para-proceso-masivo.md
 * @see docs/hu-tareas/TR-041(SH)-selección-múltiple-de-tareas.md
 * @see docs/hu-tareas/TR-042(SH)-procesamiento-masivo-de-tareas-cerrarreabrir.md
 * @see docs/hu-tareas/TR-043(SH)-validación-de-selección-para-procesamiento.md
 */

import { test, expect } from '@playwright/test';

const SUPERVISOR = {
  code: 'MGARCIA',
  password: 'password456',
};

const EMPLEADO = {
  code: 'JPEREZ',
  password: 'password123',
};

test.describe('Proceso Masivo de Tareas — TR-039 a TR-043', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000);
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible({ timeout: 15000 });
  });

  // ----- TR-039: Acceso solo supervisores -----
  test('TR-039: supervisor puede acceder a /tareas/proceso-masivo y ve la página', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/tareas/proceso-masivo');
    await expect(page).toHaveURL('/tareas/proceso-masivo', { timeout: 10000 });
    await expect(page.locator('[data-testid="procesoMasivo.page"]')).toBeVisible({ timeout: 15000 });
    await expect(page.getByRole('heading', { name: /proceso masivo de tareas/i })).toBeVisible();
    await expect(page.locator('[data-testid="procesoMasivo.filtros"]')).toBeVisible();
  });

  test('TR-039: empleado sin permisos es redirigido al acceder a /tareas/proceso-masivo', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO.password);
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/tareas/proceso-masivo');
    await expect(page).toHaveURL('/', { timeout: 10000 });
    await expect(page.locator('[data-testid="procesoMasivo.page"]')).not.toBeVisible();
  });

  // ----- TR-040: Filtros y total -----
  test('TR-040: supervisor puede aplicar filtros y ver total de tareas', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await page.goto('/tareas/proceso-masivo');
    await expect(page.locator('[data-testid="procesoMasivo.page"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="procesoMasivo.loading"]')).not.toBeVisible({ timeout: 15000 });

    await page.getByLabel(/fecha desde/i).fill('2025-01-01');
    await page.getByLabel(/fecha hasta/i).fill('2026-12-31');
    await page.click('[data-testid="procesoMasivo.aplicarFiltros"]');
    await expect(page.locator('[data-testid="procesoMasivo.loading"]')).not.toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="procesoMasivo.total"]')).toBeVisible();
  });

  test('TR-040: fecha_desde mayor que fecha_hasta muestra mensaje de error (1305)', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await page.goto('/tareas/proceso-masivo');
    await expect(page.locator('[data-testid="procesoMasivo.page"]')).toBeVisible({ timeout: 15000 });

    await page.getByLabel(/fecha desde/i).fill('2026-02-01');
    await page.getByLabel(/fecha hasta/i).fill('2026-01-15');
    await page.click('[data-testid="procesoMasivo.aplicarFiltros"]');
    await expect(page.locator('[data-testid="procesoMasivo.mensajeError"]')).toBeVisible({ timeout: 10000 });
  });

  // ----- TR-041: Selección múltiple -----
  test('TR-041: contador y seleccionar/deseleccionar todos', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await page.goto('/tareas/proceso-masivo');
    await expect(page.locator('[data-testid="procesoMasivo.page"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="procesoMasivo.loading"]')).not.toBeVisible({ timeout: 15000 });

    const contador = page.locator('[data-testid="procesoMasivo.contadorSeleccionadas"]');
    await expect(contador).toBeVisible();
    await expect(contador).toContainText('tareas seleccionadas');

    const table = page.locator('[data-testid="procesoMasivo.tabla"]');
    const empty = page.locator('[data-testid="procesoMasivo.empty"]');
    await expect(table.or(empty)).toBeVisible();
    if (await table.isVisible()) {
      await page.click('[data-testid="procesoMasivo.seleccionarTodos"]');
      await expect(page.locator('[data-testid="procesoMasivo.procesar"]')).toBeEnabled();
      await page.click('[data-testid="procesoMasivo.deseleccionarTodos"]');
      await expect(contador).toContainText('0 ');
    }
  });

  // ----- TR-042 y TR-043: Procesar deshabilitado sin selección; procesar con confirmación -----
  test('TR-043: botón Procesar está deshabilitado cuando no hay tareas seleccionadas', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await page.goto('/tareas/proceso-masivo');
    await expect(page.locator('[data-testid="procesoMasivo.page"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="procesoMasivo.loading"]')).not.toBeVisible({ timeout: 15000 });

    const procesarBtn = page.locator('[data-testid="procesoMasivo.procesar"]');
    await expect(procesarBtn).toBeVisible();
    await expect(procesarBtn).toBeDisabled();
  });

  test('TR-042: seleccionar tareas, Procesar y confirmar muestra mensaje de éxito', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await page.goto('/tareas/proceso-masivo');
    await expect(page.locator('[data-testid="procesoMasivo.page"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="procesoMasivo.loading"]')).not.toBeVisible({ timeout: 15000 });

    const table = page.locator('[data-testid="procesoMasivo.tabla"]');
    if (!(await table.isVisible())) {
      test.skip();
      return;
    }
    const firstCheckbox = page.locator('[data-testid^="procesoMasivo.checkboxTarea."]').first();
    if (!(await firstCheckbox.isVisible())) {
      test.skip();
      return;
    }
    await firstCheckbox.check();
    await expect(page.locator('[data-testid="procesoMasivo.procesar"]')).toBeEnabled();
    await page.click('[data-testid="procesoMasivo.procesar"]');
    await expect(page.locator('[data-testid="procesoMasivo.confirmModal-overlay"]')).toBeVisible({ timeout: 5000 });
    await page.click('[data-testid="procesoMasivo.confirmarProcesar"]');
    await expect(page.locator('[data-testid="procesoMasivo.mensajeExito"]')).toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-testid="procesoMasivo.mensajeExito"]')).toContainText(/procesaron \d+ registro/);
  });
});
