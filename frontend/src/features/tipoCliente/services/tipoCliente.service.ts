/**
 * Service: tipoCliente.service
 *
 * ABM de tipos de cliente (listado paginado, crear, editar, eliminar). TR-014, TR-015, TR-016, TR-017.
 */

import { getToken } from '../../../shared/utils/tokenStorage';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

export interface TipoClienteListItem {
  id: number;
  code: string;
  descripcion: string;
  activo: boolean;
  inhabilitado: boolean;
  created_at?: string;
  updated_at?: string;
}

export interface TiposClienteListParams {
  page?: number;
  page_size?: number;
  search?: string;
  activo?: boolean | null;
  inhabilitado?: boolean | null;
  sort?: string;
  sort_dir?: 'asc' | 'desc';
}

export interface TiposClienteListResult {
  success: boolean;
  data?: TipoClienteListItem[];
  pagination?: { page: number; page_size: number; total: number; total_pages: number };
  errorMessage?: string;
  errorCode?: number;
}

export interface GetTipoClienteResult {
  success: boolean;
  data?: TipoClienteListItem;
  errorMessage?: string;
  errorCode?: number;
}

export interface CreateTipoClienteBody {
  code: string;
  descripcion: string;
  activo?: boolean;
  inhabilitado?: boolean;
}

export interface UpdateTipoClienteBody {
  descripcion: string;
  activo?: boolean;
  inhabilitado?: boolean;
}

export const ERROR_TIENE_CLIENTES = 2115;

async function request<T>(
  url: string,
  options: RequestInit = {}
): Promise<{ data: T & { error?: number; respuesta?: string; resultado?: unknown }; status: number }> {
  const token = getToken();
  const headers: HeadersInit = {
    Accept: 'application/json',
    ...(options.headers as Record<string, string>),
  };
  if (token) (headers as Record<string, string>)['Authorization'] = `Bearer ${token}`;
  const response = await fetch(url, { ...options, headers });
  const data = await response.json().catch(() => ({}));
  return { data: data as T & { error?: number; respuesta?: string; resultado?: unknown }, status: response.status };
}

/**
 * Listado paginado de tipos de cliente (ABM). Enviar page para obtener formato paginado.
 */
export async function getTiposClienteList(params: TiposClienteListParams): Promise<TiposClienteListResult> {
  const searchParams = new URLSearchParams();
  if (params.page != null) searchParams.set('page', String(params.page));
  if (params.page_size != null) searchParams.set('page_size', String(params.page_size));
  if (params.search) searchParams.set('search', params.search);
  if (params.activo !== undefined && params.activo !== null) searchParams.set('activo', params.activo ? 'true' : 'false');
  if (params.inhabilitado !== undefined && params.inhabilitado !== null) searchParams.set('inhabilitado', params.inhabilitado ? 'true' : 'false');
  if (params.sort) searchParams.set('sort', params.sort);
  if (params.sort_dir) searchParams.set('sort_dir', params.sort_dir);

  const url = `${API_BASE_URL}/v1/tipos-cliente?${searchParams.toString()}`;
  const { data, status } = await request<{ error: number; respuesta: string; resultado?: { items: TipoClienteListItem[]; total: number; page: number; page_size: number } }>(url);

  if (status === 403) {
    return { success: false, errorMessage: data.respuesta || 'Sin permiso', errorCode: data.error };
  }
  if (status === 401) {
    return { success: false, errorMessage: 'No autenticado', errorCode: 3001 };
  }
  if (status !== 200 || data.error !== 0 || !data.resultado) {
    return { success: false, errorMessage: data.respuesta || 'Error al obtener tipos de cliente', errorCode: data.error };
  }
  const r = data.resultado as { items: TipoClienteListItem[]; total: number; page: number; page_size: number };
  const totalPages = r.page_size > 0 ? Math.ceil(r.total / r.page_size) : 0;
  return {
    success: true,
    data: r.items,
    pagination: { page: r.page, page_size: r.page_size, total: r.total, total_pages: totalPages },
  };
}

