/**
 * Tests E2E: Tipos de Cliente (Supervisor) — TR-014, TR-015, TR-016, TR-017
 *
 * Listado, creación, edición y eliminación de tipos de cliente (solo supervisores).
 *
 * @see docs/hu-tareas/TR-014(MH)-listado-de-tipos-de-cliente.md
 * @see docs/hu-tareas/TR-015(MH)-creación-de-tipo-de-cliente.md
 * @see docs/hu-tareas/TR-016(MH)-edición-de-tipo-de-cliente.md
 * @see docs/hu-tareas/TR-017(MH)-eliminación-de-tipo-de-cliente.md
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

test.describe('Tipos de Cliente (Supervisor) — TR-014 a TR-017', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000);
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible({ timeout: 15000 });
  });

  test('supervisor puede acceder a /tipos-cliente y ver la lista (TR-014)', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/tipos-cliente');
    await expect(page).toHaveURL('/tipos-cliente', { timeout: 10000 });
    await expect(page.locator('[data-testid="tiposCliente.list"]')).toBeVisible({ timeout: 15000 });
    await expect(page.getByRole('heading', { name: /tipos de cliente/i })).toBeVisible();
  });

  test('supervisor ve tabla o mensaje vacío, total y filtros (TR-014)', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await page.goto('/tipos-cliente');
    await expect(page.locator('[data-testid="tiposCliente.list"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="tiposCliente.loading"]')).not.toBeVisible({ timeout: 15000 });

    await expect(page.locator('[data-testid="tiposCliente.tabla"]').or(page.locator('[data-testid="tiposCliente.empty"]'))).toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-testid="tiposCliente.crear"]')).toBeVisible();
    await expect(page.locator('[data-testid="tiposCliente.busqueda"]')).toBeVisible();
  });

  test('empleado sin permisos es redirigido al acceder a /tipos-cliente', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/tipos-cliente');
    await expect(page).toHaveURL('/', { timeout: 10000 });
    await expect(page.locator('[data-testid="tiposCliente.list"]')).not.toBeVisible();
  });

  test('supervisor puede crear tipo de cliente (TR-015)', async ({ page }) => {
    const code = `E2E_${Date.now()}`;
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/tipos-cliente/nuevo');
    await expect(page.locator('[data-testid="tipoClienteCrear.form"]')).toBeVisible({ timeout: 10000 });

    await page.fill('[data-testid="tipoClienteCrear.code"]', code);
    await page.fill('[data-testid="tipoClienteCrear.descripcion"]', 'Tipo E2E Test');
    await page.click('[data-testid="tipoClienteCrear.submit"]');

    await expect(page).toHaveURL('/tipos-cliente', { timeout: 15000 });
    await expect(page.locator('[data-testid="tiposCliente.list"]')).toBeVisible();
  });

  test('supervisor puede editar tipo de cliente (TR-016)', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/tipos-cliente');
    await expect(page.locator('[data-testid="tiposCliente.list"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="tiposCliente.loading"]')).not.toBeVisible({ timeout: 15000 });

    const editBtn = page.locator('[data-testid^="tiposCliente.editar."]').first();
    if (!(await editBtn.isVisible())) {
      test.skip();
      return;
    }
    await editBtn.click();
    await expect(page).toHaveURL(/\/tipos-cliente\/\d+\/editar/, { timeout: 10000 });
    await expect(page.locator('[data-testid="tipoClienteEditar.form"]')).toBeVisible({ timeout: 5000 });
    await expect(page.locator('[data-testid="tipoClienteEditar.code"]')).toBeVisible();
    await page.fill('[data-testid="tipoClienteEditar.descripcion"]', 'Descripción editada E2E');
    await page.click('[data-testid="tipoClienteEditar.submit"]');
    await expect(page).toHaveURL('/tipos-cliente', { timeout: 15000 });
  });

  test('supervisor puede eliminar tipo sin clientes (TR-017)', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/tipos-cliente');
    await expect(page.locator('[data-testid="tiposCliente.list"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="tiposCliente.loading"]')).not.toBeVisible({ timeout: 15000 });

    const deleteBtn = page.locator('[data-testid^="tiposCliente.eliminar."]').first();
    if (!(await deleteBtn.isVisible())) {
      test.skip();
      return;
    }
    await deleteBtn.click();
    await expect(page.locator('[data-testid="tiposCliente.delete.modal"]')).toBeVisible({ timeout: 5000 });
    await page.click('[data-testid="tiposCliente.deleteConfirm"]');
    await expect(page.locator('[data-testid="tiposCliente.delete.modal"]')).not.toBeVisible({ timeout: 10000 });
  });
});
