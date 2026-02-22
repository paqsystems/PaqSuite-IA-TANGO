/**
 * Tests E2E: Visualización de Lista de Tareas Propias
 *
 * Tests end-to-end con Playwright para el flujo de lista de tareas.
 * Reglas: data-testid, sin waits ciegos, expect sobre estado visible.
 *
 * @see TR-033(MH)-visualización-de-lista-de-tareas-propias.md
 */

import { test, expect } from '@playwright/test';

const TEST_USER = {
  code: 'JPEREZ',
  password: 'password123',
};

test.describe('Lista de Tareas Propias', () => {
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

  test('debe navegar a Mis Tareas desde el dashboard', async ({ page }) => {
    await page.click('[data-testid="app.myTasksLink"]');
    await expect(page).toHaveURL('/tareas', { timeout: 10000 });
    await expect(page.locator('[data-testid="task.list.container"]')).toBeVisible();
  });

  test('debe mostrar filtros y contenedor de lista', async ({ page }) => {
    await page.goto('/tareas');
    await expect(page.locator('[data-testid="task.list.container"]')).toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-testid="task.list.filters"]')).toBeVisible();
    await expect(page.locator('[data-testid="task.list.totals"]')).toBeVisible();
  });

  test('debe mostrar tabla o estado vacío según datos', async ({ page }) => {
    await page.goto('/tareas');
    await expect(page.locator('[data-testid="task.list.container"]')).toBeVisible({ timeout: 10000 });
    const table = page.locator('[data-testid="task.list.table"]');
    const empty = page.locator('[data-testid="task.list.empty"]');
    const loading = page.locator('[data-testid="task.list.loading"]');
    await expect(loading).not.toBeVisible();
    const hasTable = await table.isVisible();
    const hasEmpty = await empty.isVisible();
    expect(hasTable || hasEmpty).toBe(true);
  });
});
