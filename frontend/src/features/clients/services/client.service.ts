/**
 * Service: client.service
 *
 * Servicio de clientes (listado, creación, edición y eliminación para supervisores). TR-008(MH), TR-009(MH), TR-010(MH), TR-011(MH).
 *
 * @see TR-008(MH)-listado-de-clientes.md
 * @see TR-009(MH)-creación-de-cliente.md
 * @see TR-010(MH)-edición-de-cliente.md
 * @see TR-011(MH)-eliminación-de-cliente.md
 * @see TR-012(MH)-asignación-de-tipos-de-tarea-a-cliente.md
 */

import { getToken } from '../../../shared/utils/tokenStorage';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

export interface TipoClienteItem {
  id: number;
  code: string;
  descripcion: string;
  activo: boolean;
  inhabilitado: boolean;
}

export interface ClienteListItem {
  id: number;
  code: string;
  nombre: string;
  tipo_cliente: { id: number; code: string; descripcion: string } | null;
  email: string | null;
  activo: boolean;
  inhabilitado: boolean;
  created_at: string;
  updated_at: string;
}

export interface ClientesListParams {
  page?: number;
  page_size?: number;
  search?: string;
  tipo_cliente_id?: number | null;
  activo?: boolean | null;
  inhabilitado?: boolean | null;
  sort?: string;
  sort_dir?: 'asc' | 'desc';
}

export interface ClientesListResult {
  success: boolean;
  data?: ClienteListItem[];
  pagination?: {
    page: number;
    page_size: number;
    total: number;
    total_pages: number;
  };
  errorMessage?: string;
  errorCode?: number;
}

export interface TiposClienteResult {
  success: boolean;
  data?: TipoClienteItem[];
  errorMessage?: string;
  errorCode?: number;
}

/** Body para crear cliente. TR-009. */
export interface CreateClienteBody {
  code: string;
  nombre: string;
  tipo_cliente_id: number;
  email?: string | null;
  password?: string | null;
  habilitar_acceso?: boolean;
  activo?: boolean;
  inhabilitado?: boolean;
}

/** Resultado de creación de cliente (201). */
export interface ClienteCreadoItem {
  id: number;
  code: string;
  nombre: string;
  tipo_cliente_id: number;
  tipo_cliente: { id: number; code: string; descripcion: string } | null;
  email: string | null;
  activo: boolean;
  inhabilitado: boolean;
  created_at: string;
  updated_at: string;
}

export interface CreateClienteResult {
  success: boolean;
  data?: ClienteCreadoItem;
  errorMessage?: string;
  errorCode?: number;
  validationErrors?: Record<string, string[]>;
}

/** Detalle de cliente para edición (GET /clientes/{id}). TR-010. */
export interface ClienteDetalleItem {
  id: number;
  code: string;
  nombre: string;
  tipo_cliente_id: number;
  tipo_cliente: { id: number; code: string; descripcion: string } | null;
  email: string | null;
  activo: boolean;
  inhabilitado: boolean;
  tiene_acceso: boolean;
  created_at: string;
  updated_at: string;
}

export interface GetClienteResult {
  success: boolean;
  data?: ClienteDetalleItem;
  errorMessage?: string;
  errorCode?: number;
}

/** Body para actualizar cliente. TR-010. */
export interface UpdateClienteBody {
  nombre: string;
  tipo_cliente_id: number;
  email?: string | null;
  password?: string | null;
  habilitar_acceso?: boolean;
  activo?: boolean;
  inhabilitado?: boolean;
}

/** Resultado de actualización (200). */
export interface ClienteActualizadoItem {
  id: number;
  code: string;
  nombre: string;
  tipo_cliente_id: number;
  tipo_cliente: { id: number; code: string; descripcion: string } | null;
  email: string | null;
  activo: boolean;
  inhabilitado: boolean;
  updated_at: string;
}

export interface UpdateClienteResult {
  success: boolean;
  data?: ClienteActualizadoItem;
  errorMessage?: string;
  errorCode?: number;
  validationErrors?: Record<string, string[]>;
}

async function request<T>(
  url: string,
  options: RequestInit = {}
): Promise<{ data: T; status: number }> {
  const token = getToken();
  const headers: HeadersInit = {
    Accept: 'application/json',
    ...(options.headers as Record<string, string>),
  };
  if (token) {
    (headers as Record<string, string>)['Authorization'] = `Bearer ${token}`;
  }
  const response = await fetch(url, { ...options, headers });
  const data = (await response.json().catch(() => ({}))) as T & {
    error?: number;
    respuesta?: string;
  };
  return { data, status: response.status };
}

/**
 * Obtener listado paginado de clientes (solo supervisores).
 */
