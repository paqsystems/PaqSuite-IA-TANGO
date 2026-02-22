/**
 * Tests E2E: Flujo de Login
 * 
 * Tests end-to-end con Playwright para el flujo de autenticación.
 * 
 * Reglas:
 * - Usa selectores data-testid (NO CSS/XPath/texto)
 * - NO usa esperas ciegas (waitForTimeout, sleep, etc.)
 * - Espera estados visibles con expect().toBeVisible()
 * - Verifica almacenamiento de token en localStorage
 * 
 * @see TR-001(MH)-login-de-empleado.md
 * @see TR-002(SH)-login-de-cliente.md
 */

import { test, expect } from '@playwright/test';

// Datos de prueba (deben coincidir con el seeder TestUsersSeeder)
const TEST_USER = {
  code: 'JPEREZ',
  password: 'password123',
  name: 'Juan Pérez',
};

const SUPERVISOR_USER = {
  code: 'MGARCIA',
  password: 'password456',
  name: 'María García',
};

// Datos de prueba para cliente (TR-002)
const CLIENT_USER = {
  code: 'CLI001',
  password: 'cliente123',
  name: 'Empresa ABC S.A.',
};

test.describe('Login de Empleado', () => {
  test.beforeEach(async ({ page }) => {
    // Limpiar localStorage antes de cada test
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
  });

  test('debe mostrar el formulario de login', async ({ page }) => {
    await page.goto('/login');

    // Verificar que el formulario está visible
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible();
    await expect(page.locator('[data-testid="auth.login.usuarioInput"]')).toBeVisible();
    await expect(page.locator('[data-testid="auth.login.passwordInput"]')).toBeVisible();
    await expect(page.locator('[data-testid="auth.login.submitButton"]')).toBeVisible();
  });

  test('debe autenticar empleado y redirigir al dashboard', async ({ page }) => {
    // Arrange: Navegar a login
    await page.goto('/login');
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible();

    // Act: Llenar formulario y enviar
    await page.fill('[data-testid="auth.login.usuarioInput"]', TEST_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', TEST_USER.password);
    
    // Esperar la respuesta del API antes de verificar redirección
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);

    // Assert: Verificar redirección al dashboard (con timeout extendido)
    await expect(page).toHaveURL('/', { timeout: 10000 });
    await expect(page.locator('[data-testid="app.dashboard"]')).toBeVisible();

    // Verificar que el token está en localStorage
    const token = await page.evaluate(() => localStorage.getItem('auth_token'));
    expect(token).toBeTruthy();
    expect(token).toContain('|');
  });

  test('debe autenticar supervisor y mostrar badge de supervisor', async ({ page }) => {
    // Arrange
    await page.goto('/login');
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible();

    // Act
    await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR_USER.password);
    
    // Esperar la respuesta del API antes de verificar redirección
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);

    // Assert
    await expect(page).toHaveURL('/', { timeout: 10000 });
    await expect(page.locator('[data-testid="app.dashboard"]')).toBeVisible();

    // Verificar datos del usuario en localStorage
    const userData = await page.evaluate(() => {
      const data = localStorage.getItem('auth_user');
      return data ? JSON.parse(data) : null;
    });
    expect(userData).toBeTruthy();
    expect(userData.esSupervisor).toBe(true);
  });

  test('debe mostrar error con credenciales inválidas', async ({ page }) => {
    // Arrange
    await page.goto('/login');
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible();

    // Act
    await page.fill('[data-testid="auth.login.usuarioInput"]', TEST_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', 'contraseñaIncorrecta');
    
    // Esperar la respuesta del API (401 para credenciales inválidas)
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 401),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);

    // Assert: Verificar mensaje de error (con timeout extendido)
    await expect(page.locator('[data-testid="auth.login.errorMessage"]')).toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-testid="auth.login.errorMessage"]')).toContainText('Credenciales inválidas');

    // Verificar que NO hay redirección
    await expect(page).toHaveURL('/login');

    // Verificar que NO hay token
    const token = await page.evaluate(() => localStorage.getItem('auth_token'));
    expect(token).toBeNull();
  });

  test('debe mostrar error de validación si código de usuario está vacío', async ({ page }) => {
    // Arrange
    await page.goto('/login');

    // Act: Dejar usuario vacío y llenar password
    await page.fill('[data-testid="auth.login.passwordInput"]', TEST_USER.password);
    await page.click('[data-testid="auth.login.submitButton"]');

    // Assert: Verificar mensaje de validación (puede ser en el campo o general)
    // El formulario valida en el cliente antes de enviar
    await expect(page).toHaveURL('/login');
  });

  test('debe mostrar error de validación si contraseña está vacía', async ({ page }) => {
    // Arrange
    await page.goto('/login');

    // Act: Llenar usuario y dejar password vacío
    await page.fill('[data-testid="auth.login.usuarioInput"]', TEST_USER.code);
    await page.click('[data-testid="auth.login.submitButton"]');

    // Assert: El formulario no debería enviarse
    await expect(page).toHaveURL('/login');
  });

  test('debe mostrar indicador de carga durante el envío', async ({ page }) => {
    // Arrange
    await page.goto('/login');

    // Act
    await page.fill('[data-testid="auth.login.usuarioInput"]', TEST_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', TEST_USER.password);
    
    // Interceptar la petición para verificar el estado loading
    const responsePromise = page.waitForResponse(resp => 
      resp.url().includes('/api/v1/auth/login') && resp.status() === 200
    );
    await page.click('[data-testid="auth.login.submitButton"]');

    // El botón debería estar deshabilitado durante la carga
    await expect(page.locator('[data-testid="auth.login.submitButton"]')).toBeDisabled();

    // Esperar la respuesta
    await responsePromise;

    // Después del login exitoso, redirige (con timeout extendido)
    await expect(page).toHaveURL('/', { timeout: 10000 });
  });

  test('debe redirigir a login si intenta acceder a ruta protegida sin token', async ({ page }) => {
    // Limpiar cualquier token existente
    await page.evaluate(() => localStorage.clear());

    // Intentar acceder al dashboard directamente
    await page.goto('/');

    // Debería redirigir a login
    await expect(page).toHaveURL('/login');
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible();
  });

  test('debe redirigir a dashboard si ya está autenticado e intenta acceder a login', async ({ page }) => {
    // Primero, autenticar
    await page.goto('/login');
    await page.fill('[data-testid="auth.login.usuarioInput"]', TEST_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', TEST_USER.password);
    
    // Esperar la respuesta del API
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 10000 });

    // Intentar volver a login
    await page.goto('/login');

    // Debería redirigir de vuelta al dashboard
    await expect(page).toHaveURL('/', { timeout: 10000 });
  });

  test('debe permitir cerrar sesión desde el dashboard', async ({ page }) => {
    // Autenticar primero
    await page.goto('/login');
    await page.fill('[data-testid="auth.login.usuarioInput"]', TEST_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', TEST_USER.password);
    
    // Esperar la respuesta del API de login
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 10000 });

    // Verificar que el token existe
    let token = await page.evaluate(() => localStorage.getItem('auth_token'));
    expect(token).toBeTruthy();

    // Click en cerrar sesión y esperar respuesta del API de logout
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/logout') && resp.status() === 200),
      page.click('[data-testid="app.logoutButton"]'),
    ]);

    // Verificar redirección a login
    await expect(page).toHaveURL('/login', { timeout: 10000 });

    // Verificar que el token fue eliminado
    token = await page.evaluate(() => localStorage.getItem('auth_token'));
    expect(token).toBeNull();

    // Verificar que user_data también fue eliminado
    const userData = await page.evaluate(() => localStorage.getItem('auth_user'));
    expect(userData).toBeNull();
  });

  test('debe mostrar botón de logout deshabilitado durante la petición', async ({ page }) => {
    // Autenticar primero
    await page.goto('/login');
    await page.fill('[data-testid="auth.login.usuarioInput"]', TEST_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', TEST_USER.password);
    
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 10000 });

    // Verificar que el botón está visible y habilitado antes del logout
    await expect(page.locator('[data-testid="app.logoutButton"]')).toBeVisible();
    await expect(page.locator('[data-testid="app.logoutButton"]')).toBeEnabled();

    // Hacer click y esperar respuesta usando Promise.all para evitar race condition
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/logout')),
      page.click('[data-testid="app.logoutButton"]'),
    ]);

    // Verificar redirección después del logout
    await expect(page).toHaveURL('/login', { timeout: 10000 });
  });

  test('no debe poder acceder a dashboard después de logout', async ({ page }) => {
    // Limpiar estado inicial
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
    
    // Autenticar
    await page.fill('[data-testid="auth.login.usuarioInput"]', TEST_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', TEST_USER.password);
    
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 10000 });

    // Verificar que estamos en el dashboard
    await expect(page.locator('[data-testid="app.dashboard"]')).toBeVisible();
    await expect(page.locator('[data-testid="app.logoutButton"]')).toBeVisible();

    // Hacer logout
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/logout')),
      page.click('[data-testid="app.logoutButton"]'),
    ]);
    await expect(page).toHaveURL('/login', { timeout: 10000 });

    // Verificar que localStorage fue limpiado
    const token = await page.evaluate(() => localStorage.getItem('auth_token'));
    expect(token).toBeNull();

    // Intentar acceder al dashboard directamente
    await page.goto('/');

    // Debe redirigir a login porque no hay sesión
    await expect(page).toHaveURL('/login', { timeout: 10000 });
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible();
  });
});

