/**
 * Tests E2E: Edición de Empleado (Supervisor) — TR-020
 *
 * Flujo: login supervisor → Empleados → Editar empleado → modificar campos → Guardar → listado.
 *
 * @see docs/hu-tareas/TR-020(MH)-edición-de-empleado.md
 */

import { test, expect } from '@playwright/test';

const SUPERVISOR = {
  code: 'MGARCIA',
  password: 'password456',
};

test.describe('Edición de Empleado (Supervisor) — TR-020', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000);
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible({ timeout: 15000 });
  });

  test('supervisor puede acceder al formulario de edición desde listado', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/empleados');
    await expect(page.locator('[data-testid="empleados.list"]')).toBeVisible({ timeout: 15000 });

    // Esperar a que la tabla se cargue y encontrar el primer botón de editar
    await expect(page.locator('[data-testid^="empleados.edit."]').first()).toBeVisible({ timeout: 10000 });
    const editButton = page.locator('[data-testid^="empleados.edit."]').first();
    const editButtonId = await editButton.getAttribute('data-testid');
    const empleadoId = editButtonId?.replace('empleados.edit.', '');

    await editButton.click();
    await expect(page).toHaveURL(`/empleados/${empleadoId}/editar`, { timeout: 10000 });
    await expect(page.locator('[data-testid="empleados.edit.page"]')).toBeVisible({ timeout: 5000 });
    await expect(page.locator('[data-testid="empleados.edit.form"]')).toBeVisible({ timeout: 5000 });
    await expect(page.locator('[data-testid="empleados.edit.code"]')).toBeVisible();
    await expect(page.locator('[data-testid="empleados.edit.nombre"]')).toBeVisible();
    await expect(page.locator('[data-testid="empleados.edit.email"]')).toBeVisible();
    await expect(page.locator('[data-testid="empleados.edit.submit"]')).toBeVisible();
    await expect(page.locator('[data-testid="empleados.edit.cancel"]')).toBeVisible();
  });

  test('supervisor edita empleado correctamente y es redirigido al listado', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/empleados');
    await expect(page.locator('[data-testid="empleados.list"]')).toBeVisible({ timeout: 15000 });

    // Esperar a que la tabla se cargue y encontrar el primer botón de editar
    await expect(page.locator('[data-testid^="empleados.edit."]').first()).toBeVisible({ timeout: 10000 });
    const editButton = page.locator('[data-testid^="empleados.edit."]').first();
    const editButtonId = await editButton.getAttribute('data-testid');
    const empleadoId = editButtonId?.replace('empleados.edit.', '');

    await editButton.click();
    await expect(page).toHaveURL(`/empleados/${empleadoId}/editar`, { timeout: 10000 });
    await expect(page.locator('[data-testid="empleados.edit.form"]')).toBeVisible({ timeout: 10000 });

    // Obtener el nombre actual y modificarlo
    const nombreInput = page.locator('[data-testid="empleados.edit.nombre"]');
    const nombreActual = await nombreInput.inputValue();
    const nuevoNombre = nombreActual + ' (Editado E2E)';

    await nombreInput.clear();
    await nombreInput.fill(nuevoNombre);

    await Promise.all([
      page.waitForResponse(
        (resp) => resp.url().includes(`/api/v1/empleados/${empleadoId}`) && resp.request().method() === 'PUT',
        { timeout: 15000 }
      ),
      page.click('[data-testid="empleados.edit.submit"]'),
    ]);

    await expect(page).toHaveURL('/empleados', { timeout: 20000 });
    await expect(page.locator('[data-testid="empleados.list"]')).toBeVisible({ timeout: 10000 });
  });

  test('código es solo lectura y no se puede modificar', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/empleados');
    await expect(page.locator('[data-testid="empleados.list"]')).toBeVisible({ timeout: 15000 });

    await expect(page.locator('[data-testid^="empleados.edit."]').first()).toBeVisible({ timeout: 10000 });
    const editButton = page.locator('[data-testid^="empleados.edit."]').first();
    const editButtonId = await editButton.getAttribute('data-testid');
    const empleadoId = editButtonId?.replace('empleados.edit.', '');

    await editButton.click();
    await expect(page).toHaveURL(`/empleados/${empleadoId}/editar`, { timeout: 10000 });
    await expect(page.locator('[data-testid="empleados.edit.form"]')).toBeVisible({ timeout: 10000 });

    const codeInput = page.locator('[data-testid="empleados.edit.code"]');
    await expect(codeInput).toBeDisabled();
    await expect(codeInput).toHaveAttribute('readonly');
  });

  test('opción cambiar contraseña muestra campos de contraseña', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/empleados');
    await expect(page.locator('[data-testid="empleados.list"]')).toBeVisible({ timeout: 15000 });

    await expect(page.locator('[data-testid^="empleados.edit."]').first()).toBeVisible({ timeout: 10000 });
    const editButton = page.locator('[data-testid^="empleados.edit."]').first();
    const editButtonId = await editButton.getAttribute('data-testid');
    const empleadoId = editButtonId?.replace('empleados.edit.', '');

    await editButton.click();
    await expect(page).toHaveURL(`/empleados/${empleadoId}/editar`, { timeout: 10000 });
    await expect(page.locator('[data-testid="empleados.edit.form"]')).toBeVisible({ timeout: 10000 });

    // Los campos de contraseña no deben estar visibles inicialmente
    await expect(page.locator('[data-testid="empleados.edit.password"]')).not.toBeVisible();

    // Marcar checkbox para cambiar contraseña
    await page.click('[data-testid="empleados.edit.showChangePassword"]');

    // Ahora los campos de contraseña deben estar visibles
    await expect(page.locator('[data-testid="empleados.edit.password"]')).toBeVisible({ timeout: 2000 });
    await expect(page.locator('[data-testid="empleados.edit.passwordConfirm"]')).toBeVisible({ timeout: 2000 });
  });

  test('validación: contraseñas no coinciden muestra error', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/empleados');
    await expect(page.locator('[data-testid="empleados.list"]')).toBeVisible({ timeout: 15000 });

    await expect(page.locator('[data-testid^="empleados.edit."]').first()).toBeVisible({ timeout: 10000 });
    const editButton = page.locator('[data-testid^="empleados.edit."]').first();
    const editButtonId = await editButton.getAttribute('data-testid');
    const empleadoId = editButtonId?.replace('empleados.edit.', '');

    await editButton.click();
    await expect(page).toHaveURL(`/empleados/${empleadoId}/editar`, { timeout: 10000 });
    await expect(page.locator('[data-testid="empleados.edit.form"]')).toBeVisible({ timeout: 10000 });

    await page.click('[data-testid="empleados.edit.showChangePassword"]');
    await page.fill('[data-testid="empleados.edit.password"]', 'password123');
    await page.fill('[data-testid="empleados.edit.passwordConfirm"]', 'password456');

    await page.click('[data-testid="empleados.edit.submit"]');

    await expect(page.locator('[data-testid="empleados.edit.passwordConfirm"][aria-invalid="true"]')).toBeVisible({
      timeout: 5000,
    });
    await expect(page.locator('text=Las contraseñas no coinciden')).toBeVisible();
  });

  test('validación: contraseña muy corta muestra error', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/empleados');
    await expect(page.locator('[data-testid="empleados.list"]')).toBeVisible({ timeout: 15000 });

    await expect(page.locator('[data-testid^="empleados.edit."]').first()).toBeVisible({ timeout: 10000 });
    const editButton = page.locator('[data-testid^="empleados.edit."]').first();
    const editButtonId = await editButton.getAttribute('data-testid');
    const empleadoId = editButtonId?.replace('empleados.edit.', '');

    await editButton.click();
    await expect(page).toHaveURL(`/empleados/${empleadoId}/editar`, { timeout: 10000 });
    await expect(page.locator('[data-testid="empleados.edit.form"]')).toBeVisible({ timeout: 10000 });

    await page.click('[data-testid="empleados.edit.showChangePassword"]');
    await page.fill('[data-testid="empleados.edit.password"]', 'short');
    await page.fill('[data-testid="empleados.edit.passwordConfirm"]', 'short');

    await page.click('[data-testid="empleados.edit.submit"]');

    await expect(page.locator('[data-testid="empleados.edit.password"][aria-invalid="true"]')).toBeVisible({
      timeout: 5000,
    });
    await expect(page.locator('text=La contraseña debe tener al menos 8 caracteres')).toBeVisible();
  });

  test('botón cancelar redirige al listado sin guardar cambios', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/empleados');
    await expect(page.locator('[data-testid="empleados.list"]')).toBeVisible({ timeout: 15000 });

    await expect(page.locator('[data-testid^="empleados.edit."]').first()).toBeVisible({ timeout: 10000 });
    const editButton = page.locator('[data-testid^="empleados.edit."]').first();
    const editButtonId = await editButton.getAttribute('data-testid');
    const empleadoId = editButtonId?.replace('empleados.edit.', '');

    await editButton.click();
    await expect(page).toHaveURL(`/empleados/${empleadoId}/editar`, { timeout: 10000 });
    await expect(page.locator('[data-testid="empleados.edit.form"]')).toBeVisible({ timeout: 10000 });

    // Modificar un campo
    const nombreInput = page.locator('[data-testid="empleados.edit.nombre"]');
    await nombreInput.clear();
    await nombreInput.fill('Nombre modificado pero cancelado');

    // Hacer clic en cancelar
    await page.click('[data-testid="empleados.edit.cancel"]');

    // Debe redirigir al listado sin guardar
    await expect(page).toHaveURL('/empleados', { timeout: 10000 });
    await expect(page.locator('[data-testid="empleados.list"]')).toBeVisible({ timeout: 10000 });
  });
});
