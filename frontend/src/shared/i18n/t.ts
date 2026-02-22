/**
 * Función de traducción t() con fallback obligatorio
 * Integrada con i18next (react-i18next)
 *
 * Regla: TODOS los textos visibles al usuario DEBEN usar esta función.
 *
 * @param key - Clave de traducción con notación de puntos (ej: "auth.login.title")
 * @param fallback - Texto legible en español que se muestra si no hay traducción
 * @param params - Parámetros opcionales para interpolación (ej: { count: 5 })
 * @returns Texto traducido o fallback
 *
 * @example
 * t("auth.login.title", "Iniciar Sesión")
 * t("tasks.summary.totalHours", "Total: {{hours}} horas", { hours: 2.5 })
 */
import i18n from '../../i18n';

export function t(key: string, fallback: string, params?: Record<string, any>): string {
  if (!fallback) {
    console.warn(`[i18n] Missing fallback for key: ${key}`);
    return key;
  }

  const result = i18n.t(key, {
    defaultValue: fallback,
    ...params
  });

  return result || fallback;
}

