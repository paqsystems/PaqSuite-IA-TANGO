/**
 * Tests E2E: Tareas por Fecha (TR-048)
 *
 * Usuario (empleado/supervisor): login → /informes/tareas-por-fecha → filtros, grupos, total general.
 * Expandir un grupo → ver detalle (tabla).
 *
 * @see docs/hu-tareas/TR-048(SH)-consulta-agrupada-por-fecha.md
 */

import { test, expect } from '@playwright/test';

const EMPLEADO = { code: 'JPEREZ', password: 'password123' };
const FECHA_DESDE = '2026-01-01';
const FECHA_HASTA = '2026-01-31';

test.describe('Tareas por Fecha — TR-048', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000);
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible({ timeout: 15000 });
  });

  test('empleado puede acceder a tareas por fecha y ver filtros y total general', async ({
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

    await page.goto('/informes/tareas-por-fecha', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL('/informes/tareas-por-fecha', { timeout: 10000 });
    await expect(page.locator('[data-testid="tareasPorFecha.page"]')).toBeVisible({
      timeout: 20000,
    });
    await expect(page.getByRole('heading', { name: /tareas por fecha/i })).toBeVisible();
    await expect(page.locator('[data-testid="tareasPorFecha.filtros"]')).toBeVisible();
    await expect(page.locator('[data-testid="tareasPorFecha.aplicarFiltros"]')).toBeVisible();
    await expect(page.locator('[data-testid="tareasPorFecha.totalGeneral"]')).toBeVisible({
      timeout: 15000,
    });
  });

  test('empleado aplica filtros y ve grupos o estado vacío', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO.password);
    await Promise.all([
      page.waitForResponse(
        (resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200
      ),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/informes/tareas-por-fecha', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-testid="tareasPorFecha.page"]')).toBeVisible({
      timeout: 20000,
    });
    await expect(page.locator('[data-testid="tareasPorFecha.loading"]')).not.toBeVisible({
      timeout: 15000,
    });
    const fechaDesde = page.getByLabel(/fecha desde/i);
    const fechaHasta = page.getByLabel(/fecha hasta/i);
    await fechaDesde.fill(FECHA_DESDE);
    await fechaHasta.fill(FECHA_HASTA);
    await page.click('[data-testid="tareasPorFecha.aplicarFiltros"]');
    await expect(page.locator('[data-testid="tareasPorFecha.loading"]')).not.toBeVisible({
      timeout: 15000,
    });
    await expect(
      page.locator('[data-testid="tareasPorFecha.grupos"], [data-testid="tareasPorFecha.empty"]')
    ).toBeVisible({ timeout: 10000 });
  });
});
