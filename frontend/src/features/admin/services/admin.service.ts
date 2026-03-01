/**
 * Servicio API para módulo de administración.
 * @see docs/04-tareas/001-Seguridad/TR-010-administracion-usuarios.md
 */

import { apiFetch } from '../../../shared/api/apiClient';

const PREFIX = '/v1/admin';

export interface ApiListResult<T> {
  items: T[];
  page?: number;
  page_size?: number;
  total?: number;
  total_pages?: number;
}

export interface ApiResponse<T> {
  error: number;
  respuesta: string;
  resultado: T;
}

async function handleResponse<T>(res: Response): Promise<ApiResponse<T>> {
  const data = await res.json();
  if (!res.ok) {
    throw { status: res.status, ...data };
  }
  return data as ApiResponse<T>;
}

export const adminUsersApi = {
  list: (params?: Record<string, string>) =>
    apiFetch(`${PREFIX}/users?${new URLSearchParams(params ?? {}).toString()}`).then((r) =>
      handleResponse<ApiListResult<Record<string, unknown>>>(r)
    ),
  get: (id: number) => apiFetch(`${PREFIX}/users/${id}`).then(handleResponse),
  create: (body: Record<string, unknown>) =>
    apiFetch(`${PREFIX}/users`, { method: 'POST', body: JSON.stringify(body) }).then(handleResponse),
  update: (id: number, body: Record<string, unknown>) =>
    apiFetch(`${PREFIX}/users/${id}`, { method: 'PUT', body: JSON.stringify(body) }).then(handleResponse),
  inhabilitar: (id: number) =>
    apiFetch(`${PREFIX}/users/${id}/inhabilitar`, { method: 'PUT' }).then(handleResponse),
};

export const adminEmpresasApi = {
  list: (params?: Record<string, string>) =>
    apiFetch(`${PREFIX}/empresas?${new URLSearchParams(params ?? {}).toString()}`).then((r) =>
      handleResponse<ApiListResult<Record<string, unknown>>>(r)
    ),
  get: (id: number) => apiFetch(`${PREFIX}/empresas/${id}`).then(handleResponse),
  create: (body: Record<string, unknown>) =>
    apiFetch(`${PREFIX}/empresas`, { method: 'POST', body: JSON.stringify(body) }).then(handleResponse),
  update: (id: number, body: Record<string, unknown>) =>
    apiFetch(`${PREFIX}/empresas/${id}`, { method: 'PUT', body: JSON.stringify(body) }).then(handleResponse),
};

export const adminGruposEmpresariosApi = {
  list: () =>
    apiFetch(`${PREFIX}/grupos-empresarios`).then((r) =>
      handleResponse<ApiListResult<Record<string, unknown>>>(r)
    ),
  get: (id: number) => apiFetch(`${PREFIX}/grupos-empresarios/${id}`).then(handleResponse),
  create: (body: { descripcion: string; empresaIds: number[] }) =>
    apiFetch(`${PREFIX}/grupos-empresarios`, {
      method: 'POST',
      body: JSON.stringify(body),
    }).then(handleResponse),
  update: (id: number, body: { descripcion: string; empresaIds: number[] }) =>
    apiFetch(`${PREFIX}/grupos-empresarios/${id}`, {
      method: 'PUT',
      body: JSON.stringify(body),
    }).then(handleResponse),
  delete: (id: number) =>
    apiFetch(`${PREFIX}/grupos-empresarios/${id}`, { method: 'DELETE' }).then(async (r) => {
      if (r.status === 204) return { error: 0, respuesta: 'OK', resultado: {} };
      const data = await r.json();
      if (!r.ok) throw { status: r.status, ...data };
      return data;
    }),
};

export const adminRolesApi = {
  list: () => apiFetch(`${PREFIX}/roles`).then((r) => handleResponse<{ items: Record<string, unknown>[] }>(r)),
  get: (id: number) => apiFetch(`${PREFIX}/roles/${id}`).then(handleResponse),
  create: (body: Record<string, unknown>) =>
    apiFetch(`${PREFIX}/roles`, { method: 'POST', body: JSON.stringify(body) }).then(handleResponse),
  update: (id: number, body: Record<string, unknown>) =>
    apiFetch(`${PREFIX}/roles/${id}`, { method: 'PUT', body: JSON.stringify(body) }).then(handleResponse),
  delete: (id: number) => apiFetch(`${PREFIX}/roles/${id}`, { method: 'DELETE' }).then(handleResponse),
  getAtributos: (id: number) => apiFetch(`${PREFIX}/roles/${id}/atributos`).then(handleResponse),
  updateAtributos: (id: number, body: { items: { idOpcionMenu: number; permisoAlta: boolean; permisoBaja: boolean; permisoModi: boolean; permisoRepo: boolean }[] }) =>
    apiFetch(`${PREFIX}/roles/${id}/atributos`, {
      method: 'PUT',
      body: JSON.stringify(body),
    }).then(handleResponse),
};

export const adminPermisosApi = {
  list: (params?: Record<string, string>) =>
    apiFetch(`${PREFIX}/permisos?${new URLSearchParams(params ?? {}).toString()}`).then((r) =>
      handleResponse<{ items: Record<string, unknown>[] }>(r)
    ),
  create: (body: Record<string, unknown>) =>
    apiFetch(`${PREFIX}/permisos`, { method: 'POST', body: JSON.stringify(body) }).then(handleResponse),
  update: (id: number, body: Record<string, unknown>) =>
    apiFetch(`${PREFIX}/permisos/${id}`, { method: 'PUT', body: JSON.stringify(body) }).then(handleResponse),
  delete: (id: number) => apiFetch(`${PREFIX}/permisos/${id}`, { method: 'DELETE' }).then(handleResponse),
};