export async function getClientes(params: ClientesListParams): Promise<ClientesListResult> {
  const searchParams = new URLSearchParams();
  if (params.page != null) searchParams.set('page', String(params.page));
  if (params.page_size != null) searchParams.set('page_size', String(params.page_size));
  if (params.search) searchParams.set('search', params.search);
  if (params.tipo_cliente_id != null) searchParams.set('tipo_cliente_id', String(params.tipo_cliente_id));
  if (params.activo !== undefined && params.activo !== null) searchParams.set('activo', params.activo ? '1' : '0');
  if (params.inhabilitado !== undefined && params.inhabilitado !== null) searchParams.set('inhabilitado', params.inhabilitado ? '1' : '0');
  if (params.sort) searchParams.set('sort', params.sort);
  if (params.sort_dir) searchParams.set('sort_dir', params.sort_dir);

  const url = `${API_BASE_URL}/v1/clientes?${searchParams.toString()}`;
  const { data, status } = await request<{
    error: number;
    respuesta: string;
    resultado?: {
      items: ClienteListItem[];
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
      errorMessage: data.respuesta || 'Error al obtener clientes',
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
 * Obtener tipos de cliente (para filtro en listado). Solo supervisores.
 */
export async function getTiposCliente(): Promise<TiposClienteResult> {
  const url = `${API_BASE_URL}/v1/tipos-cliente`;
  const { data, status } = await request<{
    error: number;
    respuesta: string;
    resultado?: TipoClienteItem[];
  }>(url);

  if (status === 403) {
    return {
      success: false,
      errorMessage: data.respuesta || 'No tiene permiso',
      errorCode: data.error,
    };
  }
  if (status !== 200 || data.error !== 0 || !Array.isArray(data.resultado)) {
    return {
      success: false,
      errorMessage: data.respuesta || 'Error al obtener tipos de cliente',
      errorCode: data.error,
    };
  }

  return {
    success: true,
    data: data.resultado,
  };
}

/**
 * Crear un nuevo cliente. Solo supervisores. TR-009(MH).
 * Si habilitar_acceso: crear User y cliente con user_id.
 */
export async function createCliente(body: CreateClienteBody): Promise<CreateClienteResult> {
  const url = `${API_BASE_URL}/v1/clientes`;
  const { data, status } = await request<{
    error: number;
    respuesta: string;
    resultado?: ClienteCreadoItem;
    errors?: Record<string, string[]>;
  }>(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      code: body.code.trim(),
      nombre: body.nombre.trim(),
      tipo_cliente_id: body.tipo_cliente_id,
      email: body.email?.trim() || null,
      password: body.habilitar_acceso ? body.password || null : undefined,
      habilitar_acceso: !!body.habilitar_acceso,
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
      errorMessage: data.respuesta || 'No tiene permiso para crear clientes',
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
    errorMessage: data.respuesta || 'Error al crear cliente',
    errorCode: data.error,
  };
}

/**
 * Obtener detalle de un cliente para edición. Solo supervisores. TR-010(MH).
 */
export async function getCliente(id: number): Promise<GetClienteResult> {
  const url = `${API_BASE_URL}/v1/clientes/${id}`;
  const { data, status } = await request<{
    error: number;
    respuesta: string;
    resultado?: ClienteDetalleItem;
  }>(url);

  if (status === 200 && data.error === 0 && data.resultado) {
    return { success: true, data: data.resultado };
  }
  if (status === 403) {
    return {
      success: false,
      errorMessage: data.respuesta || 'No tiene permiso',
      errorCode: data.error,
    };
  }
  if (status === 404) {
    return {
      success: false,
      errorMessage: data.respuesta || 'Cliente no encontrado',
      errorCode: data.error,
    };
  }
  return {
    success: false,
    errorMessage: data.respuesta || 'Error al obtener cliente',
    errorCode: data.error,
  };
}

/**
 * Actualizar un cliente existente. Solo supervisores. TR-010(MH).
 */
export async function updateCliente(id: number, body: UpdateClienteBody): Promise<UpdateClienteResult> {
  const url = `${API_BASE_URL}/v1/clientes/${id}`;
  const { data, status } = await request<{
    error: number;
    respuesta: string;
    resultado?: ClienteActualizadoItem;
    errors?: Record<string, string[]>;
  }>(url, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      nombre: body.nombre.trim(),
      tipo_cliente_id: body.tipo_cliente_id,
      email: body.email?.trim() || null,
      password: body.password?.trim() || undefined,
      habilitar_acceso: !!body.habilitar_acceso,
      activo: body.activo,
      inhabilitado: body.inhabilitado,
    }),
  });

  if (status === 200 && data.error === 0 && data.resultado) {
    return { success: true, data: data.resultado };
  }
  if (status === 403) {
    return {
      success: false,
      errorMessage: data.respuesta || 'No tiene permiso para editar clientes',
      errorCode: data.error,
    };
  }
  if (status === 404) {
    return {
      success: false,
      errorMessage: data.respuesta || 'Cliente no encontrado',
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
    errorMessage: data.respuesta || 'Error al actualizar cliente',
    errorCode: data.error,
  };
}

/** Código de error 2112: no se puede eliminar cliente con tareas asociadas. */
export const ERROR_TIENE_TAREAS = 2112;

/** Código 2116: el cliente debe tener al menos un tipo de tarea disponible. TR-012. */
export const ERROR_SIN_TIPOS_TAREA = 2116;

/** Código 2118: no se puede asignar tipo de tarea genérico. TR-012. */
export const ERROR_TIPO_GENERICO = 2118;

/** Tipo de tarea (asignado o disponible para asignación). TR-012. */
export interface TipoTareaItem {
  id: number;
  code: string;
  descripcion: string;
  is_generico: boolean;
  activo?: boolean;
  inhabilitado?: boolean;
}

export interface GetTiposTareaClienteResult {
  success: boolean;
  data?: TipoTareaItem[];
  errorMessage?: string;
  errorCode?: number;
}

export interface UpdateTiposTareaClienteResult {
  success: boolean;
  data?: TipoTareaItem[];
  errorMessage?: string;
  errorCode?: number;
}

/**
 * Obtener tipos de tarea asignados al cliente (no genéricos). Solo supervisores. TR-012(MH).
 */
export async function getTiposTareaCliente(clienteId: number): Promise<GetTiposTareaClienteResult> {
  const url = `${API_BASE_URL}/v1/clientes/${clienteId}/tipos-tarea`;
  const { data, status } = await request<{
    error: number;
    respuesta: string;
    resultado?: TipoTareaItem[];
  }>(url);

  if (status === 200 && data.error === 0 && Array.isArray(data.resultado)) {
    return { success: true, data: data.resultado };
  }
  if (status === 403) {
    return {
      success: false,
      errorMessage: data.respuesta || 'No tiene permiso',
      errorCode: data.error,
    };
  }
  if (status === 404) {
    return {
      success: false,
      errorMessage: data.respuesta || 'Cliente no encontrado',
      errorCode: data.error,
    };
  }
  return {
    success: false,
    errorMessage: data.respuesta || 'Error al obtener tipos de tarea',
    errorCode: data.error,
  };
}

/**
 * Actualizar asignación de tipos de tarea del cliente. Solo supervisores. TR-012(MH).
 * Body: tipo_tarea_ids (array de IDs). Error 2116 si lista vacía y no hay genéricos.
 */
export async function updateTiposTareaCliente(
  clienteId: number,
  tipoTareaIds: number[]
): Promise<UpdateTiposTareaClienteResult> {
  const url = `${API_BASE_URL}/v1/clientes/${clienteId}/tipos-tarea`;
  const { data, status } = await request<{
    error: number;
    respuesta: string;
    resultado?: TipoTareaItem[];
  }>(url, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ tipo_tarea_ids: tipoTareaIds }),
  });

  if (status === 200 && data.error === 0 && Array.isArray(data.resultado)) {
    return { success: true, data: data.resultado };
  }
  if (status === 403) {
    return {
      success: false,
      errorMessage: data.respuesta || 'No tiene permiso',
      errorCode: data.error,
    };
  }
  if (status === 404) {
    return {
      success: false,
      errorMessage: data.respuesta || 'Cliente no encontrado',
      errorCode: data.error,
    };
  }
  if (status === 422) {
    return {
      success: false,
      errorMessage: data.respuesta || 'Error al actualizar tipos de tarea',
      errorCode: data.error,
    };
  }
  return {
    success: false,
    errorMessage: data.respuesta || 'Error al actualizar tipos de tarea',
    errorCode: data.error,
  };
}

