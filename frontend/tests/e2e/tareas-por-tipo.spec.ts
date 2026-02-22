/**
 * Tests E2E: Tareas por Tipo (TR-047)
 *
 * Supervisor: login → /informes/tareas-por-tipo → filtros, grupos, total general.
 * Expandir un grupo → ver detalle (tabla) → colapsar.
 *
 * @see docs/hu-tareas/TR-047(SH)-consulta-agrupada-por-tipo-de-tarea.md
 */

import { test, expect } from '@playwright/test';

const SUPERVISOR = { code: 'MGARCIA', password: 'password456' };
const FECHA_DESDE = '2026-01-01';
const FECHA_HASTA = '2026-01-31';

test.describe('Tareas por Tipo — TR-047', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000);
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible({ timeout: 15000 });
  });

  test('supervisor puede acceder a tareas por tipo y ver filtros y total general', async ({
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

    await page.goto('/informes/tareas-por-tipo');
    await expect(page).toHaveURL('/informes/tareas-por-tipo', { timeout: 10000 });
    await expect(page.locator('[data-testid="tareasPorTipo.page"]')).toBeVisible({
      timeout: 15000,
    });
    await expect(page.getByRole('heading', { name: /tareas por tipo/i })).toBeVisible();
    await expect(page.locator('[data-testid="tareasPorTipo.filtros"]')).toBeVisible();
    await expect(page.locator('[data-testid="tareasPorTipo.aplicarFiltros"]')).toBeVisible();
    await expect(page.locator('[data-testid="tareasPorTipo.totalGeneral"]')).toBeVisible({
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

    await page.goto('/informes/tareas-por-tipo');
    await expect(page.locator('[data-testid="tareasPorTipo.page"]')).toBeVisible({
      timeout: 15000,
    });
    await expect(page.locator('[data-testid="tareasPorTipo.loading"]')).not.toBeVisible({
      timeout: 15000,
    });
    const fechaDesde = page.getByLabel(/fecha desde/i);
    const fechaHasta = page.getByLabel(/fecha hasta/i);
    await fechaDesde.fill(FECHA_DESDE);
    await fechaHasta.fill(FECHA_HASTA);
    await page.click('[data-testid="tareasPorTipo.aplicarFiltros"]');
    await expect(page.locator('[data-testid="tareasPorTipo.loading"]')).not.toBeVisible({
      timeout: 15000,
    });
    await expect(
      page.locator('[data-testid="tareasPorTipo.grupos"], [data-testid="tareasPorTipo.empty"]')
    ).toBeVisible({ timeout: 10000 });
  });
});
