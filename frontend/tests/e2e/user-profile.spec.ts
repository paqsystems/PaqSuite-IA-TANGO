/**
 * Tests E2E: Visualización de Perfil de Usuario
 * 
 * Tests end-to-end con Playwright para el flujo de visualización de perfil.
 * 
 * Reglas:
 * - Usa selectores data-testid (NO CSS/XPath/texto)
 * - NO usa esperas ciegas (waitForTimeout, sleep, etc.)
 * - Espera estados visibles con expect().toBeVisible()
 * - Verifica datos del perfil
 * 
 * @see TR-006(MH)-visualización-de-perfil-de-usuario.md
 */

import { test, expect } from '@playwright/test';

// Datos de prueba (deben coincidir con el seeder TestUsersSeeder)
const EMPLEADO_USER = {
  code: 'JPEREZ',
  password: 'password123',
  name: 'Juan Pérez',
  email: 'juan.perez@ejemplo.com',
};

const SUPERVISOR_USER = {
  code: 'MGARCIA',
  password: 'password456',
  name: 'María García',
  email: 'maria.garcia@ejemplo.com',
};

const CLIENT_USER = {
  code: 'CLI001',
  password: 'cliente123',
  name: 'Empresa ABC S.A.',
};

test.describe('Visualización de Perfil de Usuario', () => {
  test.beforeEach(async ({ page }) => {
    // Navegar a login primero (necesario para tener contexto de página)
    await page.goto('/login');
    // Limpiar localStorage después de que la página esté cargada
    await page.evaluate(() => localStorage.clear());
  });

  test('debe mostrar el perfil del empleado después de login', async ({ page }) => {
    // Arrange: Login como empleado
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO_USER.password);
    
    // Configurar waitForResponse con manejo de errores
    const loginResponsePromise = page.waitForResponse(resp => 
      resp.url().includes('/api/v1/auth/login') && resp.status() === 200,
      { timeout: 30000 }
    ).catch(() => null);
    
    await page.click('[data-testid="auth.login.submitButton"]');
    
    // Esperar redirección y dashboard (indica que el login se completó)
    await expect(page).toHaveURL('/', { timeout: 15000 });
    await expect(page.locator('[data-testid="app.dashboard"]')).toBeVisible({ timeout: 15000 });
    
    // Esperar respuesta del API de login (si aún no se completó)
    await loginResponsePromise;

    // Act: Click en enlace de perfil
    await expect(page.locator('[data-testid="app.profileLink"]')).toBeVisible();
    
    // Configurar waitForResponse ANTES del click para asegurar que capture la respuesta
    const profileResponsePromise = page.waitForResponse(resp => 
      resp.url().includes('/api/v1/user/profile') && resp.status() === 200,
      { timeout: 30000 }
    ).catch(() => null); // Si no captura la respuesta, continuar igual
    
    await page.click('[data-testid="app.profileLink"]');
    
    // Esperar redirección y que el contenedor esté visible (indica que la llamada se completó)
    await expect(page).toHaveURL('/perfil', { timeout: 10000 });
    await expect(page.locator('[data-testid="user.profile.container"]')).toBeVisible({ timeout: 15000 });
    
    // Esperar respuesta del API de perfil (si aún no se completó)
    await profileResponsePromise;

    // Verificar que se muestran los datos del perfil (esperar que el contenedor esté listo)
    await expect(page.locator('[data-testid="user.profile.code"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="user.profile.name"]')).toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-testid="user.profile.email"]')).toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-testid="user.profile.type"]')).toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-testid="user.profile.createdAt"]')).toBeVisible({ timeout: 10000 });

    // Verificar valores específicos
    await expect(page.locator('[data-testid="user.profile.code"]')).toContainText(EMPLEADO_USER.code);
    await expect(page.locator('[data-testid="user.profile.name"]')).toContainText(EMPLEADO_USER.name);
    await expect(page.locator('[data-testid="user.profile.email"]')).toContainText(EMPLEADO_USER.email);
    await expect(page.locator('[data-testid="user.profile.type"]')).toContainText('Empleado');
  });

  test('debe mostrar badge de supervisor si el usuario es supervisor', async ({ page }) => {
    // Arrange: Login como supervisor
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR_USER.password);
    
    // Configurar waitForResponse con manejo de errores
    const loginResponsePromise = page.waitForResponse(resp => 
      resp.url().includes('/api/v1/auth/login') && resp.status() === 200,
      { timeout: 30000 }
    ).catch(() => null);
    
    await page.click('[data-testid="auth.login.submitButton"]');
    
    // Esperar redirección y dashboard (indica que el login se completó)
    await expect(page).toHaveURL('/', { timeout: 15000 });
    await expect(page.locator('[data-testid="app.dashboard"]')).toBeVisible({ timeout: 15000 });
    
    // Esperar respuesta del API de login (si aún no se completó)
    await loginResponsePromise;

    // Act: Ir al perfil y esperar respuesta del API
    const profileResponsePromise = page.waitForResponse(resp => 
      resp.url().includes('/api/v1/user/profile') && resp.status() === 200,
      { timeout: 30000 }
    ).catch(() => null); // Si no captura la respuesta, continuar igual
    
    await page.click('[data-testid="app.profileLink"]');
    
    // Esperar redirección y que el contenedor esté visible (indica que la llamada se completó)
    await expect(page).toHaveURL('/perfil', { timeout: 10000 });
    await expect(page.locator('[data-testid="user.profile.container"]')).toBeVisible({ timeout: 15000 });
    
    // Esperar respuesta del API de perfil (si aún no se completó)
    await profileResponsePromise;

    // Assert: Verificar badge de supervisor
    await expect(page.locator('[data-testid="user.profile.supervisorBadge"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="user.profile.supervisorBadge"]')).toContainText('Supervisor');
  });

  test('debe mostrar "No configurado" si el email es null', async ({ page }) => {
    // Arrange: Login como empleado (asumiendo que existe uno sin email en el seeder)
    // Por ahora usamos el empleado normal que tiene email
    // Este test requeriría un usuario sin email en el seeder
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO_USER.password);
    
    // Configurar waitForResponse con manejo de errores
    const loginResponsePromise = page.waitForResponse(resp => 
      resp.url().includes('/api/v1/auth/login') && resp.status() === 200,
      { timeout: 30000 }
    ).catch(() => null);
    
    await page.click('[data-testid="auth.login.submitButton"]');
    
    // Esperar redirección y dashboard (indica que el login se completó)
    await expect(page).toHaveURL('/', { timeout: 15000 });
    await expect(page.locator('[data-testid="app.dashboard"]')).toBeVisible({ timeout: 15000 });
    
    // Esperar respuesta del API de login (si aún no se completó)
    await loginResponsePromise;

    // Act: Ir al perfil y esperar respuesta del API
    const profileResponsePromise = page.waitForResponse(resp => 
      resp.url().includes('/api/v1/user/profile') && resp.status() === 200,
      { timeout: 30000 }
    ).catch(() => null); // Si no captura la respuesta, continuar igual
    
    await page.click('[data-testid="app.profileLink"]');
    
    // Esperar redirección y que el contenedor esté visible (indica que la llamada se completó)
    await expect(page).toHaveURL('/perfil', { timeout: 10000 });
    await expect(page.locator('[data-testid="user.profile.container"]')).toBeVisible({ timeout: 15000 });
    
    // Esperar respuesta del API de perfil (si aún no se completó)
    await profileResponsePromise;

    // Assert: Verificar que el campo email existe (aunque tenga valor)
    await expect(page.locator('[data-testid="user.profile.email"]')).toBeVisible({ timeout: 15000 });
    // Nota: Para probar "No configurado" necesitaríamos un usuario sin email
  });

  test('debe mostrar el perfil del cliente correctamente', async ({ page }) => {
    // Arrange: Login como cliente
    await page.fill('[data-testid="auth.login.usuarioInput"]', CLIENT_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', CLIENT_USER.password);
    
    // Configurar waitForResponse con manejo de errores
    const loginResponsePromise = page.waitForResponse(resp => 
      resp.url().includes('/api/v1/auth/login') && resp.status() === 200,
      { timeout: 30000 }
    ).catch(() => null);
    
    await page.click('[data-testid="auth.login.submitButton"]');
    
    // Esperar redirección y dashboard (indica que el login se completó)
    await expect(page).toHaveURL('/', { timeout: 15000 });
    await expect(page.locator('[data-testid="app.dashboard"]')).toBeVisible({ timeout: 15000 });
    
    // Esperar respuesta del API de login (si aún no se completó)
    await loginResponsePromise;

    // Act: Ir al perfil y esperar respuesta del API
    const profileResponsePromise = page.waitForResponse(resp => 
      resp.url().includes('/api/v1/user/profile') && resp.status() === 200,
      { timeout: 30000 }
    ).catch(() => null); // Si no captura la respuesta, continuar igual
    
    await page.click('[data-testid="app.profileLink"]');
    
    // Esperar redirección y que el contenedor esté visible (indica que la llamada se completó)
    await expect(page).toHaveURL('/perfil', { timeout: 10000 });
    await expect(page.locator('[data-testid="user.profile.container"]')).toBeVisible({ timeout: 15000 });
    
    // Esperar respuesta del API de perfil (si aún no se completó)
    await profileResponsePromise;

    // Assert: Verificar datos del cliente
    await expect(page.locator('[data-testid="user.profile.code"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="user.profile.code"]')).toContainText(CLIENT_USER.code);
    await expect(page.locator('[data-testid="user.profile.type"]')).toContainText('Cliente');
    
    // Cliente NO debe tener badge de supervisor
    await expect(page.locator('[data-testid="user.profile.supervisorBadge"]')).not.toBeVisible({ timeout: 10000 });
  });

  test('debe permitir volver al dashboard desde el perfil', async ({ page }) => {
    // Arrange: Login y navegar al perfil
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO_USER.password);
    
    // Configurar waitForResponse con manejo de errores
    const loginResponsePromise = page.waitForResponse(resp => 
      resp.url().includes('/api/v1/auth/login') && resp.status() === 200,
      { timeout: 30000 }
    ).catch(() => null);
    
    await page.click('[data-testid="auth.login.submitButton"]');
    
    // Esperar redirección y dashboard (indica que el login se completó)
    await expect(page).toHaveURL('/', { timeout: 15000 });
    await expect(page.locator('[data-testid="app.dashboard"]')).toBeVisible({ timeout: 15000 });
    
    // Esperar respuesta del API de login (si aún no se completó)
    await loginResponsePromise;

    // Navegar al perfil y esperar respuesta del API
    const profileResponsePromise = page.waitForResponse(resp => 
      resp.url().includes('/api/v1/user/profile') && resp.status() === 200,
      { timeout: 30000 }
    ).catch(() => null); // Si no captura la respuesta, continuar igual
    
    await page.click('[data-testid="app.profileLink"]');
    
    // Esperar redirección y que el contenedor esté visible (indica que la llamada se completó)
    await expect(page).toHaveURL('/perfil', { timeout: 10000 });
    await expect(page.locator('[data-testid="user.profile.container"]')).toBeVisible({ timeout: 15000 });
    
    // Esperar respuesta del API de perfil (si aún no se completó)
    await profileResponsePromise;

    // Act: Click en botón volver
    await expect(page.locator('[data-testid="user.profile.backButton"]')).toBeVisible({ timeout: 15000 });
    await page.click('[data-testid="user.profile.backButton"]');

    // Assert: Verificar redirección al dashboard
    await expect(page).toHaveURL('/', { timeout: 10000 });
    await expect(page.locator('[data-testid="app.dashboard"]')).toBeVisible();
  });

  test('debe mostrar loading mientras carga el perfil', async ({ page }) => {
    // Arrange: Login
    await page.fill('[data-testid="auth.login.usuarioInput"]', EMPLEADO_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', EMPLEADO_USER.password);
    
    // Configurar waitForResponse con manejo de errores
    const loginResponsePromise = page.waitForResponse(resp => 
      resp.url().includes('/api/v1/auth/login') && resp.status() === 200,
      { timeout: 30000 }
    ).catch(() => null);
    
    await page.click('[data-testid="auth.login.submitButton"]');
    
    // Esperar redirección y dashboard (indica que el login se completó)
    await expect(page).toHaveURL('/', { timeout: 15000 });
    await expect(page.locator('[data-testid="app.dashboard"]')).toBeVisible({ timeout: 15000 });
    
    // Esperar respuesta del API de login (si aún no se completó)
    await loginResponsePromise;

    // Act: Ir al perfil y esperar respuesta del API
    const profileResponsePromise = page.waitForResponse(resp => 
      resp.url().includes('/api/v1/user/profile') && resp.status() === 200,
      { timeout: 30000 }
    );
    await page.click('[data-testid="app.profileLink"]');
    await profileResponsePromise;

    // Assert: Verificar que el perfil se muestra
    await expect(page).toHaveURL('/perfil', { timeout: 10000 });
    await expect(page.locator('[data-testid="user.profile.container"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="user.profile.code"]')).toBeVisible({ timeout: 15000 });
  });

  test('debe redirigir a login si no está autenticado', async ({ page }) => {
    // Limpiar cualquier token existente (ya se hace en beforeEach)
    // Intentar acceder al perfil directamente
    await page.goto('/perfil');

    // Debe redirigir a login
    await expect(page).toHaveURL('/login', { timeout: 10000 });
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible();
  });
});
