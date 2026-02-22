import { defineConfig } from 'vitest/config';
import react from '@vitejs/plugin-react';
import path from 'path';

/**
 * Configuración de Vitest para tests unitarios e integración en el frontend.
 * Excluye tests/e2e/ para que solo Playwright ejecute los E2E (evita conflicto test.describe).
 *
 * @see https://vitest.dev/config/
 */
export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  test: {
    globals: true,
    environment: 'node',
    include: ['src/**/*.{test,spec}.{ts,tsx}'],
    exclude: [
      '**/node_modules/**',
      '**/dist/**',
      '**/tests/e2e/**',
    ],
  },
});
