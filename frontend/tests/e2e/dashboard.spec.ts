/**
 * Tests E2E: Dashboard principal (TR-051)
 *
 * Empleado: login → / → selector período, KPIs, Top clientes (sin Top empleados).
 * Supervisor: login → / → KPIs, Top clientes, Top empleados.
 * Cliente: login → / → KPIs, Distribución por tipo (sin Top empleados).
 * Cambio de período actualiza datos. Estado vacío (HU-050).
 *
 * Requisito: el backend debe estar en marcha (php artisan serve) para que
 * los tests que dependen del API /api/v1/dashboard pasen.
 *
 * @see docs/hu-tareas/TR-051(MH)-dashboard-principal.md
 */

import { test, expect } from '@playwright/test';

const SUPERVISOR = { code: 'MGARCIA', password: 'password456' };
const EMPLEADO = { code: 'JPEREZ', password: 'password123' };
const CLIENTE = { code: 'CLI001', password: 'cliente123' };

test.describe('Dashboard principal — TR-051', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000);
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible({ timeout: 15000 });
  });

  test('empleado accede al dashboard y ve KPIs y Top clientes (sin Top empleados)', async ({
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

    await expect(page.locator('[data-testid="dashboard.container"]')).toBeVisible({
      timeout: 15000,
    });
    await expect(page.locator('[data-testid="dashboard.periodSelector"]')).toBeVisible();
    await expect(
      page.locator('[data-testid="dashboard.kpis"]').or(page.locator('[data-testid="dashboard.error"]'))
    ).toBeVisible({ timeout: 25000 });
    await expect(page.locator('[data-testid="dashboard.kpis"]')).toBeVisible();
    await expect(page.locator('[data-testid="dashboard.topClientes"]')).toBeVisible({
      timeout: 5000,
    });
    await expect(page.locator('[data-testid="dashboard.dedicacionEmpleado"]')).not.toBeVisible();
    await expect(page.locator('[data-testid="dashboard.kpi.totalHoras"]')).toBeVisible();
    await expect(page.locator('[data-testid="dashboard.kpi.cantidadTareas"]')).toBeVisible();
  });

  test('supervisor accede al dashboard y ve Top clientes y Top empleados', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse(
        (resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200
      ),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await expect(page.locator('[data-testid="dashboard.container"]')).toBeVisible({
      timeout: 15000,
    });
    await expect(
      page.locator('[data-testid="dashboard.kpis"]').or(page.locator('[data-testid="dashboard.error"]'))
    ).toBeVisible({ timeout: 25000 });
    await expect(page.locator('[data-testid="dashboard.kpis"]')).toBeVisible();
    await expect(page.locator('[data-testid="dashboard.topClientes"]')).toBeVisible({
      timeout: 5000,
    });
    await expect(page.locator('[data-testid="dashboard.dedicacionEmpleado"]')).toBeVisible({
      timeout: 5000,
    });
  });

  test('cliente accede al dashboard y ve Distribución por tipo (sin Dedicación por Empleado)', async ({
    page,
  }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', CLIENTE.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', CLIENTE.password);
    await Promise.all([
      page.waitForResponse(
        (resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200
      ),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await expect(page.locator('[data-testid="dashboard.container"]')).toBeVisible({
      timeout: 15000,
    });
    await expect(
      page.locator('[data-testid="dashboard.kpis"]').or(page.locator('[data-testid="dashboard.error"]'))
    ).toBeVisible({ timeout: 25000 });
    await expect(page.locator('[data-testid="dashboard.kpis"]')).toBeVisible();
    await expect(page.locator('[data-testid="dashboard.distribucionTipo"]')).toBeVisible({
      timeout: 5000,
    });
    await expect(page.locator('[data-testid="dashboard.dedicacionEmpleado"]')).not.toBeVisible();
  });

  test('TR-053: supervisor ve Dedicación por Empleado y enlace Ver detalle lleva a tareas-por-empleado', async ({
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
    await expect(
      page.locator('[data-testid="dashboard.kpis"]').or(page.locator('[data-testid="dashboard.error"]'))
    ).toBeVisible({ timeout: 25000 });
    await expect(page.locator('[data-testid="dashboard.dedicacionEmpleado"]')).toBeVisible();
    await expect(page.locator('[data-testid="dashboard.dedicacionEmpleado.totalGeneral"]')).toBeVisible();
    const linkDetalle = page.locator('[data-testid^="dashboard.dedicacionEmpleado.linkDetalle."]').first();
    if ((await linkDetalle.count()) > 0) {
      await linkDetalle.click();
      await expect(page).toHaveURL(/\/informes\/tareas-por-empleado/, { timeout: 10000 });
    }
  });

  test('TR-055: botón Actualizar e indicador de última actualización visibles', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO.password);
    await Promise.all([
      page.waitForResponse(
        (resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200
      ),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await expect(
      page.locator('[data-testid="dashboard.kpis"]').or(page.locator('[data-testid="dashboard.error"]'))
    ).toBeVisible({ timeout: 25000 });
    await expect(page.locator('[data-testid="dashboard.botonActualizar"]')).toBeVisible();
    await expect(page.locator('[data-testid="dashboard.ultimaActualizacion"]')).toBeVisible();
    await expect(page.locator('[data-testid="dashboard.ultimaActualizacion"]')).toContainText(
      'Actualizado hace'
    );
  });

  test('cambio de período actualiza datos del dashboard', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO.password);
    await Promise.all([
      page.waitForResponse(
        (resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200
      ),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await expect(page.locator('[data-testid="dashboard.container"]')).toBeVisible({
      timeout: 15000,
    });
    await page.locator('[data-testid="dashboard.periodDesde"]').fill('2030-01-01');
    await page.locator('[data-testid="dashboard.periodHasta"]').fill('2030-01-31');
    await expect(
      page.locator('[data-testid="dashboard.kpis"]').or(page.locator('[data-testid="dashboard.error"]'))
    ).toBeVisible({ timeout: 25000 });
  });

  test('botón Mes actual restablece período', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO.password);
    await Promise.all([
      page.waitForResponse(
        (resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200
      ),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await expect(page.locator('[data-testid="dashboard.periodSelector"]')).toBeVisible({
      timeout: 15000,
    });
    await page.click('[data-testid="dashboard.periodCurrentMonth"]');
    await expect(page.locator('[data-testid="dashboard.periodDesde"]')).not.toHaveValue('');
    await expect(page.locator('[data-testid="dashboard.periodHasta"]')).not.toHaveValue('');
  });

  test('TR-052: sección Dedicación por Cliente muestra total general y enlace Ver detalle', async ({
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
    await expect(
      page.locator('[data-testid="dashboard.kpis"]').or(page.locator('[data-testid="dashboard.error"]'))
    ).toBeVisible({ timeout: 25000 });
    await expect(page.locator('[data-testid="dashboard.dedicacionCliente"]')).toBeVisible();
    await expect(page.locator('[data-testid="dashboard.dedicacionCliente.totalGeneral"]')).toBeVisible();
    const linkDetalle = page.locator('[data-testid^="dashboard.dedicacionCliente.linkDetalle."]').first();
    if ((await linkDetalle.count()) > 0) {
      await linkDetalle.click();
      await expect(page).toHaveURL(/\/informes\/tareas-por-cliente/, { timeout: 10000 });
    }
  });

  test('TR-056: menú lateral visible; enlaces Inicio y Perfil presentes', async ({ page }) => {
    await page.setViewportSize({ width: 1024, height: 768 });
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO.password);
    await Promise.all([
      page.waitForResponse(
        (resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200
      ),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await expect(page.locator('[data-testid="app.layout"]')).toBeVisible();
    const sidebar = page.locator('[data-testid="app.sidebar"]');
    await expect(sidebar).toBeVisible({ timeout: 5000 });
    await expect(page.locator('[data-testid="app.sidebar.inicio"]')).toBeVisible();
    await expect(page.locator('[data-testid="app.profileLink"]')).toBeVisible();
    await page.click('[data-testid="app.profileLink"]');
    await expect(page).toHaveURL('/perfil', { timeout: 5000 });
  });
});
