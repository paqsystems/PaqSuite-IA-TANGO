import { test, expect } from '@playwright/test';

/**
 * Test de ejemplo para verificar la configuración de Playwright
 * 
 * Este test puede ser eliminado una vez que se implementen los tests reales.
 * 
 * Para tests del flujo E2E completo, ver:
 * - specs/flows/e2e-core-flow.md - Flujo E2E documentado
 * - specs/tests/e2e/ - Especificaciones de tests
 */
test.describe('Configuración de Playwright', () => {
  test('debe cargar la aplicación correctamente', async ({ page }) => {
    // Navegar a la página principal
    await page.goto('/');
    
    // Verificar que la página carga (ajustar según la estructura real de la app)
    await expect(page).toHaveTitle(/.*/);
  });

  test('debe tener acceso a la página de login', async ({ page }) => {
    // Navegar a la página de login
    await page.goto('/login');
    
    // Verificar que la página de login existe
    // Nota: Este test fallará hasta que se implemente la página de login
    // con los data-testid correspondientes
    await expect(page).toHaveURL(/.*login/);
  });
});

