import { defineConfig, devices } from '@playwright/test';

/**
 * Configuración de Playwright para tests E2E
 * 
 * @see https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
  testDir: './tests/e2e',
  
  /* Timeout global para cada test */
  timeout: 60000, // 60 segundos
  
  /* Timeout para expect assertions */
  expect: {
    timeout: 10000, // 10 segundos
  },
  
  /* Ejecutar tests en archivos en paralelo */
  fullyParallel: true,
  
  /* Fallar el build en CI si accidentalmente dejaste test.only en el código */
  forbidOnly: !!process.env.CI,
  
  /* Reintentar en CI solo si falla */
  retries: process.env.CI ? 2 : 0,
  
  /* Limitar el número de workers para evitar sobrecarga del servidor */
  workers: process.env.CI ? 1 : 4, // Reducir de 8 a 4 workers en local
  
  /* Reporter a usar */
  reporter: [
    ['html'],
    ['list'],
    process.env.CI ? ['github'] : ['list']
  ],
  
  /* Configuración compartida para todos los proyectos */
  use: {
    /* URL base para usar en acciones como `await page.goto('/')`. */
    baseURL: process.env.PLAYWRIGHT_BASE_URL || 'http://localhost:3000',
    
    /* Recopilar trace cuando se reintenta el test fallido */
    trace: 'on-first-retry',
    
    /* Screenshots solo en fallos */
    screenshot: 'only-on-failure',
    
    /* Video solo en fallos */
    video: 'retain-on-failure',
  },

  /* Configurar proyectos para navegadores */
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },

    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] },
    },

    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] },
    },

    /* Test en dispositivos móviles */
    {
      name: 'Mobile Chrome',
      use: { ...devices['Pixel 5'] },
    },
    {
      name: 'Mobile Safari',
      use: { ...devices['iPhone 12'] },
    },
  ],

  /* Ejecutar el servidor de desarrollo local antes de iniciar los tests */
  webServer: {
    command: 'npm run dev',
    url: process.env.PLAYWRIGHT_BASE_URL || 'http://localhost:3000',
    reuseExistingServer: !process.env.CI,
    timeout: 120 * 1000,
    stdout: 'ignore',
    stderr: 'pipe',
  },
});

