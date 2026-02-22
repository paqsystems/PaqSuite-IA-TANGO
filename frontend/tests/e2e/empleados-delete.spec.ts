/**
 * Tests E2E: Eliminación de Empleado (Supervisor) — TR-021
 *
 * Flujo: login supervisor → Empleados → Eliminar empleado sin tareas → confirmar → ver mensaje y desaparición del listado.
 *
 * @see docs/hu-tareas/TR-021(MH)-eliminación-de-empleado.md
 */

import { test, expect } from '@playwright/test';

const SUPERVISOR = {
  code: 'MGARCIA',
  password: 'password456',
};

test.describe('Eliminación de Empleado (Supervisor) — TR-021', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000);
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible({ timeout: 15000 });
  });

  test('supervisor elimina empleado sin tareas y es redirigido al listado', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/empleados');
    await expect(page.locator('[data-testid="empleados.list"]')).toBeVisible({ timeout: 15000 });

    // Esperar a que la tabla se cargue y encontrar el primer botón de eliminar
    await expect(page.locator('[data-testid^="empleados.row."][data-testid$=".delete"]').first()).toBeVisible({
      timeout: 10000,
    });
    const deleteButton = page.locator('[data-testid^="empleados.row."][data-testid$=".delete"]').first();
    const deleteButtonId = await deleteButton.getAttribute('data-testid');
    const empleadoId = deleteButtonId?.replace('empleados.row.', '').replace('.delete', '');

    // Hacer clic en eliminar
    await deleteButton.click();

    // Verificar que aparece el modal de confirmación
    await expect(page.locator('[data-testid="empleados.delete.modal"]')).toBeVisible({ timeout: 5000 });
    await expect(page.locator('[data-testid="empleados.delete.code"]')).toBeVisible();
    await expect(page.locator('[data-testid="empleados.delete.nombre"]')).toBeVisible();
    await expect(page.locator('[data-testid="empleados.delete.confirm"]')).toBeVisible();
    await expect(page.locator('[data-testid="empleados.delete.cancel"]')).toBeVisible();

    // Confirmar eliminación
    await Promise.all([
      page.waitForResponse(
        (resp) => resp.url().includes(`/api/v1/empleados/${empleadoId}`) && resp.request().method() === 'DELETE',
        { timeout: 15000 }
      ),
      page.click('[data-testid="empleados.delete.confirm"]'),
    ]);

    // Verificar que el modal se cerró y aparece mensaje de éxito
    await expect(page.locator('[data-testid="empleados.delete.modal"]')).not.toBeVisible({ timeout: 5000 });
    await expect(page.locator('[data-testid="empleados.success"]')).toBeVisible({ timeout: 5000 });
    await expect(page.locator('[data-testid="empleados.list"]')).toBeVisible({ timeout: 10000 });

    // Verificar que el empleado ya no aparece en la tabla
    await expect(page.locator(`[data-testid="empleados.row.${empleadoId}"]`)).not.toBeVisible({ timeout: 5000 });
  });

  test('cancelar eliminación cierra el modal sin eliminar', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/empleados');
    await expect(page.locator('[data-testid="empleados.list"]')).toBeVisible({ timeout: 15000 });

    await expect(page.locator('[data-testid^="empleados.row."][data-testid$=".delete"]').first()).toBeVisible({
      timeout: 10000,
    });
    const deleteButton = page.locator('[data-testid^="empleados.row."][data-testid$=".delete"]').first();
    const deleteButtonId = await deleteButton.getAttribute('data-testid');
    const empleadoId = deleteButtonId?.replace('empleados.row.', '').replace('.delete', '');

    // Hacer clic en eliminar
    await deleteButton.click();

    // Verificar que aparece el modal
    await expect(page.locator('[data-testid="empleados.delete.modal"]')).toBeVisible({ timeout: 5000 });

    // Cancelar eliminación
    await page.click('[data-testid="empleados.delete.cancel"]');

    // Verificar que el modal se cerró
    await expect(page.locator('[data-testid="empleados.delete.modal"]')).not.toBeVisible({ timeout: 5000 });

    // Verificar que el empleado sigue en la tabla
    await expect(page.locator(`[data-testid="empleados.row.${empleadoId}"]`)).toBeVisible({ timeout: 5000 });
  });

  test('modal muestra código y nombre del empleado', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/empleados');
    await expect(page.locator('[data-testid="empleados.list"]')).toBeVisible({ timeout: 15000 });

    await expect(page.locator('[data-testid^="empleados.row."][data-testid$=".delete"]').first()).toBeVisible({
      timeout: 10000,
    });
    const deleteButton = page.locator('[data-testid^="empleados.row."][data-testid$=".delete"]').first();

    // Obtener código y nombre del empleado de la tabla antes de hacer clic
    const row = deleteButton.locator('..').locator('..'); // Subir dos niveles para llegar a la fila
    const code = await row.locator('td').nth(0).textContent();
    const nombre = await row.locator('td').nth(1).textContent();

    // Hacer clic en eliminar
    await deleteButton.click();

    // Verificar que el modal muestra código y nombre
    await expect(page.locator('[data-testid="empleados.delete.modal"]')).toBeVisible({ timeout: 5000 });
    const modalCode = await page.locator('[data-testid="empleados.delete.code"]').textContent();
    const modalNombre = await page.locator('[data-testid="empleados.delete.nombre"]').textContent();

    expect(modalCode).toBe(code?.trim());
    expect(modalNombre).toBe(nombre?.trim());

    // Cancelar
    await page.click('[data-testid="empleados.delete.cancel"]');
  });
});
