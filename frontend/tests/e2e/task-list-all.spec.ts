/**
 * Tests E2E: Visualización de Lista de Todas las Tareas (Supervisor) — TR-034
 *
 * Flujo supervisor: login (MGARCIA) → navegar a /tareas/todas → tabla con columna Empleado, filtros, paginación.
 * Flujo empleado: login (JPEREZ) → navegar a /tareas/todas → redirección a /.
 *
 * @see docs/hu-tareas/TR-034(MH)-visualización-de-lista-de-todas-las-tareas-supervisor.md
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

const FECHA_DESDE = '2025-01-01';
const FECHA_HASTA = '2026-12-31';

test.describe('Lista de Todas las Tareas (Supervisor) — TR-034', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000);
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible({ timeout: 15000 });
  });

  test('supervisor puede acceder a /tareas/todas y ver la lista', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/tareas/todas');
    await expect(page).toHaveURL('/tareas/todas', { timeout: 10000 });
    await expect(page.locator('[data-testid="task.all.container"]')).toBeVisible({ timeout: 15000 });
    await expect(page.getByRole('heading', { name: /todas las tareas/i })).toBeVisible();
  });

  test('supervisor ve tabla o mensaje vacío y puede aplicar filtros', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await page.goto('/tareas/todas');
    await expect(page.locator('[data-testid="task.all.container"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="task.all.loading"]')).not.toBeVisible({ timeout: 15000 });

    const table = page.locator('[data-testid="task.all.table"]');
    const empty = page.locator('[data-testid="task.all.empty"]');
    await expect(table.or(empty)).toBeVisible({ timeout: 10000 });
    if (await table.isVisible()) {
      await expect(page.getByRole('columnheader', { name: /empleado/i })).toBeVisible();
    }

    const fechaDesdeInput = page.getByLabel(/fecha desde/i);
    const fechaHastaInput = page.getByLabel(/fecha hasta/i);
    await fechaDesdeInput.fill(FECHA_DESDE);
    await fechaHastaInput.fill(FECHA_HASTA);
    await page.click('[data-testid="task.list.filters.apply"]');
    await expect(page.locator('[data-testid="task.all.loading"]')).not.toBeVisible({ timeout: 15000 });
  });

  test('empleado sin permisos de supervisor es redirigido al acceder a /tareas/todas', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO.password);
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/tareas/todas');
    await expect(page).toHaveURL('/', { timeout: 10000 });
    await expect(page.locator('[data-testid="task.all.container"]')).not.toBeVisible();
  });
});
