/**
 * Servicio de preferencias de usuario.
 * @see docs/04-tareas/000-Generalidades/TR-003-apertura-menu-misma-o-nueva-pestana.md
 * @see docs/04-tareas/000-Generalidades/TR-004-seleccion-idioma.md
 */

import { apiFetch } from '../../../shared/api/apiClient';
import {
  getUserData,
  setUserData,
  getLocale,
  setLocale as setLocaleStorage,
} from '../../../shared/utils/tokenStorage';

export interface UserPreferences {
  locale: string;
  menuAbrirNuevaPestana: boolean;
}

export async function getPreferences(): Promise<UserPreferences | null> {
  try {
    const res = await apiFetch('/v1/user/preferences');
    const data = await res.json();
    if (res.ok && data.error === 0) {
      return data.resultado as UserPreferences;
    }
  } catch {
    // Fallback a datos locales
  }
  const user = getUserData();
  if (user) {
    return {
      locale: user.locale ?? getLocale(),
      menuAbrirNuevaPestana: user.menuAbrirNuevaPestana ?? false,
    };
  }
  return null;
}

export async function updatePreferences(prefs: Partial<UserPreferences>): Promise<boolean> {
  try {
    const res = await apiFetch('/v1/user/preferences', {
      method: 'PUT',
      body: JSON.stringify(prefs),
    });
    const data = await res.json();
    if (res.ok && data.error === 0) {
      const user = getUserData();
      if (user) {
        const updated = { ...user };
        if (prefs.locale !== undefined) {
          updated.locale = prefs.locale;
          setLocaleStorage(prefs.locale);
        }
        if (prefs.menuAbrirNuevaPestana !== undefined) {
          updated.menuAbrirNuevaPestana = prefs.menuAbrirNuevaPestana;
        }
        setUserData(updated);
      }
      return true;
    }
  } catch {
    //
  }
  return false;
}
