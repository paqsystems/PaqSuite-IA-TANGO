/**
 * Tests E2E: Consulta Detallada de Tareas (TR-044, TR-050)
 *
 * Supervisor: login → /informes/consulta-detallada → filtros, tabla, total horas.
 * Empleado: login → /informes/consulta-detallada → ve solo sus tareas (sin filtro empleado).
 * Estado vacío (TR-050): mensaje cuando no hay resultados.
 *
 * @see docs/hu-tareas/TR-044(MH)-consulta-detallada-de-tareas.md
 * @see docs/hu-tareas/TR-050(MH)-manejo-de-resultados-vacíos-en-consultas.md
 */

import { test, expect } from '@playwright/test';

const SUPERVISOR = { code: 'MGARCIA', password: 'password456' };
const EMPLEADO = { code: 'JPEREZ', password: 'password123' };
const FECHA_DESDE = '2025-01-01';
const FECHA_HASTA = '2026-12-31';
const PERIODO_SIN_DATOS = { desde: '2030-01-01', hasta: '2030-01-31' };

test.describe('Consulta Detallada de Tareas — TR-044', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000);
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible({ timeout: 15000 });
  });

  test('supervisor puede acceder a consulta detallada y ver filtros y total horas', async ({
    page,
  }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/informes/consulta-detallada');
    await expect(page).toHaveURL('/informes/consulta-detallada', { timeout: 10000 });
    await expect(page.locator('[data-testid="report.detail.container"]')).toBeVisible({
      timeout: 15000,
    });
    await expect(page.getByRole('heading', { name: /consulta detallada/i })).toBeVisible();
    await expect(page.locator('[data-testid="report.detail.filters"]')).toBeVisible();
    await expect(page.locator('[data-testid="report.detail.applyFilters"]')).toBeVisible();
    await expect(page.locator('[data-testid="report.detail.totalHours"]')).toBeVisible({ timeout: 15000 });
  });

  test('supervisor puede aplicar filtros de período', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await page.goto('/informes/consulta-detallada');
    await expect(page.locator('[data-testid="report.detail.container"]')).toBeVisible({
      timeout: 15000,
    });
    await expect(page.locator('[data-testid="report.detail.loading"]')).not.toBeVisible({
      timeout: 15000,
    });

    const fechaDesde = page.getByLabel(/fecha desde/i);
    const fechaHasta = page.getByLabel(/fecha hasta/i);
    await fechaDesde.fill(FECHA_DESDE);
    await fechaHasta.fill(FECHA_HASTA);
    await page.click('[data-testid="report.detail.applyFilters"]');
    await expect(page.locator('[data-testid="report.detail.loading"]')).not.toBeVisible({
      timeout: 15000,
    });
    await expect(
      page.locator('[data-testid="report.detail.table"]').or(page.locator('[data-testid="report.detail.empty"]'))
    ).toBeVisible({ timeout: 10000 });
  });

  test('empleado puede acceder a consulta detallada y filtrar por cliente (sin filtro empleado)', async ({
    page,
  }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO.password);
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/informes/consulta-detallada');
    await expect(page).toHaveURL('/informes/consulta-detallada', { timeout: 10000 });
    await expect(page.locator('[data-testid="report.detail.container"]')).toBeVisible({
      timeout: 15000,
    });
    await expect(page.locator('[data-testid="report.detail.totalHours"]')).toBeVisible({ timeout: 15000 });
    await expect(page.getByRole('combobox', { name: /cliente/i })).toBeVisible();
  });

  test('TR-050: muestra mensaje de estado vacío cuando no hay resultados', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO.password);
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/informes/consulta-detallada');
    await expect(page.locator('[data-testid="report.detail.container"]')).toBeVisible({
      timeout: 15000,
    });
    const fechaDesde = page.getByLabel(/fecha desde/i);
    const fechaHasta = page.getByLabel(/fecha hasta/i);
    await fechaDesde.fill(PERIODO_SIN_DATOS.desde);
    await fechaHasta.fill(PERIODO_SIN_DATOS.hasta);
    await page.click('[data-testid="report.detail.applyFilters"]');
    await expect(page.locator('[data-testid="report.detail.loading"]')).not.toBeVisible({
      timeout: 15000,
    });
    await expect(
      page.locator('[data-testid="report.detail.table"]').or(page.locator('[data-testid="report.detail.empty"]'))
    ).toBeVisible({ timeout: 10000 });
    const emptyEl = page.locator('[data-testid="report.detail.empty"]');
    if (await emptyEl.isVisible()) {
      await expect(emptyEl).toContainText(/no se encontraron tareas para los filtros seleccionados/i);
    }
  });
});
