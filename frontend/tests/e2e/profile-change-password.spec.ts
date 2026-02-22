/**
 * Tests E2E: Cambio de contraseña (usuario autenticado)
 *
 * Tests end-to-end con Playwright para el flujo de cambio de contraseña desde perfil.
 * Usa test.describe.serial para que el primer test cambie la contraseña y el segundo
 * use la nueva contraseña para login y pruebe el error de contraseña actual incorrecta.
 *
 * Reglas:
 * - Usa selectores data-testid (NO CSS/XPath/texto)
 * - NO usa esperas ciegas (waitForTimeout, sleep, etc.)
 * - Espera estados visibles con expect().toBeVisible()
 *
 * @see TR-005(SH)-cambio-de-contraseña-usuario-autenticado.md
 */

import { test, expect } from '@playwright/test';

const EMPLEADO_USER = {
  code: 'JPEREZ',
  password: 'password123',
  name: 'Juan Pérez',
};

const NUEVA_PASSWORD = 'nuevaContraseña456';

test.describe.serial('Cambio de contraseña desde perfil', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
  });

  test('debe cambiar contraseña correctamente y mostrar mensaje de éxito', async ({ page }) => {
    // Login como empleado (esperar respuesta 200 del API para fallar claro si backend/user falla)
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO_USER.password);
    await Promise.all([
      page.waitForResponse(
        (resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200,
        { timeout: 30000 }
      ),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 15000 });
    await expect(page.locator('[data-testid="app.dashboard"]')).toBeVisible({ timeout: 15000 });

    // Ir al perfil
    const profileResponsePromise = page.waitForResponse(
      (resp) => resp.url().includes('/api/v1/user/profile') && resp.status() === 200,
      { timeout: 30000 }
    ).catch(() => null);
    await page.click('[data-testid="app.profileLink"]');
    await expect(page).toHaveURL('/perfil', { timeout: 10000 });
    await expect(page.locator('[data-testid="user.profile.container"]')).toBeVisible({ timeout: 15000 });
    await profileResponsePromise;

    // Abrir formulario de cambio de contraseña
    await expect(page.locator('[data-testid="profile.changePasswordLink"]')).toBeVisible();
    await page.click('[data-testid="profile.changePasswordLink"]');
    await expect(page.locator('[data-testid="profile.changePassword.form"]')).toBeVisible({ timeout: 5000 });

    // Llenar formulario válido
    await page.fill('[data-testid="profile.currentPassword"]', EMPLEADO_USER.password);
    await page.fill('[data-testid="profile.newPassword"]', NUEVA_PASSWORD);
    await page.fill('[data-testid="profile.newPasswordConfirm"]', NUEVA_PASSWORD);

    const changePasswordResponsePromise = page.waitForResponse(
      (resp) => resp.url().includes('/api/v1/auth/change-password') && resp.status() === 200,
      { timeout: 15000 }
    ).catch(() => null);
    await page.click('[data-testid="profile.changePasswordSubmit"]');
    await changePasswordResponsePromise;

    // Mensaje de éxito
    await expect(page.locator('[data-testid="profile.changePassword.success"]')).toBeVisible({ timeout: 10000 });
  });

  test('debe mostrar error cuando la contraseña actual es incorrecta', async ({ page }) => {
    // Login con la nueva contraseña (establecida en el test anterior)
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', NUEVA_PASSWORD);
    await Promise.all([
      page.waitForResponse(
        (resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200,
        { timeout: 30000 }
      ),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 15000 });
    await expect(page.locator('[data-testid="app.dashboard"]')).toBeVisible({ timeout: 15000 });

    // Ir al perfil
    const profileResponsePromise = page.waitForResponse(
      (resp) => resp.url().includes('/api/v1/user/profile') && resp.status() === 200,
      { timeout: 30000 }
    ).catch(() => null);
    await page.click('[data-testid="app.profileLink"]');
    await expect(page).toHaveURL('/perfil', { timeout: 10000 });
    await expect(page.locator('[data-testid="user.profile.container"]')).toBeVisible({ timeout: 15000 });
    await profileResponsePromise;

    // Abrir formulario de cambio de contraseña
    await expect(page.locator('[data-testid="profile.changePasswordLink"]')).toBeVisible();
    await page.click('[data-testid="profile.changePasswordLink"]');
    await expect(page.locator('[data-testid="profile.changePassword.form"]')).toBeVisible({ timeout: 5000 });

    // Contraseña actual incorrecta, nueva y confirmación válidas
    await page.fill('[data-testid="profile.currentPassword"]', 'contraseñaIncorrecta');
    await page.fill('[data-testid="profile.newPassword"]', 'otraNueva789');
    await page.fill('[data-testid="profile.newPasswordConfirm"]', 'otraNueva789');

    const changePasswordResponsePromise = page.waitForResponse(
      (resp) => resp.url().includes('/api/v1/auth/change-password') && resp.status() === 422,
      { timeout: 15000 }
    ).catch(() => null);
    await page.click('[data-testid="profile.changePasswordSubmit"]');
    await changePasswordResponsePromise;

    // Mensaje de error
    await expect(page.locator('[data-testid="profile.changePassword.error"]')).toBeVisible({ timeout: 10000 });
  });

  test('restaura contraseña original para no afectar otros E2E', async ({ page }) => {
    // Login con la contraseña que dejó el primer test
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', NUEVA_PASSWORD);
    await Promise.all([
      page.waitForResponse(
        (resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200,
        { timeout: 30000 }
      ),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 15000 });
    await expect(page.locator('[data-testid="app.dashboard"]')).toBeVisible({ timeout: 15000 });

    await page.click('[data-testid="app.profileLink"]');
    await expect(page).toHaveURL('/perfil', { timeout: 10000 });
    await expect(page.locator('[data-testid="user.profile.container"]')).toBeVisible({ timeout: 15000 });

    await page.click('[data-testid="profile.changePasswordLink"]');
    await expect(page.locator('[data-testid="profile.changePassword.form"]')).toBeVisible({ timeout: 5000 });
    await page.fill('[data-testid="profile.currentPassword"]', NUEVA_PASSWORD);
    await page.fill('[data-testid="profile.newPassword"]', EMPLEADO_USER.password);
    await page.fill('[data-testid="profile.newPasswordConfirm"]', EMPLEADO_USER.password);

    await page.waitForResponse(
      (resp) => resp.url().includes('/api/v1/auth/change-password') && resp.status() === 200,
      { timeout: 15000 }
    ).catch(() => null);
    await page.click('[data-testid="profile.changePasswordSubmit"]');
    await expect(page.locator('[data-testid="profile.changePassword.success"]')).toBeVisible({ timeout: 10000 });
  });
});
