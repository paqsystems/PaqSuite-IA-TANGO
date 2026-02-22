/**
 * Service: tipoTarea.service
 *
 * ABM de tipos de tarea (listado paginado, crear, editar, eliminar, detalle). TR-023 a TR-027.
 */

import { getToken } from '../../../shared/utils/tokenStorage';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

export interface TipoTareaListItem {
  id: number;
  code: string;
  descripcion: string;
  is_generico: boolean;
  is_default: boolean;
  activo: boolean;
  inhabilitado: boolean;
  created_at?: string;
  updated_at?: string;
}

export interface TipoTareaListParams {
  page?: number;
  page_size?: number;
  search?: string;
  is_generico?: boolean | null;
  is_default?: boolean | null;
  activo?: boolean | null;
  inhabilitado?: boolean | null;
  sort?: string;
  sort_dir?: 'asc' | 'desc';
}

export interface TipoTareaListResult {
  success: boolean;
  data?: TipoTareaListItem[];
  pagination?: { page: number; page_size: number; total: number; total_pages: number };
  errorMessage?: string;
  errorCode?: number;
}

export interface GetTipoTareaResult {
  success: boolean;
  data?: TipoTareaListItem & { clientes?: { id: number; code: string; nombre: string }[] };
  errorMessage?: string;
  errorCode?: number;
}

export interface CreateTipoTareaBody {
  code: string;
  descripcion: string;
  is_generico?: boolean;
  is_default?: boolean;
  activo?: boolean;
  inhabilitado?: boolean;
}

export interface UpdateTipoTareaBody {
  descripcion: string;
  is_generico?: boolean;
  is_default?: boolean;
  activo?: boolean;
  inhabilitado?: boolean;
}

export const ERROR_EN_USO = 2114;
export const ERROR_YA_HAY_POR_DEFECTO = 2117;

async function request<T>(
  url: string,
  options: RequestInit = {}
): Promise<{ data: T & { error?: number; respuesta?: string; resultado?: unknown }; status: number }> {
  const token = getToken();
  const headers: HeadersInit = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    ...(options.headers as Record<string, string>),
  };
  if (token) (headers as Record<string, string>)['Authorization'] = `Bearer ${token}`;
  const response = await fetch(url, { ...options, headers });
  const data = await response.json().catch(() => ({}));
  return { data: data as T & { error?: number; respuesta?: string; resultado?: unknown }, status: response.status };
}

/**
 * Listado paginado de tipos de tarea (ABM). Enviar page para obtener formato paginado.
 */
export async function getTiposTareaList(params: TipoTareaListParams): Promise<TipoTareaListResult> {
  const searchParams = new URLSearchParams();
  if (params.page != null) searchParams.set('page', String(params.page));
  if (params.page_size != null) searchParams.set('page_size', String(params.page_size));
  if (params.search) searchParams.set('search', params.search);
  if (params.is_generico !== undefined && params.is_generico !== null) searchParams.set('is_generico', params.is_generico ? 'true' : 'false');
  if (params.is_default !== undefined && params.is_default !== null) searchParams.set('is_default', params.is_default ? 'true' : 'false');
  if (params.activo !== undefined && params.activo !== null) searchParams.set('activo', params.activo ? 'true' : 'false');
  if (params.inhabilitado !== undefined && params.inhabilitado !== null) searchParams.set('inhabilitado', params.inhabilitado ? 'true' : 'false');
  if (params.sort) searchParams.set('sort', params.sort);
  if (params.sort_dir) searchParams.set('sort_dir', params.sort_dir);

  const url = `${API_BASE_URL}/v1/tipos-tarea?${searchParams.toString()}`;
  const { data, status } = await request<{
    error: number;
    respuesta: string;
    resultado?: { items: TipoTareaListItem[]; total: number; page: number; page_size: number };
  }>(url);

  if (status === 403) {
    return { success: false, errorMessage: data.respuesta || 'Sin permiso', errorCode: data.error };
  }
  if (status === 401) {
    return { success: false, errorMessage: 'No autenticado', errorCode: 3001 };
  }
  if (status !== 200 || data.error !== 0 || !data.resultado) {
    return { success: false, errorMessage: data.respuesta || 'Error al obtener tipos de tarea', errorCode: data.error };
  }
  const r = data.resultado as { items: TipoTareaListItem[]; total: number; page: number; page_size: number };
  const totalPages = r.page_size > 0 ? Math.ceil(r.total / r.page_size) : 0;
  return {
    success: true,
    data: r.items,
    pagination: { page: r.page, page_size: r.page_size, total: r.total, total_pages: totalPages },
  };
}

/**
 * Obtener un tipo de tarea por ID (edición).
 */
