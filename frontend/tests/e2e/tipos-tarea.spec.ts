/**
 * Tests E2E: Tipos de Tarea (Supervisor) — TR-023, TR-024, TR-025, TR-026, TR-027
 *
 * Listado, creación, edición, eliminación y detalle de tipos de tarea (solo supervisores).
 *
 * @see docs/hu-tareas/TR-023(MH)-listado-de-tipos-de-tarea.md
 * @see docs/hu-tareas/TR-024(MH)-creación-de-tipo-de-tarea.md
 * @see docs/hu-tareas/TR-025(MH)-edición-de-tipo-de-tarea.md
 * @see docs/hu-tareas/TR-026(MH)-eliminación-de-tipo-de-tarea.md
 * @see docs/hu-tareas/TR-027(SH)-visualización-de-detalle-de-tipo-de-tarea.md
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

test.describe('Tipos de Tarea (Supervisor) — TR-023 a TR-027', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000);
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible({ timeout: 15000 });
  });

  test('supervisor puede acceder a /tipos-tarea y ver la lista (TR-023)', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/tipos-tarea');
    await expect(page).toHaveURL('/tipos-tarea', { timeout: 10000 });
    await expect(page.locator('[data-testid="tiposTarea.list"]')).toBeVisible({ timeout: 15000 });
    await expect(page.getByRole('heading', { name: /tipos de tarea/i })).toBeVisible();
  });

  test('supervisor ve tabla o mensaje vacío, total y filtros (TR-023)', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await page.goto('/tipos-tarea');
    await expect(page.locator('[data-testid="tiposTarea.list"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="tiposTarea.loading"]')).not.toBeVisible({ timeout: 15000 });

    await expect(page.locator('[data-testid="tiposTarea.tabla"]').or(page.locator('[data-testid="tiposTarea.empty"]'))).toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-testid="tiposTarea.crear"]')).toBeVisible();
    await expect(page.locator('[data-testid="tiposTarea.busqueda"]')).toBeVisible();
  });

  test('empleado sin permisos es redirigido al acceder a /tipos-tarea', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/tipos-tarea');
    await expect(page).toHaveURL('/', { timeout: 10000 });
    await expect(page.locator('[data-testid="tiposTarea.list"]')).not.toBeVisible();
  });

  test('supervisor puede crear tipo de tarea (TR-024)', async ({ page }) => {
    const code = `E2E_${Date.now()}`;
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/tipos-tarea/nuevo');
    await expect(page.locator('[data-testid="tipoTareaCrear.form"]')).toBeVisible({ timeout: 10000 });

    await page.fill('[data-testid="tipoTareaCrear.code"]', code);
    await page.fill('[data-testid="tipoTareaCrear.descripcion"]', 'Tipo E2E Test');
    await page.click('[data-testid="tipoTareaCrear.submit"]');

    await expect(page).toHaveURL('/tipos-tarea', { timeout: 15000 });
    await expect(page.locator('[data-testid="tiposTarea.list"]')).toBeVisible();
  });

  test('supervisor puede editar tipo de tarea (TR-025)', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/tipos-tarea');
    await expect(page.locator('[data-testid="tiposTarea.list"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="tiposTarea.loading"]')).not.toBeVisible({ timeout: 15000 });

    const editBtn = page.locator('[data-testid^="tiposTarea.editar."]').first();
    if (!(await editBtn.isVisible())) {
      test.skip();
      return;
    }
    await editBtn.click();
    await expect(page).toHaveURL(/\/tipos-tarea\/\d+\/editar/, { timeout: 10000 });
    await expect(page.locator('[data-testid="tipoTareaEditar.form"]')).toBeVisible({ timeout: 5000 });
    await expect(page.locator('[data-testid="tipoTareaEditar.code"]')).toBeVisible();
    await page.fill('[data-testid="tipoTareaEditar.descripcion"]', 'Descripción editada E2E');
    await page.click('[data-testid="tipoTareaEditar.submit"]');
    await expect(page).toHaveURL('/tipos-tarea', { timeout: 15000 });
  });

  test('supervisor puede abrir detalle de tipo de tarea (TR-027)', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });
    await page.goto('/tipos-tarea');
    await expect(page.locator('[data-testid="tiposTarea.list"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="tiposTarea.loading"]')).not.toBeVisible({ timeout: 15000 });

    const verBtn = page.locator('[data-testid^="tiposTarea.ver."]').first();
    const hasRows = await page.locator('[data-testid="tiposTarea.tabla"] tbody tr').count() > 0;
    if (hasRows) {
      await verBtn.click();
      await expect(page).toHaveURL(/\/tipos-tarea\/\d+$/, { timeout: 10000 });
      await expect(page.locator('[data-testid="tipoTareaDetalle.container"]')).toBeVisible();
      await expect(page.locator('[data-testid="tipoTareaDetalle.editar"]')).toBeVisible();
    }
  });

  test('supervisor puede eliminar tipo sin referencias (TR-026)', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/tipos-tarea');
    await expect(page.locator('[data-testid="tiposTarea.list"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="tiposTarea.loading"]')).not.toBeVisible({ timeout: 15000 });

    const deleteBtn = page.locator('[data-testid^="tiposTarea.eliminar."]').first();
    if (!(await deleteBtn.isVisible())) {
      test.skip();
      return;
    }
    await deleteBtn.click();
    await expect(page.locator('[data-testid="tiposTarea.delete.modal"]')).toBeVisible({ timeout: 5000 });
    await page.click('[data-testid="tiposTarea.deleteConfirm"]');
    await expect(page.locator('[data-testid="tiposTarea.delete.modal"]')).not.toBeVisible({ timeout: 10000 });
  });
});
