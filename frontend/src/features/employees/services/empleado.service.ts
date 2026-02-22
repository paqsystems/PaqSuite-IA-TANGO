/**
 * Service: empleado.service
 *
 * Servicio de empleados (listado, creación, edición, eliminación y detalle para supervisores). TR-018(MH), TR-019(MH), TR-020(MH), TR-021(MH), TR-022(SH).
 *
 * @see TR-018(MH)-listado-de-empleados.md
 * @see TR-019(MH)-creación-de-empleado.md
 * @see TR-020(MH)-edición-de-empleado.md
 * @see TR-021(MH)-eliminación-de-empleado.md
 * @see TR-022(SH)-visualización-de-detalle-de-empleado.md
 */

import { getToken } from '../../../shared/utils/tokenStorage';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

export interface EmpleadoListItem {
  id: number;
  code: string;
  nombre: string;
  email: string | null;
  supervisor: boolean;
  activo: boolean;
  inhabilitado: boolean;
  created_at: string;
  updated_at: string;
}

export interface EmpleadosListParams {
  page?: number;
  page_size?: number;
  search?: string;
  supervisor?: boolean | null;
  activo?: boolean | null;
  inhabilitado?: boolean | null;
  sort?: string;
  sort_dir?: 'asc' | 'desc';
}

export interface EmpleadosListResult {
  success: boolean;
  data?: EmpleadoListItem[];
  pagination?: {
    page: number;
    page_size: number;
    total: number;
    total_pages: number;
  };
  errorMessage?: string;
  errorCode?: number;
}

/** Body para crear empleado. TR-019. */
export interface CreateEmpleadoBody {
  code: string;
  nombre: string;
  email?: string | null;
  password: string;
  supervisor?: boolean;
  activo?: boolean;
  inhabilitado?: boolean;
}

/** Resultado de creación de empleado (201). */
export interface EmpleadoCreadoItem {
  id: number;
  code: string;
  nombre: string;
  email: string | null;
  supervisor: boolean;
  activo: boolean;
  inhabilitado: boolean;
  created_at: string;
  updated_at: string;
}

export interface CreateEmpleadoResult {
  success: boolean;
  data?: EmpleadoCreadoItem;
  errorMessage?: string;
  errorCode?: number;
  validationErrors?: Record<string, string[]>;
}

/** Empleado por ID (GET para edición). TR-020. */
export interface EmpleadoItem {
  id: number;
  code: string;
  nombre: string;
  email: string | null;
  supervisor: boolean;
  activo: boolean;
  inhabilitado: boolean;
  created_at?: string;
  updated_at?: string;
}

export interface GetEmpleadoResult {
  success: boolean;
  data?: EmpleadoItem;
  errorMessage?: string;
  errorCode?: number;
}

/** Detalle con estadísticas opcionales. TR-022(SH). */
export interface EmpleadoDetalleItem extends EmpleadoItem {
  total_tareas?: number;
}

export interface GetEmpleadoDetalleResult {
  success: boolean;
  data?: EmpleadoDetalleItem;
  errorMessage?: string;
  errorCode?: number;
}

/** Body para actualizar empleado. TR-020. */
export interface UpdateEmpleadoBody {
  nombre: string;
  email?: string | null;
  password?: string;
  supervisor?: boolean;
  activo?: boolean;
  inhabilitado?: boolean;
}

/** Resultado de actualización (200). */
export interface EmpleadoActualizadoItem {
  id: number;
  code: string;
  nombre: string;
  email: string | null;
  supervisor: boolean;
  activo: boolean;
  inhabilitado: boolean;
  created_at: string;
  updated_at: string;
}

export interface UpdateEmpleadoResult {
  success: boolean;
  data?: EmpleadoActualizadoItem;
  errorMessage?: string;
  errorCode?: number;
  validationErrors?: Record<string, string[]>;
}

/**
 * Función auxiliar para realizar peticiones HTTP
 */
async function request<T>(
  url: string,
  options: RequestInit = {}
): Promise<{ data: T; status: number }> {
  const token = getToken();
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    ...(options.headers && typeof options.headers === 'object' && !Array.isArray(options.headers)
      ? (options.headers as Record<string, string>)
      : {}),
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const response = await fetch(url, {
    ...options,
    headers,
  });

  const data = (await response.json()) as T;
  return { data, status: response.status };
}

/**
 * Obtener listado paginado de empleados (solo supervisores). TR-018(MH).
 */
