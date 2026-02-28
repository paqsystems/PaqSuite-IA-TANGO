/**
 * Versión de la aplicación.
 * Inyectada en build desde el archivo VERSION (raíz del proyecto).
 * Fuente de verdad: .cursor/rules/23-versioning-and-deploy.md
 */
export const appVersion = (import.meta.env.VITE_APP_VERSION as string) || '0.0.0';
