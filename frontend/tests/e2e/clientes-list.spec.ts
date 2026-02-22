/**
 * Tests E2E: Listado de Clientes (Supervisor) — TR-008
 *
 * Flujo supervisor: login (MGARCIA) → navegar a /clientes → tabla, filtros, total.
 * Flujo empleado: login (JPEREZ) → navegar a /clientes → redirección a /.
 *
 * @see docs/hu-tareas/TR-008(MH)-listado-de-clientes.md
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

test.describe('Listado de Clientes (Supervisor) — TR-008', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000);
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible({ timeout: 15000 });
  });

  test('supervisor puede acceder a /clientes y ver la lista', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/clientes');
    await expect(page).toHaveURL('/clientes', { timeout: 10000 });
    await expect(page.locator('[data-testid="clientes.list"]')).toBeVisible({ timeout: 15000 });
    await expect(page.getByRole('heading', { name: /clientes/i })).toBeVisible();
  });

  test('supervisor ve tabla o mensaje vacío, total y filtros', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await page.goto('/clientes');
    await expect(page.locator('[data-testid="clientes.list"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="clientes.loading"]')).not.toBeVisible({ timeout: 15000 });

    const table = page.locator('[data-testid="clientes.table"]');
    const empty = page.locator('[data-testid="clientes.empty"]');
    await expect(table.or(empty)).toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-testid="clientes.total"]')).toBeVisible();
    await expect(page.locator('[data-testid="clientes.search"]')).toBeVisible();
    await expect(page.locator('[data-testid="clientes.filters.apply"]')).toBeVisible();
  });

  test('empleado sin permisos de supervisor es redirigido al acceder a /clientes', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/clientes');
    await expect(page).toHaveURL('/', { timeout: 10000 });
    await expect(page.locator('[data-testid="clientes.list"]')).not.toBeVisible();
  });

  test('supervisor puede abrir detalle de cliente desde listado (TR-013)', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await page.goto('/clientes');
    await expect(page.locator('[data-testid="clientes.list"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="clientes.loading"]')).not.toBeVisible({ timeout: 15000 });

    const verBtn = page.locator('[data-testid^="clientes.ver."]').first();
    const hasRows = await page.locator('[data-testid="clientes.table"] tbody tr').count() > 0;
    if (hasRows) {
      await verBtn.click();
      await expect(page).toHaveURL(/\/clientes\/\d+$/, { timeout: 10000 });
      await expect(page.locator('[data-testid="clienteDetalle.container"]')).toBeVisible();
      await expect(page.locator('[data-testid="clienteDetalle.editar"]')).toBeVisible();
      await expect(page.locator('[data-testid="clienteDetalle.tiposTarea"]')).toBeVisible();
    }
  });
});
