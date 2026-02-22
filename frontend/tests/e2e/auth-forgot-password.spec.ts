/**
 * Tests E2E: Flujo recuperación de contraseña (forgot + mensaje genérico)
 *
 * @see TR-004(SH)-recuperación-de-contraseña.md
 */

import { test, expect } from '@playwright/test';

test.describe('Recuperación de contraseña (TR-004)', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
  });

  test('enlace "¿Olvidaste tu contraseña?" lleva a /forgot-password', async ({ page }) => {
    await page.goto('/login');
    await expect(page.locator('[data-testid="auth.forgotPasswordLink"]')).toBeVisible();
    await page.click('[data-testid="auth.forgotPasswordLink"]');
    await expect(page).toHaveURL(/\/forgot-password/);
    await expect(page.locator('[data-testid="forgotPassword.form"]')).toBeVisible();
  });

  test('formulario forgot: enviar código muestra mensaje genérico de éxito', async ({ page }) => {
    await page.goto('/forgot-password');
    await expect(page.locator('[data-testid="forgotPassword.codeOrEmail"]')).toBeVisible();
    await page.fill('[data-testid="forgotPassword.codeOrEmail"]', 'JPEREZ');
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/forgot-password') && resp.status() === 200),
      page.click('[data-testid="forgotPassword.submit"]'),
    ]);
    await expect(page.locator('[data-testid="forgotPassword.success"]')).toBeVisible({ timeout: 5000 });
  });

  test('página reset-password sin token muestra mensaje y enlace a solicitar recuperación', async ({ page }) => {
    await page.goto('/reset-password');
    await expect(page.locator('text=No se encontró un enlace válido')).toBeVisible();
    await expect(page.locator('[data-testid="resetPassword.requestAgain"]')).toBeVisible();
  });
});