/**
 * Obtener catálogo de tipos de tarea (todos activos) para filtrar no genéricos en UI. TR-012.
 * Usa GET /api/v1/tasks/task-types sin cliente_id.
 */
export async function getTiposTareaParaAsignacion(): Promise<{
  success: boolean;
  data?: TipoTareaItem[];
  errorMessage?: string;
}> {
  const url = `${API_BASE_URL}/v1/tasks/task-types`;
  const { data, status } = await request<{
    error: number;
    respuesta: string;
    resultado?: { id: number; code: string; descripcion: string; is_generico: boolean }[];
  }>(url);

  if (status === 200 && data.error === 0 && Array.isArray(data.resultado)) {
    const noGenericos = data.resultado.filter((t) => !t.is_generico);
    return { success: true, data: noGenericos };
  }
  return {
    success: false,
    errorMessage: data.respuesta || 'Error al obtener tipos de tarea',
  };
}

export interface DeleteClienteResult {
  success: boolean;
  errorMessage?: string;
  errorCode?: number;
}

/**
 * Eliminar un cliente. Solo supervisores. TR-011(MH).
 * Retorna error 2112 si el cliente tiene tareas asociadas.
 */
export async function deleteCliente(id: number): Promise<DeleteClienteResult> {
  const url = `${API_BASE_URL}/v1/clientes/${id}`;
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
      errorMessage: data.respuesta || 'No tiene permiso para eliminar clientes',
      errorCode: data.error,
    };
  }
  if (status === 404) {
    return {
      success: false,
      errorMessage: data.respuesta || 'Cliente no encontrado',
      errorCode: data.error,
    };
  }
  if (status === 422 && data.error === ERROR_TIENE_TAREAS) {
    return {
      success: false,
      errorMessage: data.respuesta || 'No se puede eliminar un cliente que tiene tareas asociadas.',
      errorCode: data.error,
    };
  }
  return {
    success: false,
    errorMessage: data.respuesta || 'Error al eliminar cliente',
    errorCode: data.error,
  };
}
