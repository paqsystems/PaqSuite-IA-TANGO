/**
 * ThemeLoader - Carga dinámica de tema DevExtreme según empresa activa.
 * @see docs/04-tareas/000-Generalidades/TR-005-seleccion-apariencias.md
 */

import { useEffect } from 'react';
import { getEmpresaActiva } from '../utils/tokenStorage';

const FALLBACK_THEME = 'light';

export function getThemeForEmpresa(): string {
  const empresa = getEmpresaActiva();
  const theme = empresa?.theme;
  if (theme && ['light', 'dark', 'generic.light', 'generic.dark', 'material.blue.light', 'material.blue.dark'].includes(theme)) {
    return theme.includes('.') ? theme : `${theme}`;
  }
  return FALLBACK_THEME;
}

/**
 * Aplica el tema de la empresa activa. Por ahora solo light/dark.
 */
export function useThemeLoader(): void {
  const empresa = getEmpresaActiva();
  useEffect(() => {
    const theme = getThemeForEmpresa();
    document.documentElement.setAttribute('data-theme', theme === 'dark' || theme.includes('dark') ? 'dark' : 'light');
  }, [empresa?.id]);
}
