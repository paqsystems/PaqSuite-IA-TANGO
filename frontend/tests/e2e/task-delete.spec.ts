/**
 * Tests E2E: Eliminación de Tarea Propia (TR-030)
 *
 * Flujo: login → lista tareas → aplicar filtros → eliminar tarea → confirmar → verificar mensaje y desaparición.
 * Cancelar: abrir modal → cancelar → modal se cierra.
 *
 * @see TR-030(MH)-eliminación-de-tarea-propia.md
 */

import { test, expect } from '@playwright/test';

const TEST_USER = {
  code: 'JPEREZ',
  password: 'password123',
};

const FECHA_DESDE = '2025-01-01';
const FECHA_HASTA = '2026-12-31';

test.describe('Eliminación de Tarea Propia', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000);
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible({ timeout: 15000 });
    await page.fill('[data-testid="auth.login.usuarioInput"]', TEST_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', TEST_USER.password);
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
    await expect(page.locator('[data-testid="task.list.filters"]')).toBeVisible();
    const fechaDesdeInput = page.getByLabel(/fecha desde/i);
    const fechaHastaInput = page.getByLabel(/fecha hasta/i);
    await fechaDesdeInput.fill(FECHA_DESDE);
    await fechaHastaInput.fill(FECHA_HASTA);
    await page.click('[data-testid="task.list.filters.apply"]');
    await expect(page.locator('[data-testid="task.list.loading"]')).not.toBeVisible({ timeout: 15000 });
  }

  test('debe mostrar modal de confirmación al hacer clic en Eliminar', async ({ page }) => {
    await applyFiltersAndLoadList(page);
    const deleteButton = page.locator('[data-testid^="task.list.delete."]').first();
    if ((await deleteButton.count()) === 0) {
      test.skip();
      return;
    }
    await deleteButton.click();
    await expect(page.locator('[data-testid="task.delete.modal"]')).toBeVisible({ timeout: 5000 });
    await expect(page.locator('[data-testid="task.delete.confirm"]')).toBeVisible();
    await expect(page.locator('[data-testid="task.delete.cancel"]')).toBeVisible();
    await page.click('[data-testid="task.delete.cancel"]');
    await expect(page.locator('[data-testid="task.delete.modal"]')).not.toBeVisible({ timeout: 3000 });
  });

  test('debe eliminar tarea al confirmar en el modal', async ({ page }) => {
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
    await page.click('[data-testid="task.delete.confirm"]');
    await expect(page.locator('[data-testid="task.delete.modal"]')).not.toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="task.list.deleteSuccess"]')).toBeVisible({ timeout: 5000 });
    await expect(page.locator(`[data-testid="task.list.row.${taskId}"]`)).not.toBeVisible({ timeout: 5000 });
  });
});
