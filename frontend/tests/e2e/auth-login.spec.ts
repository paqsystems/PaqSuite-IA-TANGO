/**
 * Test E2E: Login de usuario
 *
 * Usa credenciales del TestUsersSeeder (JPEREZ / password123).
 * Requiere backend en ejecución (php artisan serve) y DB con seeders.
 *
 * @see docs/backend/autenticacion.md
 */

import { test, expect } from '@playwright/test';

test.describe('Login', () => {
  test('login exitoso redirige al dashboard', async ({ page }) => {
    await page.goto('/login');

    await page.getByTestId('login.codigo').fill('EMP');
    await page.getByTestId('login.password').fill('emple123');
    await page.getByTestId('login.submit').click();

    await expect(page).toHaveURL(/\/(\?.*)?$/);
    await expect(page.getByTestId('dashboard.container')).toBeVisible({ timeout: 10000 });
  });

  test('login con credenciales inválidas muestra error', async ({ page }) => {
    await page.goto('/login');

    await page.getByTestId('login.codigo').fill('EMP');
    await page.getByTestId('login.password').fill('contraseña_incorrecta');
    await page.getByTestId('login.submit').click();

    await expect(page.getByTestId('auth.login.errorMessage')).toBeVisible();
    await expect(page).toHaveURL(/\/login/);
  });
});
