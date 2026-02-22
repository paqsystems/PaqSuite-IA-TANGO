/**
 * Tests E2E: Tareas por Empleado (TR-045)
 *
 * Supervisor: login → /informes/tareas-por-empleado → filtros, grupos, total general.
 * Expandir un grupo → ver detalle (tabla) → colapsar.
 * Empleado no supervisor: acceso directo a la URL → redirige o 403 (según backend; front con SupervisorRoute redirige).
 *
 * @see docs/hu-tareas/TR-045(SH)-consulta-agrupada-por-empleado.md
 */

import { test, expect } from '@playwright/test';

const SUPERVISOR = { code: 'MGARCIA', password: 'password456' };
const EMPLEADO = { code: 'JPEREZ', password: 'password123' };
const FECHA_DESDE = '2026-01-01';
const FECHA_HASTA = '2026-01-31';

test.describe('Tareas por Empleado — TR-045', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000);
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible({ timeout: 15000 });
  });

  test('supervisor puede acceder a tareas por empleado y ver filtros y total general', async ({
    page,
  }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse(
        (resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200
      ),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/informes/tareas-por-empleado');
    await expect(page).toHaveURL('/informes/tareas-por-empleado', { timeout: 10000 });
    await expect(page.locator('[data-testid="tareasPorEmpleado.page"]')).toBeVisible({
      timeout: 15000,
    });
    await expect(page.getByRole('heading', { name: /tareas por empleado/i })).toBeVisible();
    await expect(page.locator('[data-testid="tareasPorEmpleado.filtros"]')).toBeVisible();
    await expect(page.locator('[data-testid="tareasPorEmpleado.aplicarFiltros"]')).toBeVisible();
    await expect(page.locator('[data-testid="tareasPorEmpleado.totalGeneral"]')).toBeVisible({
      timeout: 15000,
    });
  });

  test('supervisor aplica filtros y ve grupos o estado vacío', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse(
        (resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200
      ),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/informes/tareas-por-empleado');
    await expect(page.locator('[data-testid="tareasPorEmpleado.page"]')).toBeVisible({
      timeout: 15000,
    });
    await expect(page.locator('[data-testid="tareasPorEmpleado.loading"]')).not.toBeVisible({
      timeout: 15000,
    });
    const fechaDesde = page.getByLabel(/fecha desde/i);
    const fechaHasta = page.getByLabel(/fecha hasta/i);
    await fechaDesde.fill(FECHA_DESDE);
    await fechaHasta.fill(FECHA_HASTA);
    await page.click('[data-testid="tareasPorEmpleado.aplicarFiltros"]');
    await expect(page.locator('[data-testid="tareasPorEmpleado.loading"]')).not.toBeVisible({
      timeout: 15000,
    });
    await expect(
      page
        .locator('[data-testid="tareasPorEmpleado.grupos"]')
        .or(page.locator('[data-testid="tareasPorEmpleado.empty"]'))
    ).toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-testid="tareasPorEmpleado.totalGeneral"]')).toBeVisible();
  });

  test('supervisor expande un grupo, ve detalle y colapsa', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse(
        (resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200
      ),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/informes/tareas-por-empleado');
    await expect(page.locator('[data-testid="tareasPorEmpleado.page"]')).toBeVisible({
      timeout: 15000,
    });
    await expect(page.locator('[data-testid="tareasPorEmpleado.loading"]')).not.toBeVisible({
      timeout: 15000,
    });
    const fechaDesde = page.getByLabel(/fecha desde/i);
    const fechaHasta = page.getByLabel(/fecha hasta/i);
    await fechaDesde.fill(FECHA_DESDE);
    await fechaHasta.fill(FECHA_HASTA);
    await page.click('[data-testid="tareasPorEmpleado.aplicarFiltros"]');
    await expect(page.locator('[data-testid="tareasPorEmpleado.loading"]')).not.toBeVisible({
      timeout: 15000,
    });

    const expandButton = page.locator('[data-testid^="tareasPorEmpleado.grupoExpandir."]').first();
    const groupsContainer = page.locator('[data-testid="tareasPorEmpleado.grupos"]');

    const hasGroups = await groupsContainer.isVisible().catch(() => false);

    if (hasGroups && (await expandButton.count()) > 0) {
      await expandButton.click();
      await expect(page.locator('[data-testid^="tareasPorEmpleado.tabla."]').first()).toBeVisible({
        timeout: 5000,
      });
      await expandButton.click();
      await expect(page.locator('[data-testid^="tareasPorEmpleado.tabla."]').first()).not.toBeVisible({
        timeout: 3000,
      });
    }
  });

  test('empleado no supervisor no ve enlace y al acceder por URL es redirigido a /', async ({
    page,
  }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO.password);
    await Promise.all([
      page.waitForResponse(
        (resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200
      ),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await expect(page.locator('[data-testid="app.tareasPorEmpleadoLink"]')).not.toBeVisible();

    await page.goto('/informes/tareas-por-empleado');
    await expect(page).toHaveURL('/', { timeout: 10000 });
  });
});
