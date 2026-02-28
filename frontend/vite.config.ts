import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';
import fs from 'fs';

/** Lee la versión del archivo VERSION en la raíz del proyecto (fuente de verdad) */
function readAppVersion(): string {
  const versionPath = path.resolve(__dirname, '../VERSION');
  if (fs.existsSync(versionPath)) {
    return fs.readFileSync(versionPath, 'utf-8').trim() || '0.0.0';
  }
  return '0.0.0';
}

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  define: {
    'import.meta.env.VITE_APP_VERSION': JSON.stringify(readAppVersion()),
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    port: 3000,
    host: true,
  },
});

