/**
 * Tests E2E: Edición de Tarea Propia (TR-029)
 *
 * Flujo: login → lista tareas → aplicar filtros (fechas + botón Aplicar) → editar primera tarea → verificar.
 * Los datos se cargan al seleccionar filtros y presionar "Aplicar".
 *
 * @see TR-029(MH)-edición-de-tarea-propia.md
 */

import { test, expect } from '@playwright/test';

const TEST_USER = {
  code: 'JPEREZ',
  password: 'password123',
};

/** Rango de fechas amplio para que existan tareas (ajustar según datos de prueba) */
const FECHA_DESDE = '2025-01-01';
const FECHA_HASTA = '2026-12-31';

test.describe('Edición de Tarea Propia', () => {
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

  /** Aplica filtros de fecha y pulsa Aplicar para cargar datos en la lista */
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

  test('debe navegar a editar desde la lista si hay botón Editar', async ({ page }) => {
    await applyFiltersAndLoadList(page);
    const editButton = page.locator('[data-testid^="task.list.edit."]').first();
    const count = await editButton.count();
    if (count === 0) {
      test.skip();
      return;
    }
    await editButton.click();
    await expect(page).toHaveURL(/\/tareas\/\d+\/editar/, { timeout: 10000 });
    await expect(page.locator('[data-testid="task.edit.form"], [data-testid="task.form"]')).toBeVisible({ timeout: 5000 });
  });

  test('debe mostrar formulario de edición con título Editar Tarea', async ({ page }) => {
    await applyFiltersAndLoadList(page);
    const editButton = page.locator('[data-testid^="task.list.edit."]').first();
    if ((await editButton.count()) === 0) {
      test.skip();
      return;
    }
    await editButton.click();
    await expect(page).toHaveURL(/\/tareas\/\d+\/editar/, { timeout: 10000 });
    await expect(page.getByRole('heading', { name: /editar tarea/i })).toBeVisible({ timeout: 5000 });
  });
});