export async function getEmpleados(params: EmpleadosListParams): Promise<EmpleadosListResult> {
  const searchParams = new URLSearchParams();
  if (params.page != null) searchParams.set('page', String(params.page));
  if (params.page_size != null) searchParams.set('page_size', String(params.page_size));
  if (params.search) searchParams.set('search', params.search);
  if (params.supervisor !== undefined && params.supervisor !== null) {
    searchParams.set('supervisor', params.supervisor ? '1' : '0');
  }
  if (params.activo !== undefined && params.activo !== null) {
    searchParams.set('activo', params.activo ? '1' : '0');
  }
  if (params.inhabilitado !== undefined && params.inhabilitado !== null) {
    searchParams.set('inhabilitado', params.inhabilitado ? '1' : '0');
  }
  if (params.sort) searchParams.set('sort', params.sort);
  if (params.sort_dir) searchParams.set('sort_dir', params.sort_dir);

  const url = `${API_BASE_URL}/v1/empleados?${searchParams.toString()}`;
  const { data, status } = await request<{
    error: number;
    respuesta: string;
    resultado?: {
      items: EmpleadoListItem[];
      page: number;
      page_size: number;
      total: number;
      total_pages: number;
    };
  }>(url);

  if (status === 403) {
    return {
      success: false,
      errorMessage: data.respuesta || 'No tiene permiso para acceder a esta funcionalidad',
      errorCode: data.error,
    };
  }
  if (status === 401) {
    return {
      success: false,
      errorMessage: 'Usuario no autenticado',
      errorCode: 3001,
    };
  }
  if (status !== 200 || data.error !== 0 || !data.resultado) {
    return {
      success: false,
      errorMessage: data.respuesta || 'Error al obtener empleados',
      errorCode: data.error,
    };
  }

  const r = data.resultado;
  return {
    success: true,
    data: r.items,
    pagination: {
      page: r.page,
      page_size: r.page_size,
      total: r.total,
      total_pages: r.total_pages,
    },
  };
}

/**
 * Crear un nuevo empleado. Solo supervisores. TR-019(MH).
 * Siempre crea primero User en USERS y luego empleado en PQ_PARTES_USUARIOS.
 */
export async function createEmpleado(body: CreateEmpleadoBody): Promise<CreateEmpleadoResult> {
  const url = `${API_BASE_URL}/v1/empleados`;
  const { data, status } = await request<{
    error: number;
    respuesta: string;
    resultado?: EmpleadoCreadoItem;
    errors?: Record<string, string[]>;
  }>(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      code: body.code.trim(),
      nombre: body.nombre.trim(),
      email: body.email?.trim() || null,
      password: body.password,
      supervisor: body.supervisor !== undefined ? body.supervisor : false,
      activo: body.activo !== false,
      inhabilitado: !!body.inhabilitado,
    }),
  });

  if (status === 201 && data.error === 0 && data.resultado) {
    return { success: true, data: data.resultado };
  }
  if (status === 403) {
    return {
      success: false,
      errorMessage: data.respuesta || 'No tiene permiso para crear empleados',
      errorCode: data.error,
    };
  }
  if (status === 422) {
    const errors = (data as { resultado?: { errors?: Record<string, string[]> } }).resultado?.errors;
    return {
      success: false,
      errorMessage: data.respuesta || 'Error de validación',
      errorCode: data.error,
      validationErrors: errors,
    };
  }
  if (status === 409) {
    return {
      success: false,
      errorMessage: data.respuesta || 'Código o email duplicado',
      errorCode: data.error,
    };
  }
  return {
    success: false,
    errorMessage: data.respuesta || 'Error al crear empleado',
    errorCode: data.error,
  };
}

/**
 * Obtener un empleado por ID para edición. Solo supervisores. TR-020(MH).
 */
export async function getEmpleado(id: number): Promise<GetEmpleadoResult> {
  const url = `${API_BASE_URL}/v1/empleados/${id}`;
  const { data, status } = await request<{
    error: number;
    respuesta: string;
    resultado?: EmpleadoItem;
  }>(url);

  if (status === 200 && data.error === 0 && data.resultado) {
    return { success: true, data: data.resultado };
  }
  if (status === 403) {
    return {
      success: false,
      errorMessage: data.respuesta || 'No tiene permiso para acceder a esta funcionalidad',
      errorCode: data.error,
    };
  }
  if (status === 404) {
    return {
      success: false,
      errorMessage: data.respuesta || 'Empleado no encontrado',
      errorCode: data.error,
    };
  }
  return {
    success: false,
    errorMessage: data.respuesta || 'Error al obtener empleado',
    errorCode: data.error,
  };
}