/**
 * Obtener un tipo de cliente por ID.
 */
export async function getTipoCliente(id: number): Promise<GetTipoClienteResult> {
  const url = `${API_BASE_URL}/v1/tipos-cliente/${id}`;
  const { data, status } = await request<{ error: number; respuesta: string; resultado?: TipoClienteListItem }>(url);
  if (status === 200 && data.error === 0 && data.resultado) {
    return { success: true, data: data.resultado as TipoClienteListItem };
  }
  if (status === 403) return { success: false, errorMessage: data.respuesta || 'Sin permiso', errorCode: data.error };
  if (status === 404) return { success: false, errorMessage: data.respuesta || 'No encontrado', errorCode: data.error };
  return { success: false, errorMessage: data.respuesta || 'Error', errorCode: data.error };
}

/**
 * Crear tipo de cliente.
 */
export async function createTipoCliente(body: CreateTipoClienteBody): Promise<{ success: boolean; data?: TipoClienteListItem; errorMessage?: string; errorCode?: number }> {
  const url = `${API_BASE_URL}/v1/tipos-cliente`;
  const { data, status } = await request<{ error: number; respuesta: string; resultado?: TipoClienteListItem }>(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      code: body.code.trim(),
      descripcion: body.descripcion.trim(),
      activo: body.activo !== false,
      inhabilitado: body.inhabilitado === true,
    }),
  });
  if (status === 201 && data.error === 0 && data.resultado) {
    return { success: true, data: data.resultado as TipoClienteListItem };
  }
  if (status === 403) return { success: false, errorMessage: data.respuesta || 'Sin permiso', errorCode: data.error };
  if (status === 409 || status === 422) return { success: false, errorMessage: data.respuesta || 'Error', errorCode: data.error };
  return { success: false, errorMessage: data.respuesta || 'Error', errorCode: data.error };
}

/**
 * Actualizar tipo de cliente.
 */
export async function updateTipoCliente(id: number, body: UpdateTipoClienteBody): Promise<{ success: boolean; data?: TipoClienteListItem; errorMessage?: string; errorCode?: number }> {
  const url = `${API_BASE_URL}/v1/tipos-cliente/${id}`;
  const { data, status } = await request<{ error: number; respuesta: string; resultado?: TipoClienteListItem }>(url, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      descripcion: body.descripcion.trim(),
      activo: body.activo,
      inhabilitado: body.inhabilitado,
    }),
  });
  if (status === 200 && data.error === 0 && data.resultado) {
    return { success: true, data: data.resultado as TipoClienteListItem };
  }
  if (status === 403) return { success: false, errorMessage: data.respuesta || 'Sin permiso', errorCode: data.error };
  if (status === 404) return { success: false, errorMessage: data.respuesta || 'No encontrado', errorCode: data.error };
  if (status === 422) return { success: false, errorMessage: data.respuesta || 'Error', errorCode: data.error };
  return { success: false, errorMessage: data.respuesta || 'Error', errorCode: data.error };
}

/**
 * Eliminar tipo de cliente. Error 2115 si tiene clientes asociados.
 */
export async function deleteTipoCliente(id: number): Promise<{ success: boolean; errorMessage?: string; errorCode?: number }> {
  const url = `${API_BASE_URL}/v1/tipos-cliente/${id}`;
  const { data, status } = await request<{ error: number; respuesta: string }>(url, { method: 'DELETE' });
  if (status === 200 && data.error === 0) return { success: true };
  if (status === 403) return { success: false, errorMessage: data.respuesta || 'Sin permiso', errorCode: data.error };
  if (status === 404) return { success: false, errorMessage: data.respuesta || 'No encontrado', errorCode: data.error };
  if (status === 422) return { success: false, errorMessage: data.respuesta || 'No se puede eliminar', errorCode: data.error };
  return { success: false, errorMessage: data.respuesta || 'Error', errorCode: data.error };
}
