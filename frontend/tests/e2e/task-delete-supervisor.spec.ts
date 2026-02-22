/**
 * Tests E2E: Eliminación de Tarea por Supervisor (TR-032)
 *
 * Flujo: login como supervisor (MGARCIA) → lista tareas → eliminar tarea (de otro) → modal con empleado → confirmar → verificar.
 *
 * @see TR-032(MH)-eliminación-de-tarea-supervisor.md
 */

import { test, expect } from '@playwright/test';

const SUPERVISOR = {
  code: 'MGARCIA',
  password: 'password456',
};

const FECHA_DESDE = '2025-01-01';
const FECHA_HASTA = '2026-12-31';

test.describe('Eliminación de Tarea (Supervisor)', () => {
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

  test('debe mostrar modal con información del empleado al eliminar (supervisor)', async ({ page }) => {
    await applyFiltersAndLoadList(page);
    const deleteButton = page.locator('[data-testid^="task.list.delete."]').first();
    if ((await deleteButton.count()) === 0) {
      test.skip();
      return;
    }
    await deleteButton.click();
    await expect(page.locator('[data-testid="task.delete.modal"]')).toBeVisible({ timeout: 5000 });
    await expect(page.locator('[data-testid="task.delete.employee"]')).toBeVisible({ timeout: 5000 });
    await expect(page.locator('[data-testid="task.delete.confirm"]')).toBeVisible();
    await page.click('[data-testid="task.delete.cancel"]');
    await expect(page.locator('[data-testid="task.delete.modal"]')).not.toBeVisible({ timeout: 3000 });
  });

  test('debe eliminar tarea de otro al confirmar (supervisor)', async ({ page }) => {
    await applyFiltersAndLoadList(page);
    const deleteButton = page.locator('[data-testid^="task.list.delete."]').first();
    if ((await deleteButton.count()) === 0) {
      test.skip();
      return;
    }
    const rowTestId = await deleteButton.getAttribute('data-testid');
    const taskId = rowTestId?.replace('task.list.delete.', '') ?? '';
    await deleteButton.click();
    await expect(page.locator('[data-testid="task.delete.modal"]')).toBeVisible({ timeout: 5000 });
    const deletePromise = page.waitForResponse(
      resp => resp.url().match(/\/api\/v1\/tasks\/\d+$/) !== null && resp.request().method() === 'DELETE' && resp.status() === 200,
      { timeout: 15000 }
    );
    await page.click('[data-testid="task.delete.confirm"]');
    await deletePromise;
    await expect(page.locator('[data-testid="task.delete.modal"]')).not.toBeVisible({ timeout: 5000 });
    await expect(page.locator('[data-testid="task.list.deleteSuccess"]')).toBeVisible({ timeout: 5000 });
    await expect(page.locator(`[data-testid="task.list.row.${taskId}"]`)).not.toBeVisible({ timeout: 5000 });
  });
});