export async function getTipoTarea(id: number): Promise<GetTipoTareaResult> {
  const url = `${API_BASE_URL}/v1/tipos-tarea/${id}`;
  const { data, status } = await request<{ error: number; respuesta: string; resultado?: TipoTareaListItem }>(url);
  if (status === 200 && data.error === 0 && data.resultado) {
    return { success: true, data: data.resultado as TipoTareaListItem };
  }
  if (status === 403) return { success: false, errorMessage: data.respuesta || 'Sin permiso', errorCode: data.error };
  if (status === 404) return { success: false, errorMessage: data.respuesta || 'No encontrado', errorCode: data.error };
  return { success: false, errorMessage: data.respuesta || 'Error al obtener tipo de tarea', errorCode: data.error };
}

/**
 * Obtener tipo de tarea por ID con clientes asociados (detalle TR-027).
 */
export async function getTipoTareaConClientes(id: number): Promise<GetTipoTareaResult> {
  const url = `${API_BASE_URL}/v1/tipos-tarea/${id}?clientes=1`;
  const { data, status } = await request<{
    error: number;
    respuesta: string;
    resultado?: TipoTareaListItem & { clientes?: { id: number; code: string; nombre: string }[] };
  }>(url);
  if (status === 200 && data.error === 0 && data.resultado) {
    return { success: true, data: data.resultado as TipoTareaListItem & { clientes?: { id: number; code: string; nombre: string }[] } };
  }
  if (status === 403) return { success: false, errorMessage: data.respuesta || 'Sin permiso', errorCode: data.error };
  if (status === 404) return { success: false, errorMessage: data.respuesta || 'No encontrado', errorCode: data.error };
  return { success: false, errorMessage: data.respuesta || 'Error al obtener tipo de tarea', errorCode: data.error };
}

/**
 * Crear tipo de tarea.
 */
export async function createTipoTarea(
  body: CreateTipoTareaBody
): Promise<{ success: boolean; data?: TipoTareaListItem; errorMessage?: string; errorCode?: number }> {
  const url = `${API_BASE_URL}/v1/tipos-tarea`;
  const { data, status } = await request<{ error: number; respuesta: string; resultado?: TipoTareaListItem }>(url, {
    method: 'POST',
    body: JSON.stringify(body),
  });
  if (status === 201 && data.error === 0 && data.resultado) {
    return { success: true, data: data.resultado as TipoTareaListItem };
  }
  if (status === 403) return { success: false, errorMessage: data.respuesta || 'Sin permiso', errorCode: data.error };
  if (status === 409) return { success: false, errorMessage: data.respuesta || 'Código duplicado', errorCode: data.error };
  if (status === 422) return { success: false, errorMessage: data.respuesta || 'Error de validación', errorCode: data.error };
  return { success: false, errorMessage: data.respuesta || 'Error al crear tipo de tarea', errorCode: data.error };
}

/**
 * Actualizar tipo de tarea.
 */
export async function updateTipoTarea(
  id: number,
  body: UpdateTipoTareaBody
): Promise<{ success: boolean; data?: TipoTareaListItem; errorMessage?: string; errorCode?: number }> {
  const url = `${API_BASE_URL}/v1/tipos-tarea/${id}`;
  const { data, status } = await request<{ error: number; respuesta: string; resultado?: TipoTareaListItem }>(url, {
    method: 'PUT',
    body: JSON.stringify(body),
  });
  if (status === 200 && data.error === 0 && data.resultado) {
    return { success: true, data: data.resultado as TipoTareaListItem };
  }
  if (status === 403) return { success: false, errorMessage: data.respuesta || 'Sin permiso', errorCode: data.error };
  if (status === 404) return { success: false, errorMessage: data.respuesta || 'No encontrado', errorCode: data.error };
  if (status === 422) return { success: false, errorMessage: data.respuesta || 'Error de validación', errorCode: data.error };
  return { success: false, errorMessage: data.respuesta || 'Error al actualizar tipo de tarea', errorCode: data.error };
}

/**
 * Eliminar tipo de tarea.
 */
export async function deleteTipoTarea(id: number): Promise<{ success: boolean; errorMessage?: string; errorCode?: number }> {
  const url = `${API_BASE_URL}/v1/tipos-tarea/${id}`;
  const { data, status } = await request<{ error: number; respuesta: string }>(url, { method: 'DELETE' });
  if (status === 200 && data.error === 0) return { success: true };
  if (status === 403) return { success: false, errorMessage: data.respuesta || 'Sin permiso', errorCode: data.error };
  if (status === 404) return { success: false, errorMessage: data.respuesta || 'No encontrado', errorCode: data.error };
  if (status === 422 && data.error === ERROR_EN_USO) {
    return {
      success: false,
      errorMessage: data.respuesta || 'No se puede eliminar el tipo de tarea porque está en uso (tareas o clientes asociados).',
      errorCode: data.error,
    };
  }
  return { success: false, errorMessage: data.respuesta || 'Error al eliminar tipo de tarea', errorCode: data.error };
}