/**
 * Obtener detalle de un empleado con estadísticas opcionales. Solo supervisores. TR-022(SH).
 * Incluye total_tareas si el backend lo devuelve.
 */
export async function getEmpleadoDetalle(id: number): Promise<GetEmpleadoDetalleResult> {
  const url = `${API_BASE_URL}/v1/empleados/${id}?include_stats=true`;
  const { data, status } = await request<{
    error: number;
    respuesta: string;
    resultado?: EmpleadoDetalleItem;
  }>(url);

  if (status === 200 && data.error === 0 && data.resultado) {
    return { success: true, data: data.resultado };
  }
  if (status === 403) {
    return {
      success: false,
      errorMessage: data.respuesta || 'No tiene permiso para acceder a esta funcionalidad',
      errorCode: data.error,
    };
  }
  if (status === 404) {
    return {
      success: false,
      errorMessage: data.respuesta || 'Empleado no encontrado',
      errorCode: data.error,
    };
  }
  return {
    success: false,
    errorMessage: data.respuesta || 'Error al obtener empleado',
    errorCode: data.error,
  };
}

/**
 * Actualizar un empleado existente. Solo supervisores. TR-020(MH).
 * El código no es modificable.
 */
export async function updateEmpleado(id: number, body: UpdateEmpleadoBody): Promise<UpdateEmpleadoResult> {
  const url = `${API_BASE_URL}/v1/empleados/${id}`;
  const { data, status } = await request<{
    error: number;
    respuesta: string;
    resultado?: EmpleadoActualizadoItem;
    errors?: Record<string, string[]>;
  }>(url, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      nombre: body.nombre.trim(),
      email: body.email?.trim() || null,
      password: body.password?.trim() || undefined,
      supervisor: body.supervisor !== undefined ? body.supervisor : undefined,
      activo: body.activo !== undefined ? body.activo : undefined,
      inhabilitado: body.inhabilitado !== undefined ? body.inhabilitado : undefined,
    }),
  });

  if (status === 200 && data.error === 0 && data.resultado) {
    return { success: true, data: data.resultado };
  }
  if (status === 403) {
    return {
      success: false,
      errorMessage: data.respuesta || 'No tiene permiso para editar empleados',
      errorCode: data.error,
    };
  }
  if (status === 404) {
    return {
      success: false,
      errorMessage: data.respuesta || 'Empleado no encontrado',
      errorCode: data.error,
    };
  }
  if (status === 422) {
    const errors = (data as { resultado?: { errors?: Record<string, string[]> } }).resultado?.errors;
    return {
      success: false,
      errorMessage: data.respuesta || 'Error de validación',
      errorCode: data.error,
      validationErrors: errors,
    };
  }
  if (status === 409) {
    return {
      success: false,
      errorMessage: data.respuesta || 'Email duplicado',
      errorCode: data.error,
    };
  }
  return {
    success: false,
    errorMessage: data.respuesta || 'Error al actualizar empleado',
    errorCode: data.error,
  };
}

/** Código de error 2113: no se puede eliminar empleado con tareas asociadas. */
export const ERROR_TIENE_TAREAS = 2113;

export interface DeleteEmpleadoResult {
  success: boolean;
  errorMessage?: string;
  errorCode?: number;
}

/**
 * Eliminar un empleado. Solo supervisores. TR-021(MH).
 * No se puede eliminar si tiene tareas asociadas (error 2113).
 */
export async function deleteEmpleado(id: number): Promise<DeleteEmpleadoResult> {
  const url = `${API_BASE_URL}/v1/empleados/${id}`;
  const { data, status } = await request<{
    error: number;
    respuesta: string;
    resultado?: null;
  }>(url, { method: 'DELETE' });

  if (status === 200 && data.error === 0) {
    return { success: true };
  }
  if (status === 403) {
    return {
      success: false,
      errorMessage: data.respuesta || 'No tiene permiso para eliminar empleados',
      errorCode: data.error,
    };
  }
  if (status === 404) {
    return {
      success: false,
      errorMessage: data.respuesta || 'Empleado no encontrado',
      errorCode: data.error,
    };
  }
  if (status === 422 && data.error === ERROR_TIENE_TAREAS) {
    return {
      success: false,
      errorMessage: data.respuesta || 'No se puede eliminar un empleado que tiene tareas asociadas.',
      errorCode: data.error,
    };
  }
  return {
    success: false,
    errorMessage: data.respuesta || 'Error al eliminar empleado',
    errorCode: data.error,
  };
}