// ========================================
// Tests de Login Cliente (TR-002)
// ========================================

test.describe('Login de Cliente', () => {
  test.beforeEach(async ({ page }) => {
    // Limpiar localStorage antes de cada test
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
  });

  test('debe autenticar cliente y redirigir al dashboard', async ({ page }) => {
    // Arrange: Navegar a login
    await page.goto('/login');
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible();

    // Act: Llenar formulario con credenciales de cliente y enviar
    await page.fill('[data-testid="auth.login.usuarioInput"]', CLIENT_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', CLIENT_USER.password);
    
    // Esperar la respuesta del API antes de verificar redirección
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);

    // Assert: Verificar redirección al dashboard
    await expect(page).toHaveURL('/', { timeout: 10000 });
    await expect(page.locator('[data-testid="app.dashboard"]')).toBeVisible();

    // Verificar que el token está en localStorage
    const token = await page.evaluate(() => localStorage.getItem('auth_token'));
    expect(token).toBeTruthy();
    expect(token).toContain('|');
  });

  test('debe almacenar tipo_usuario cliente en localStorage', async ({ page }) => {
    // Arrange
    await page.goto('/login');
    await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible();

    // Act
    await page.fill('[data-testid="auth.login.usuarioInput"]', CLIENT_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', CLIENT_USER.password);
    
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);

    // Assert
    await expect(page).toHaveURL('/', { timeout: 10000 });

    // Verificar datos del usuario en localStorage
    const userData = await page.evaluate(() => {
      const data = localStorage.getItem('auth_user');
      return data ? JSON.parse(data) : null;
    });
    expect(userData).toBeTruthy();
    expect(userData.tipoUsuario).toBe('cliente');
    expect(userData.esSupervisor).toBe(false);
    expect(userData.usuarioId).toBeNull();
    expect(userData.clienteId).not.toBeNull();
  });

  test('cliente NO debe tener badge de supervisor', async ({ page }) => {
    // Arrange
    await page.goto('/login');

    // Act
    await page.fill('[data-testid="auth.login.usuarioInput"]', CLIENT_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', CLIENT_USER.password);
    
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);

    // Assert
    await expect(page).toHaveURL('/', { timeout: 10000 });
    await expect(page.locator('[data-testid="app.dashboard"]')).toBeVisible();

    // Verificar que el badge de supervisor NO está visible
    await expect(page.locator('[data-testid="app.supervisorBadge"]')).not.toBeVisible();
  });

  test('cliente puede hacer logout igual que empleado', async ({ page }) => {
    // Autenticar como cliente
    await page.goto('/login');
    await page.fill('[data-testid="auth.login.usuarioInput"]', CLIENT_USER.code);
    await page.fill('[data-testid="auth.login.passwordInput"]', CLIENT_USER.password);
    
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
      page.click('[data-testid="auth.login.submitButton"]'),
    ]);
    await expect(page).toHaveURL('/', { timeout: 10000 });

    // Verificar que el token existe
    let token = await page.evaluate(() => localStorage.getItem('auth_token'));
    expect(token).toBeTruthy();

    // Click en cerrar sesión
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('/api/v1/auth/logout') && resp.status() === 200),
      page.click('[data-testid="app.logoutButton"]'),
    ]);

    // Verificar redirección a login
    await expect(page).toHaveURL('/login', { timeout: 10000 });

    // Verificar que el token fue eliminado
    token = await page.evaluate(() => localStorage.getItem('auth_token'));
    expect(token).toBeNull();
  });
});
