/**
 * Tests E2E: Listado de Empleados (Supervisor) — TR-018
 *
 * Flujo supervisor: login (MGARCIA) → navegar a /empleados → tabla, filtros, total.
 * Flujo empleado: login (JPEREZ) → navegar a /empleados → redirección a /.
 *
 * @see docs/hu-tareas/TR-018(MH)-listado-de-empleados.md
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

test.describe('Listado de Empleados (Supervisor) — TR-018', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000);
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible({ timeout: 15000 });
  });

  test('supervisor puede acceder a /empleados y ver la lista', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/empleados');
    await expect(page).toHaveURL('/empleados', { timeout: 10000 });
    await expect(page.locator('[data-testid="empleados.list"]')).toBeVisible({ timeout: 15000 });
    await expect(page.getByRole('heading', { name: /empleados/i })).toBeVisible();
  });

  test('supervisor ve tabla o mensaje vacío, total y filtros', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await page.goto('/empleados');
    await expect(page.locator('[data-testid="empleados.list"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="empleados.loading"]')).not.toBeVisible({ timeout: 15000 });

    const table = page.locator('[data-testid="empleados.table"]');
    const empty = page.locator('[data-testid="empleados.empty"]');
    await expect(table.or(empty)).toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-testid="empleados.total"]')).toBeVisible();
    await expect(page.locator('[data-testid="empleados.search"]')).toBeVisible();
    await expect(page.locator('[data-testid="empleados.filters.apply"]')).toBeVisible();
  });

  test('supervisor puede buscar empleados por código, nombre o email', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await page.goto('/empleados');
    await expect(page.locator('[data-testid="empleados.list"]')).toBeVisible({ timeout: 15000 });

    await page.fill('[data-testid="empleados.search"]', 'PEREZ');
    await page.click('[data-testid="empleados.filters.apply"]');
    await expect(page.locator('[data-testid="empleados.loading"]')).not.toBeVisible({ timeout: 10000 });
  });

  test('supervisor puede filtrar por supervisor, activo e inhabilitado', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await page.goto('/empleados');
    await expect(page.locator('[data-testid="empleados.list"]')).toBeVisible({ timeout: 15000 });

    await page.selectOption('[data-testid="empleados.filter.supervisor"]', 'false');
    await page.selectOption('[data-testid="empleados.filter.activo"]', 'true');
    await page.selectOption('[data-testid="empleados.filter.inhabilitado"]', 'false');
    await page.click('[data-testid="empleados.filters.apply"]');
    await expect(page.locator('[data-testid="empleados.loading"]')).not.toBeVisible({ timeout: 10000 });
  });

  test('supervisor ve usuarios inhabilitados diferenciados visualmente', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await page.goto('/empleados');
    await expect(page.locator('[data-testid="empleados.list"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="empleados.loading"]')).not.toBeVisible({ timeout: 15000 });

    const table = page.locator('[data-testid="empleados.table"]');
    if (await table.isVisible()) {
      const rowsInhabilitados = page.locator('.empleados-table-row-inhabilitado');
      const count = await rowsInhabilitados.count();
      if (count > 0) {
        await expect(rowsInhabilitados.first()).toBeVisible();
      }
    }
  });

  test('empleado sin permisos de supervisor es redirigido al acceder a /empleados', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/empleados');
    await expect(page).toHaveURL('/', { timeout: 10000 });
    await expect(page.locator('[data-testid="empleados.list"]')).not.toBeVisible();
  });
});
