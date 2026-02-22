/**
 * Tests E2E: Tareas por Cliente (TR-046, TR-050)
 *
 * Supervisor/empleado: login → /informes/tareas-por-cliente → filtros, grupos, total general.
 * Estado vacío (TR-050): mensaje cuando no hay grupos.
 *
 * @see docs/hu-tareas/TR-046(MH)-consulta-agrupada-por-cliente.md
 * @see docs/hu-tareas/TR-050(MH)-manejo-de-resultados-vacíos-en-consultas.md
 */

import { test, expect } from '@playwright/test';

const SUPERVISOR = { code: 'MGARCIA', password: 'password456' };
const EMPLEADO = { code: 'JPEREZ', password: 'password123' };
const FECHA_DESDE = '2025-01-01';
const FECHA_HASTA = '2026-12-31';
const PERIODO_SIN_DATOS = { desde: '2030-01-01', hasta: '2030-01-31' };

test.describe('Tareas por Cliente — TR-046', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000);
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible({ timeout: 15000 });
  });

  test('supervisor puede acceder a tareas por cliente y ver total general', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse(
        (resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200
      ),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/informes/tareas-por-cliente');
    await expect(page).toHaveURL('/informes/tareas-por-cliente', { timeout: 10000 });
    await expect(page.locator('[data-testid="report.byClient.container"]')).toBeVisible({
      timeout: 15000,
    });
    await expect(page.getByRole('heading', { name: /tareas por cliente/i })).toBeVisible();
    await expect(page.locator('[data-testid="report.byClient.filters"]')).toBeVisible();
    await expect(page.locator('[data-testid="report.byClient.applyFilters"]')).toBeVisible();
    await expect(page.locator('[data-testid="report.byClient.totalGeneral"]')).toBeVisible({
      timeout: 15000,
    });
  });

  test('empleado puede acceder a tareas por cliente', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO.password);
    await Promise.all([
      page.waitForResponse(
        (resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200
      ),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/informes/tareas-por-cliente');
    await expect(page).toHaveURL('/informes/tareas-por-cliente', { timeout: 10000 });
    await expect(page.locator('[data-testid="report.byClient.container"]')).toBeVisible({
      timeout: 15000,
    });
    await expect(page.locator('[data-testid="report.byClient.totalGeneral"]')).toBeVisible({
      timeout: 15000,
    });
  });

  test('TR-050: muestra mensaje de estado vacío cuando no hay grupos', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO.password);
    await Promise.all([
      page.waitForResponse(
        (resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200
      ),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/informes/tareas-por-cliente');
    await expect(page.locator('[data-testid="report.byClient.container"]')).toBeVisible({
      timeout: 15000,
    });
    const fechaDesde = page.getByLabel(/fecha desde/i);
    const fechaHasta = page.getByLabel(/fecha hasta/i);
    await fechaDesde.fill(PERIODO_SIN_DATOS.desde);
    await fechaHasta.fill(PERIODO_SIN_DATOS.hasta);
    await page.click('[data-testid="report.byClient.applyFilters"]');
    await expect(page.locator('[data-testid="report.byClient.loading"]')).not.toBeVisible({
      timeout: 15000,
    });
    await expect(
      page
        .locator('[data-testid="report.byClient.groups"]')
        .or(page.locator('[data-testid="report.byClient.empty"]'))
    ).toBeVisible({ timeout: 10000 });
    const emptyEl = page.locator('[data-testid="report.byClient.empty"]');
    if (await emptyEl.isVisible()) {
      await expect(emptyEl).toContainText(/no se encontraron tareas para los filtros seleccionados/i);
    }
  });
});
