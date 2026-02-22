/**
 * Tests E2E: Creación de Empleado (Supervisor) — TR-019
 *
 * Flujo: login supervisor → Empleados → Crear empleado → completar formulario → Guardar → listado.
 *
 * @see docs/hu-tareas/TR-019(MH)-creación-de-empleado.md
 */

import { test, expect } from '@playwright/test';

const SUPERVISOR = {
  code: 'MGARCIA',
  password: 'password456',
};

test.describe('Creación de Empleado (Supervisor) — TR-019', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000);
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible({ timeout: 15000 });
  });

  test('supervisor puede acceder al formulario de creación desde listado', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/empleados');
    await expect(page.locator('[data-testid="empleados.list"]')).toBeVisible({ timeout: 15000 });
    await page.click('[data-testid="empleados.create"]');
    await expect(page).toHaveURL('/empleados/nuevo', { timeout: 10000 });
    await expect(page.locator('[data-testid="empleados.create.form"]')).toBeVisible({ timeout: 5000 });
    await expect(page.locator('[data-testid="empleados.create.code"]')).toBeVisible();
    await expect(page.locator('[data-testid="empleados.create.nombre"]')).toBeVisible();
    await expect(page.locator('[data-testid="empleados.create.password"]')).toBeVisible();
    await expect(page.locator('[data-testid="empleados.create.passwordConfirm"]')).toBeVisible();
    await expect(page.locator('[data-testid="empleados.create.submit"]')).toBeVisible();
    await expect(page.locator('[data-testid="empleados.create.cancel"]')).toBeVisible();
  });

  test('supervisor crea empleado correctamente y es redirigido al listado', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/empleados/nuevo');
    await expect(page.locator('[data-testid="empleados.create.form"]')).toBeVisible({ timeout: 10000 });

    const uniqueCode = 'E2E_' + Date.now();
    await page.fill('[data-testid="empleados.create.code"]', uniqueCode);
    await page.fill('[data-testid="empleados.create.nombre"]', 'Empleado E2E TR-019');
    await page.fill('[data-testid="empleados.create.email"]', `test${Date.now()}@ejemplo.com`);
    await page.fill('[data-testid="empleados.create.password"]', 'password123');
    await page.fill('[data-testid="empleados.create.passwordConfirm"]', 'password123');

    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/empleados') && resp.request().method() === 'POST', { timeout: 15000 }),
      page.click('[data-testid="empleados.create.submit"]'),
    ]);

    await expect(page).toHaveURL('/empleados', { timeout: 20000 });
    await expect(page.locator('[data-testid="empleados.list"]')).toBeVisible({ timeout: 10000 });
  });

  test('validación: contraseñas no coinciden muestra error', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/empleados/nuevo');
    await expect(page.locator('[data-testid="empleados.create.form"]')).toBeVisible({ timeout: 10000 });

    await page.fill('[data-testid="empleados.create.code"]', 'E2E_TEST');
    await page.fill('[data-testid="empleados.create.nombre"]', 'Test');
    await page.fill('[data-testid="empleados.create.password"]', 'password123');
    await page.fill('[data-testid="empleados.create.passwordConfirm"]', 'password456');

    await page.click('[data-testid="empleados.create.submit"]');

    await expect(page.locator('[data-testid="empleados.create.passwordConfirm"][aria-invalid="true"]')).toBeVisible({ timeout: 5000 });
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

    await page.goto('/empleados/nuevo');
    await expect(page.locator('[data-testid="empleados.create.form"]')).toBeVisible({ timeout: 10000 });

    await page.fill('[data-testid="empleados.create.code"]', 'E2E_SHORT');
    await page.fill('[data-testid="empleados.create.nombre"]', 'Test');
    await page.fill('[data-testid="empleados.create.password"]', '123');
    await page.fill('[data-testid="empleados.create.passwordConfirm"]', '123');

    await page.click('[data-testid="empleados.create.submit"]');

    await expect(page.locator('[data-testid="empleados.create.password"][aria-invalid="true"]')).toBeVisible({ timeout: 5000 });
    await expect(page.locator('text=al menos 8 caracteres')).toBeVisible();
  });

  test('intento crear empleado con código duplicado muestra error', async ({ page }) => {
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 20000 });

    await page.goto('/empleados/nuevo');
    await expect(page.locator('[data-testid="empleados.create.form"]')).toBeVisible({ timeout: 10000 });

    // Usar un código que probablemente ya existe (el del supervisor)
    await page.fill('[data-testid="empleados.create.code"]', SUPERVISOR.code);
    await page.fill('[data-testid="empleados.create.nombre"]', 'Test Duplicado');
    await page.fill('[data-testid="empleados.create.password"]', 'password123');
    await page.fill('[data-testid="empleados.create.passwordConfirm"]', 'password123');

    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/empleados') && resp.request().method() === 'POST', { timeout: 15000 }),
      page.click('[data-testid="empleados.create.submit"]'),
    ]);

    await expect(page.locator('[data-testid="empleados.create.error"]')).toBeVisible({ timeout: 5000 });
  });
});
