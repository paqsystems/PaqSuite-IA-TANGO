/**
 * Cliente API con headers de autenticación y X-Company-Id.
 *
 * @see docs/04-tareas/001-Seguridad/TR-002-seleccion-empresa.md
 */

import { getToken, getEmpresaActiva } from '../utils/tokenStorage';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

export interface ApiClientOptions extends RequestInit {
  skipCompanyId?: boolean;
}

/**
 * Fetch con Authorization y X-Company-Id automáticos.
 */
export async function apiFetch(path: string, options: ApiClientOptions = {}): Promise<Response> {
  const { skipCompanyId = false, headers = {}, ...rest } = options;

  const token = getToken();
  const empresaActiva = getEmpresaActiva();

  const defaultHeaders: Record<string, string> = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...(!skipCompanyId && empresaActiva ? { 'X-Company-Id': String(empresaActiva.id) } : {}),
  };

  const mergedHeaders = { ...defaultHeaders, ...headers } as HeadersInit;

  return fetch(`${API_BASE_URL}${path}`, {
    ...rest,
    headers: mergedHeaders,
  });
}
