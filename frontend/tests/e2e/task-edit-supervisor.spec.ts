/**
 * Tests E2E: Edición de Tarea por Supervisor (TR-031)
 *
 * Flujo: login como supervisor (MGARCIA) → lista tareas → editar tarea → selector de empleado visible → guardar.
 * Opcional: tarea cerrada → error 2110.
 *
 * @see TR-031(MH)-edición-de-tarea-supervisor.md
 */

import { test, expect } from '@playwright/test';

const SUPERVISOR = {
  code: 'MGARCIA',
  password: 'password456',
};

const FECHA_DESDE = '2025-01-01';
const FECHA_HASTA = '2026-12-31';

test.describe('Edición de Tarea (Supervisor)', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000);
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible({ timeout: 15000 });
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await expect(page.locator('[data-testid="app.dashboard"]')).toBeVisible({ timeout: 20000 });
  });

  async function applyFiltersAndLoadList(page: import('@playwright/test').Page) {
    await page.goto('/tareas');
    await expect(page.locator('[data-testid="task.list.container"]')).toBeVisible({ timeout: 10000 });
    const fechaDesdeInput = page.getByLabel(/fecha desde/i);
    const fechaHastaInput = page.getByLabel(/fecha hasta/i);
    await fechaDesdeInput.fill(FECHA_DESDE);
    await fechaHastaInput.fill(FECHA_HASTA);
    await page.click('[data-testid="task.list.filters.apply"]');
    await expect(page.locator('[data-testid="task.list.loading"]')).not.toBeVisible({ timeout: 15000 });
  }

  test('debe mostrar selector de empleado en formulario de edición (supervisor)', async ({ page }) => {
    await applyFiltersAndLoadList(page);
    const editButton = page.locator('[data-testid^="task.list.edit."]').first();
    if ((await editButton.count()) === 0) {
      test.skip();
      return;
    }
    await editButton.click();
    await expect(page).toHaveURL(/\/tareas\/\d+\/editar/, { timeout: 10000 });
    await expect(page.locator('[data-testid="task.edit.form"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="task.edit.employeeSelector"]')).toBeVisible({ timeout: 5000 });
    await expect(page.locator('[data-testid="task.form.employeeSelect"]')).toBeVisible({ timeout: 5000 });
  });

  test('debe actualizar tarea al guardar (supervisor puede editar cualquier tarea)', async ({ page }) => {
    await applyFiltersAndLoadList(page);
    const editButton = page.locator('[data-testid^="task.list.edit."]').first();
    if ((await editButton.count()) === 0) {
      test.skip();
      return;
    }
    await editButton.click();
    await expect(page.locator('[data-testid="task.edit.form"]')).toBeVisible({ timeout: 15000 });
    const observacionInput = page.locator('[data-testid="task.form.observacionTextarea"]').or(page.getByLabel(/observación/i));
    await observacionInput.first().fill('Editado por supervisor TR-031');
    // Esperar PUT 200 y luego redirección a /tareas (el mensaje de éxito es fugaz ~1.5s)
    const putPromise = page.waitForResponse(
      resp => resp.url().match(/\/api\/v1\/tasks\/\d+$/) !== null && resp.request().method() === 'PUT' && resp.status() === 200,
      { timeout: 15000 }
    );
    await page.click('button[type="submit"]');
    await putPromise;
    await expect(page).toHaveURL('/tareas', { timeout: 10000 });
  });
});
