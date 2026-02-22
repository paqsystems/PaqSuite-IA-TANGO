/**
 * Service: task.service
 * 
 * Servicio de tareas del frontend.
 * Maneja las llamadas al API de gestión de tareas.
 * 
 * @see TR-028(MH)-carga-de-tarea-diaria.md
 */

import { getToken } from '../../../shared/utils/tokenStorage';
import { t } from '../../../shared/i18n';

/**
 * URL base del API
 */
const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

/**
 * Datos de una tarea para crear
 */
export interface CreateTaskData {
  fecha: string; // Formato YMD (YYYY-MM-DD)
  cliente_id: number;
  tipo_tarea_id: number;
  duracion_minutos: number;
  sin_cargo?: boolean;
  presencial?: boolean;
  observacion: string;
  usuario_id?: number | null; // Solo para supervisores
}

/**
 * Datos de una tarea creada
 */
export interface Task {
  id: number;
  usuario_id: number;
  cliente_id: number;
  tipo_tarea_id: number;
  fecha: string; // Formato YMD (YYYY-MM-DD)
  duracion_minutos: number;
  sin_cargo: boolean;
  presencial: boolean;
  observacion: string;
  cerrado: boolean;
  created_at: string;
  updated_at: string;
}

/**
 * Item de tarea en la lista (con cliente/tipo anidados y observación truncada).
 * empleado presente cuando el listado incluye propietario (TR-032 modal supervisor).
 */
export interface TaskListItem {
  id: number;
  fecha: string;
  cliente: { id: number; nombre: string };
  tipo_tarea: { id: number; nombre: string };
  duracion_minutos: number;
  duracion_horas: string;
  sin_cargo: boolean;
  presencial: boolean;
  observacion: string;
  cerrado: boolean;
  created_at: string;
  /** Propietario de la tarea (incluido en listado para modal eliminación supervisor) */
  empleado?: { id: number; code: string; nombre: string };
}

/**
 * Parámetros de filtro para listar tareas
 */
export interface TaskListParams {
  page?: number;
  per_page?: number;
  fecha_desde?: string;
  fecha_hasta?: string;
  cliente_id?: number | null;
  tipo_tarea_id?: number | null;
  usuario_id?: number | null;
  busqueda?: string;
  ordenar_por?: string;
  orden?: 'asc' | 'desc';
  /** TR-040: filtro estado cerrado (true/false; omitir = todos) */
  cerrado?: boolean | null;
}

/**
 * Respuesta paginada del API de lista de tareas
 */
export interface TaskListResult {
  data: TaskListItem[];
  pagination: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
  };
  totales: {
    cantidad_tareas: number;
    total_horas: number;
  };
}

/**
 * Resultado de getTasks / getAllTasks
 */
export type GetTasksResult =
  | { success: true; data: TaskListItem[]; pagination: TaskListResult['pagination']; totales: TaskListResult['totales']; errorCode?: number; errorMessage?: string }
  | { success: false; errorCode?: number; errorMessage?: string };

/**
 * Item de consulta detallada (TR-044): empleado solo si supervisor
 */
export interface DetailReportItem {
  id: number;
  empleado?: { id: number; nombre: string; code: string };
  cliente: { id: number; nombre: string; tipo_cliente?: string | null };
  fecha: string;
  tipo_tarea: { id: number; descripcion: string };
  horas: number;
  sin_cargo: boolean;
  presencial: boolean;
  descripcion: string;
}

/**
 * Parámetros para GET /reports/detail
 */
export interface DetailReportParams {
  page?: number;
  per_page?: number;
  fecha_desde?: string;
  fecha_hasta?: string;
  tipo_cliente_id?: number | null;
  cliente_id?: number | null;
  usuario_id?: number | null;
  ordenar_por?: string;
  orden?: 'asc' | 'desc';
}

/**
 * Resultado de getDetailReport
 */
export interface GetDetailReportResult {
  success: boolean;
  data?: DetailReportItem[];
  pagination?: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
  };
  totalHoras?: number;
  errorCode?: number;
  errorMessage?: string;
}

/**
 * Parámetros para GET /reports/by-client (TR-046)
 */
export interface ByClientReportParams {
  fecha_desde?: string;
  fecha_hasta?: string;
}

/**
 * Tarea dentro de un grupo por cliente (TR-046)
 */
export interface ByClientTaskItem {
  id: number;
  fecha: string;
  tipo_tarea: { id: number; descripcion: string };
  horas: number;
  empleado?: { id: number; nombre: string; code: string };
  descripcion: string;
}

/**
 * Grupo por cliente en reporte TR-046
 */
export interface ByClientGroup {
  cliente_id: number;
  nombre: string;
  tipo_cliente: { id: number; descripcion: string } | null;
  total_horas: number;
  cantidad_tareas: number;
  tareas: ByClientTaskItem[];
}

/**
 * Resultado de getReportByClient (TR-046)
 */
export interface GetByClientReportResult {
  success: boolean;
  grupos?: ByClientGroup[];
  totalGeneralHoras?: number;
  totalGeneralTareas?: number;
  errorCode?: number;
  errorMessage?: string;
}

/**
 * Parámetros para GET /reports/by-employee (TR-045)
 */
export interface ByEmployeeReportParams {
  fecha_desde?: string;
  fecha_hasta?: string;
  tipo_cliente_id?: number | null;
  cliente_id?: number | null;
  usuario_id?: number | null;
}

/**
 * Tarea dentro de un grupo por empleado (TR-045)
 */
export interface ByEmployeeTaskItem {
  id: number;
  fecha: string;
  cliente: { id: number; nombre: string };
  tipo_tarea: { id: number; descripcion: string };
  horas: number;
  sin_cargo: boolean;
  presencial: boolean;
  descripcion: string;
}

/**
 * Grupo por empleado en reporte TR-045
 */
export interface ByEmployeeGroup {
  usuario_id: number;
  nombre: string;
  code: string;
  total_horas: number;
  cantidad_tareas: number;
  tareas: ByEmployeeTaskItem[];
}

/**
 * Resultado de getReportByEmployee (TR-045)
 */
export interface GetByEmployeeReportResult {
  success: boolean;
  grupos?: ByEmployeeGroup[];
  totalGeneralHoras?: number;
  totalGeneralTareas?: number;
  errorCode?: number;
  errorMessage?: string;
}

/**
 * Parámetros para GET /reports/by-task-type (TR-047)
 */
export interface ByTaskTypeReportParams {
  fecha_desde?: string;
  fecha_hasta?: string;
  tipo_cliente_id?: number | null;
  cliente_id?: number | null;
  usuario_id?: number | null;
}

/**
 * Tarea dentro de un grupo por tipo de tarea (TR-047)
 */
export interface ByTaskTypeTaskItem {
  id: number;
  fecha: string;
  cliente: { id: number; nombre: string };
  tipo_tarea: { id: number; descripcion: string };
  horas: number;
  sin_cargo: boolean;
  presencial: boolean;
  descripcion: string;
}

/**
 * Grupo por tipo de tarea en reporte TR-047
 */
export interface ByTaskTypeGroup {
  tipo_tarea_id: number;
  descripcion: string;
  total_horas: number;
  cantidad_tareas: number;
  tareas: ByTaskTypeTaskItem[];
}

/**
 * Resultado de getReportByTaskType (TR-047)
 */
export interface GetByTaskTypeReportResult {
  success: boolean;
  grupos?: ByTaskTypeGroup[];
  totalGeneralHoras?: number;
  totalGeneralTareas?: number;
  errorCode?: number;
  errorMessage?: string;
}

/**
 * Parámetros para GET /reports/by-date (TR-048)
 */
export interface ByDateReportParams {
  fecha_desde?: string;
  fecha_hasta?: string;
}

/**
 * Tarea dentro de un grupo por fecha (TR-048)
 */
export interface ByDateTaskItem {
  id: number;
  fecha: string;
  cliente: { id: number; nombre: string };
  tipo_tarea: { id: number; descripcion: string };
  horas: number;
  sin_cargo: boolean;
  presencial: boolean;
  descripcion: string;
}

/**
 * Grupo por fecha en reporte TR-048
 */
export interface ByDateGroup {
  fecha: string;
  total_horas: number;
  cantidad_tareas: number;
  tareas: ByDateTaskItem[];
}

/**
 * Resultado de getReportByDate (TR-048)
 */
export interface GetByDateReportResult {
  success: boolean;
  grupos?: ByDateGroup[];
  totalGeneralHoras?: number;
  totalGeneralTareas?: number;
  errorCode?: number;
  errorMessage?: string;
}

/**
 * Datos de un cliente para selector
 */
export interface Client {
  id: number;
  code: string;
  nombre: string;
}

/**
 * Datos de un tipo de tarea para selector
 */
export interface TaskType {
  id: number;
  code: string;
  descripcion: string;
  is_generico: boolean;
}

/**
 * Datos de un empleado para selector
 */
export interface Employee {
  id: number;
  code: string;
  nombre: string;
}

/**
 * Respuesta del API
 */
export interface ApiResponse<T> {
  error: number;
  respuesta: string;
  resultado: T;
}

/**
 * Respuesta de error del API
 */
export interface ApiError {
  error: number;
  respuesta: string;
  resultado: {
    errors?: Record<string, string[]>;
  };
}

/**
 * Resultado de crear tarea
 */
export interface CreateTaskResult {
  success: boolean;
  task?: Task;
  errorCode?: number;
  errorMessage?: string;
  validationErrors?: Record<string, string[]>;
}

/**
 * Datos de tarea para edición (GET /tasks/{id})
 */
export interface TaskForEdit {
  id: number;
  usuario_id: number;
  usuario_nombre?: string;
  cliente_id: number;
  tipo_tarea_id: number;
  fecha: string;
  duracion_minutos: number;
  sin_cargo: boolean;
  presencial: boolean;
  observacion: string;
  cerrado: boolean;
}

/**
 * Resultado de obtener tarea para edición
 */
export interface GetTaskResult {
  success: boolean;
  task?: TaskForEdit;
  errorCode?: number;
  errorMessage?: string;
}

/**
 * Datos para actualizar tarea (PUT /tasks/{id}).
 * usuario_id es opcional y solo para supervisores (TR-031).
 */
export interface UpdateTaskData {
  fecha: string;
  cliente_id: number;
  tipo_tarea_id: number;
  duracion_minutos: number;
  sin_cargo?: boolean;
  presencial?: boolean;
  observacion: string;
  usuario_id?: number | null;
}

/**
 * Resultado de actualizar tarea
 */
export interface UpdateTaskResult {
  success: boolean;
  task?: Task;
  errorCode?: number;
  errorMessage?: string;
  validationErrors?: Record<string, string[]>;
}

/**
 * Resultado de eliminar tarea (TR-030)
 */
export interface DeleteTaskResult {
  success: boolean;
  errorCode?: number;
  errorMessage?: string;
}

/**
 * Item top cliente en dashboard (TR-051)
 */
export interface DashboardTopCliente {
  cliente_id: number;
  nombre: string;
  total_horas: number;
  cantidad_tareas: number;
  porcentaje?: number;
}

/**
 * Item top empleado en dashboard (TR-051, solo supervisor)
 */
export interface DashboardTopEmpleado {
  usuario_id: number;
  nombre: string;
  code: string;
  total_horas: number;
  cantidad_tareas: number;
  porcentaje?: number;
}

/**
 * Item distribución por tipo en dashboard (TR-051, solo cliente)
 */
export interface DashboardDistribucionTipo {
  tipo_tarea_id: number;
  descripcion: string;
  total_horas: number;
  cantidad_tareas: number;
}

/**
 * Datos del dashboard (TR-051)
 */
export interface DashboardData {
  total_horas: number;
  cantidad_tareas: number;
  promedio_horas_por_dia: number;
  top_clientes: DashboardTopCliente[];
  top_empleados: DashboardTopEmpleado[];
  distribucion_por_tipo: DashboardDistribucionTipo[];
}

/**
 * Parámetros para GET /dashboard (TR-051)
 */
export interface DashboardParams {
  fecha_desde: string;
  fecha_hasta: string;
}

/**
 * Resultado de getDashboard (TR-051)
 */
export interface GetDashboardResult {
  success: boolean;
  data?: DashboardData;
  errorCode?: number;
  errorMessage?: string;
}

/**
 * Resultado de obtener lista
 */
export interface GetListResult<T> {
  success: boolean;
  data?: T[];
  errorCode?: number;
  errorMessage?: string;
}

/**
 * Crea un nuevo registro de tarea
 * 
 * @param taskData Datos de la tarea (fecha en formato YMD)
 * @returns Resultado con datos de la tarea creada o error
 */
export async function createTask(taskData: CreateTaskData): Promise<CreateTaskResult> {
  const token = getToken();
  
  if (!token) {
    return {
      success: false,
      errorCode: 4001,
      errorMessage: t('tasks.service.errors.notAuthenticated', 'No autenticado'),
    };
  }

  try {
    const response = await fetch(`${API_BASE_URL}/v1/tasks`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify(taskData),
    });

    const data = await response.json();

    if (!response.ok) {
      // Error de validación o servidor
      const errorData = data as ApiError;
      
      return {
        success: false,
        errorCode: errorData.error,
        errorMessage: errorData.respuesta,
        validationErrors: errorData.resultado.errors,
      };
    }

    // Tarea creada exitosamente
    const taskResponse = data as ApiResponse<Task>;
    
    return {
      success: true,
      task: taskResponse.resultado,
    };

  } catch (error) {
    // Error de red o inesperado
    console.error('Error en createTask:', error);
    return {
      success: false,
      errorCode: 9999,
      errorMessage: t('tasks.service.errors.connection', 'Error de conexión. Intente nuevamente.'),
    };
  }
}

/**
 * Obtiene la lista de clientes activos
 * 
 * @returns Resultado con lista de clientes o error
 */
export async function getClients(): Promise<GetListResult<Client>> {
  const token = getToken();
  
  if (!token) {
    return {
      success: false,
      errorCode: 4001,
      errorMessage: t('tasks.service.errors.notAuthenticated', 'No autenticado'),
    };
  }

  try {
    const response = await fetch(`${API_BASE_URL}/v1/tasks/clients`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
    });

    const data = await response.json();

    if (!response.ok) {
      const errorData = data as ApiError;
      return {
        success: false,
        errorCode: errorData.error,
        errorMessage: errorData.respuesta,
      };
    }

    const clientsResponse = data as ApiResponse<Client[]>;
    
    return {
      success: true,
      data: clientsResponse.resultado,
    };

  } catch (error) {
    console.error('Error en getClients:', error);
    return {
      success: false,
      errorCode: 9999,
      errorMessage: t('tasks.service.errors.connection', 'Error de conexión. Intente nuevamente.'),
    };
  }
}

/**
 * Obtiene la lista de tipos de tarea disponibles
 * 
 * @param clienteId ID del cliente (opcional). Si se proporciona, retorna tipos genéricos + asignados al cliente
 * @returns Resultado con lista de tipos de tarea o error
 */
export async function getTaskTypes(clienteId?: number): Promise<GetListResult<TaskType>> {
  const token = getToken();
  
  if (!token) {
    return {
      success: false,
      errorCode: 4001,
      errorMessage: t('tasks.service.errors.notAuthenticated', 'No autenticado'),
    };
  }

  try {
    const url = clienteId 
      ? `${API_BASE_URL}/v1/tasks/task-types?cliente_id=${clienteId}`
      : `${API_BASE_URL}/v1/tasks/task-types`;

    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
    });

    const data = await response.json();

    if (!response.ok) {
      const errorData = data as ApiError;
      return {
        success: false,
        errorCode: errorData.error,
        errorMessage: errorData.respuesta,
      };
    }

    const taskTypesResponse = data as ApiResponse<TaskType[]>;
    
    return {
      success: true,
      data: taskTypesResponse.resultado,
    };

  } catch (error) {
    console.error('Error en getTaskTypes:', error);
    return {
      success: false,
      errorCode: 9999,
      errorMessage: t('tasks.service.errors.connection', 'Error de conexión. Intente nuevamente.'),
    };
  }
}

/**
 * Obtiene la lista de empleados activos (solo para supervisores)
 * 
 * @returns Resultado con lista de empleados o error
 */
export async function getEmployees(): Promise<GetListResult<Employee>> {
  const token = getToken();
  
  if (!token) {
    return {
      success: false,
      errorCode: 4001,
      errorMessage: t('tasks.service.errors.notAuthenticated', 'No autenticado'),
    };
  }

  try {
    const response = await fetch(`${API_BASE_URL}/v1/tasks/employees`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
    });

    const data = await response.json();

    if (!response.ok) {
      const errorData = data as ApiError;
      return {
        success: false,
        errorCode: errorData.error,
        errorMessage: errorData.respuesta,
      };
    }

    const employeesResponse = data as ApiResponse<Employee[]>;
    
    return {
      success: true,
      data: employeesResponse.resultado,
    };

  } catch (error) {
    console.error('Error en getEmployees:', error);
    return {
      success: false,
      errorCode: 9999,
      errorMessage: t('tasks.service.errors.connection', 'Error de conexión. Intente nuevamente.'),
    };
  }
}

/**
 * Obtiene la lista paginada de tareas del usuario con filtros
 *
 * @param params Parámetros de paginación y filtros
 * @returns Resultado con data, pagination, totales o error
 */
export async function getTasks(params: TaskListParams = {}): Promise<GetTasksResult> {
  const token = getToken();

  if (!token) {
    return {
      success: false,
      errorCode: 4001,
      errorMessage: t('tasks.service.errors.notAuthenticated', 'No autenticado'),
    };
  }

  const searchParams = new URLSearchParams();
  if (params.page != null) searchParams.set('page', String(params.page));
  if (params.per_page != null) searchParams.set('per_page', String(params.per_page));
  if (params.fecha_desde) searchParams.set('fecha_desde', params.fecha_desde);
  if (params.fecha_hasta) searchParams.set('fecha_hasta', params.fecha_hasta);
  if (params.cliente_id != null) searchParams.set('cliente_id', String(params.cliente_id));
  if (params.tipo_tarea_id != null) searchParams.set('tipo_tarea_id', String(params.tipo_tarea_id));
  if (params.usuario_id != null) searchParams.set('usuario_id', String(params.usuario_id));
  if (params.busqueda) searchParams.set('busqueda', params.busqueda);
  if (params.ordenar_por) searchParams.set('ordenar_por', params.ordenar_por);
  if (params.orden) searchParams.set('orden', params.orden);

  const queryString = searchParams.toString();
  const url = `${API_BASE_URL}/v1/tasks${queryString ? `?${queryString}` : ''}`;

  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
    });

    const data = await response.json();

    if (!response.ok) {
      const errorData = data as ApiError;
      return {
        success: false,
        errorCode: errorData.error,
        errorMessage: errorData.respuesta,
      };
    }

    const result = data as ApiResponse<TaskListResult>;
    const r = result.resultado;

    return {
      success: true,
      data: r.data,
      pagination: r.pagination,
      totales: r.totales,
    };
  } catch (error) {
    console.error('Error en getTasks:', error);
    return {
      success: false,
      errorCode: 9999,
      errorMessage: t('tasks.service.errors.connection', 'Error de conexión. Intente nuevamente.'),
    };
  }
}

/**
 * Obtiene la lista paginada de todas las tareas (solo supervisores) – TR-034
 *
 * @param params Parámetros de paginación y filtros (misma estructura que getTasks)
 * @returns Resultado con data, pagination, totales o error (403 si no es supervisor)
 */
export async function getAllTasks(params: TaskListParams = {}): Promise<GetTasksResult> {
  const token = getToken();

  if (!token) {
    return {
      success: false,
      errorCode: 4001,
      errorMessage: t('tasks.service.errors.notAuthenticated', 'No autenticado'),
    };
  }

  const searchParams = new URLSearchParams();
  if (params.page != null) searchParams.set('page', String(params.page));
  if (params.per_page != null) searchParams.set('per_page', String(params.per_page));
  if (params.fecha_desde) searchParams.set('fecha_desde', params.fecha_desde);
  if (params.fecha_hasta) searchParams.set('fecha_hasta', params.fecha_hasta);
  if (params.cliente_id != null) searchParams.set('cliente_id', String(params.cliente_id));
  if (params.tipo_tarea_id != null) searchParams.set('tipo_tarea_id', String(params.tipo_tarea_id));
  if (params.usuario_id != null) searchParams.set('usuario_id', String(params.usuario_id));
  if (params.busqueda) searchParams.set('busqueda', params.busqueda);
  if (params.ordenar_por) searchParams.set('ordenar_por', params.ordenar_por);
  if (params.orden) searchParams.set('orden', params.orden);
  if (params.cerrado === true) searchParams.set('cerrado', 'true');
  if (params.cerrado === false) searchParams.set('cerrado', 'false');

  const queryString = searchParams.toString();
  const url = `${API_BASE_URL}/v1/tasks/all${queryString ? `?${queryString}` : ''}`;

  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
    });

    const data = await response.json();

    if (!response.ok) {
      const errorData = data as ApiError;
      return {
        success: false,
        errorCode: errorData.error,
        errorMessage: errorData.respuesta,
      };
    }

    const result = data as ApiResponse<TaskListResult>;
    const r = result.resultado;

    return {
      success: true,
      data: r.data,
      pagination: r.pagination,
      totales: r.totales,
    };
  } catch (error) {
    console.error('Error en getAllTasks:', error);
    return {
      success: false,
      errorCode: 9999,
      errorMessage: t('tasks.service.errors.connection', 'Error de conexión. Intente nuevamente.'),
    };
  }
}

/**
 * Resultado de bulkToggleClose (TR-042)
 */
export interface BulkToggleCloseResult {
  success: boolean;
  processed?: number;
  errorCode?: number;
  errorMessage?: string;
}

/**
 * Procesamiento masivo: invertir estado cerrado de tareas seleccionadas (TR-042, TR-043).
 *
 * @param taskIds IDs de tareas a procesar
 * @returns processed count o error (1212 selección vacía, 403 no supervisor)
 */
export async function bulkToggleClose(taskIds: number[]): Promise<BulkToggleCloseResult> {
  const token = getToken();
  if (!token) {
    return {
      success: false,
      errorCode: 4001,
      errorMessage: t('tasks.service.errors.notAuthenticated', 'No autenticado'),
    };
  }
  if (!Array.isArray(taskIds) || taskIds.length === 0) {
    return {
      success: false,
      errorCode: 1212,
      errorMessage: 'Debe seleccionar al menos una tarea',
    };
  }
  try {
    const response = await fetch(`${API_BASE_URL}/v1/tasks/bulk-toggle-close`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({ task_ids: taskIds }),
    });
    const data = await response.json();
    if (!response.ok) {
      const err = data as ApiError;
      return {
        success: false,
        errorCode: err.error,
        errorMessage: err.respuesta,
      };
    }
    const res = data as ApiResponse<{ processed: number; task_ids: number[] }>;
    return {
      success: true,
      processed: res.resultado.processed,
    };
  } catch (error) {
    console.error('Error en bulkToggleClose:', error);
    return {
      success: false,
      errorCode: 9999,
      errorMessage: t('tasks.service.errors.connection', 'Error de conexión. Intente nuevamente.'),
    };
  }
}

/**
 * Obtiene la consulta detallada de tareas (TR-044)
 *
 * @param params Parámetros de paginación y filtros (fecha_desde, fecha_hasta, tipo_cliente_id, cliente_id, usuario_id, ordenar_por, orden)
 * @returns Resultado con data, pagination, totalHoras o error (1305 período inválido)
 */
export async function getDetailReport(params: DetailReportParams = {}): Promise<GetDetailReportResult> {
  const token = getToken();

  if (!token) {
    return {
      success: false,
      errorCode: 4001,
      errorMessage: t('tasks.service.errors.notAuthenticated', 'No autenticado'),
    };
  }

  const searchParams = new URLSearchParams();
  if (params.page != null) searchParams.set('page', String(params.page));
  if (params.per_page != null) searchParams.set('per_page', String(params.per_page));
  if (params.fecha_desde) searchParams.set('fecha_desde', params.fecha_desde);
  if (params.fecha_hasta) searchParams.set('fecha_hasta', params.fecha_hasta);
  if (params.tipo_cliente_id != null) searchParams.set('tipo_cliente_id', String(params.tipo_cliente_id));
  if (params.cliente_id != null) searchParams.set('cliente_id', String(params.cliente_id));
  if (params.usuario_id != null) searchParams.set('usuario_id', String(params.usuario_id));
  if (params.ordenar_por) searchParams.set('ordenar_por', params.ordenar_por);
  if (params.orden) searchParams.set('orden', params.orden);

  const queryString = searchParams.toString();
  const url = `${API_BASE_URL}/v1/reports/detail${queryString ? `?${queryString}` : ''}`;

  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
    });

    const data = await response.json();

    if (!response.ok) {
      const errorData = data as ApiError;
      return {
        success: false,
        errorCode: errorData.error,
        errorMessage: errorData.respuesta,
      };
    }

    const result = data as ApiResponse<{ data: DetailReportItem[]; pagination: { current_page: number; per_page: number; total: number; last_page: number }; total_horas: number }>;
    const r = result.resultado;

    return {
      success: true,
      data: r.data,
      pagination: r.pagination,
      totalHoras: r.total_horas,
    };
  } catch (error) {
    console.error('Error en getDetailReport:', error);
    return {
      success: false,
      errorCode: 9999,
      errorMessage: t('tasks.service.errors.connection', 'Error de conexión. Intente nuevamente.'),
    };
  }
}

/**
 * Obtiene reporte agrupado por cliente (TR-046)
 *
 * @param params fecha_desde, fecha_hasta
 * @returns grupos, totalGeneralHoras, totalGeneralTareas o error (1305 período inválido)
 */
export async function getReportByClient(
  params: ByClientReportParams = {}
): Promise<GetByClientReportResult> {
  const token = getToken();

  if (!token) {
    return {
      success: false,
      errorCode: 4001,
      errorMessage: t('tasks.service.errors.notAuthenticated', 'No autenticado'),
    };
  }

  const searchParams = new URLSearchParams();
  if (params.fecha_desde) searchParams.set('fecha_desde', params.fecha_desde);
  if (params.fecha_hasta) searchParams.set('fecha_hasta', params.fecha_hasta);

  const queryString = searchParams.toString();
  const url = `${API_BASE_URL}/v1/reports/by-client${queryString ? `?${queryString}` : ''}`;

  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
    });

    const data = await response.json();

    if (!response.ok) {
      const errorData = data as ApiError;
      return {
        success: false,
        errorCode: errorData.error,
        errorMessage: errorData.respuesta,
      };
    }

    const result = data as ApiResponse<{
      grupos: ByClientGroup[];
      total_general_horas: number;
      total_general_tareas: number;
    }>;
    const r = result.resultado;

    return {
      success: true,
      grupos: r.grupos,
      totalGeneralHoras: r.total_general_horas,
      totalGeneralTareas: r.total_general_tareas,
    };
  } catch (error) {
    console.error('Error en getReportByClient:', error);
    return {
      success: false,
      errorCode: 9999,
      errorMessage: t('tasks.service.errors.connection', 'Error de conexión. Intente nuevamente.'),
    };
  }
}

/**
 * Obtiene reporte agrupado por empleado (TR-045). Solo supervisores.
 *
 * @param params fecha_desde, fecha_hasta, tipo_cliente_id, cliente_id, usuario_id (opcional)
 * @returns grupos, totalGeneralHoras, totalGeneralTareas o error (1305 período inválido, 403 no supervisor)
 */
export async function getReportByEmployee(
  params: ByEmployeeReportParams = {}
): Promise<GetByEmployeeReportResult> {
  const token = getToken();

  if (!token) {
    return {
      success: false,
      errorCode: 4001,
      errorMessage: t('tasks.service.errors.notAuthenticated', 'No autenticado'),
    };
  }

  const searchParams = new URLSearchParams();
  if (params.fecha_desde) searchParams.set('fecha_desde', params.fecha_desde);
  if (params.fecha_hasta) searchParams.set('fecha_hasta', params.fecha_hasta);
  if (params.tipo_cliente_id != null) searchParams.set('tipo_cliente_id', String(params.tipo_cliente_id));
  if (params.cliente_id != null) searchParams.set('cliente_id', String(params.cliente_id));
  if (params.usuario_id != null) searchParams.set('usuario_id', String(params.usuario_id));

  const queryString = searchParams.toString();
  const url = `${API_BASE_URL}/v1/reports/by-employee${queryString ? `?${queryString}` : ''}`;

  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
    });

    const data = await response.json();

    if (!response.ok) {
      const errorData = data as ApiError;
      return {
        success: false,
        errorCode: errorData.error,
        errorMessage: errorData.respuesta,
      };
    }

    const result = data as ApiResponse<{
      grupos: ByEmployeeGroup[];
      total_general_horas: number;
      total_general_tareas: number;
    }>;
    const r = result.resultado;

    return {
      success: true,
      grupos: r.grupos,
      totalGeneralHoras: r.total_general_horas,
      totalGeneralTareas: r.total_general_tareas,
    };
  } catch (error) {
    console.error('Error en getReportByEmployee:', error);
    return {
      success: false,
      errorCode: 9999,
      errorMessage: t('tasks.service.errors.connection', 'Error de conexión. Intente nuevamente.'),
    };
  }
}

/**
 * Obtiene reporte agrupado por tipo de tarea (TR-047). Solo supervisores.
 *
 * @param params fecha_desde, fecha_hasta, tipo_cliente_id, cliente_id, usuario_id (opcional)
 * @returns grupos, totalGeneralHoras, totalGeneralTareas o error (1305 período inválido, 403 no supervisor)
 */
export async function getReportByTaskType(
  params: ByTaskTypeReportParams = {}
): Promise<GetByTaskTypeReportResult> {
  const token = getToken();

  if (!token) {
    return {
      success: false,
      errorCode: 4001,
      errorMessage: t('tasks.service.errors.notAuthenticated', 'No autenticado'),
    };
  }

  const searchParams = new URLSearchParams();
  if (params.fecha_desde) searchParams.set('fecha_desde', params.fecha_desde);
  if (params.fecha_hasta) searchParams.set('fecha_hasta', params.fecha_hasta);
  if (params.tipo_cliente_id != null) searchParams.set('tipo_cliente_id', String(params.tipo_cliente_id));
  if (params.cliente_id != null) searchParams.set('cliente_id', String(params.cliente_id));
  if (params.usuario_id != null) searchParams.set('usuario_id', String(params.usuario_id));

  const queryString = searchParams.toString();
  const url = `${API_BASE_URL}/v1/reports/by-task-type${queryString ? `?${queryString}` : ''}`;

  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
    });

    const data = await response.json();

    if (!response.ok) {
      const errorData = data as ApiError;
      return {
        success: false,
        errorCode: errorData.error,
        errorMessage: errorData.respuesta,
      };
    }

    const result = data as ApiResponse<{
      grupos: ByTaskTypeGroup[];
      total_general_horas: number;
      total_general_tareas: number;
    }>;
    const r = result.resultado;

    return {
      success: true,
      grupos: r.grupos,
      totalGeneralHoras: r.total_general_horas,
      totalGeneralTareas: r.total_general_tareas,
    };
  } catch (error) {
    console.error('Error en getReportByTaskType:', error);
    return {
      success: false,
      errorCode: 9999,
      errorMessage: t('tasks.service.errors.connection', 'Error de conexión. Intente nuevamente.'),
    };
  }
}

/**
 * Obtiene reporte agrupado por fecha (TR-048). Empleado, supervisor y cliente (datos filtrados por rol).
 *
 * @param params fecha_desde, fecha_hasta
 * @returns grupos, totalGeneralHoras, totalGeneralTareas o error (1305 período inválido)
 */
export async function getReportByDate(
  params: ByDateReportParams = {}
): Promise<GetByDateReportResult> {
  const token = getToken();

  if (!token) {
    return {
      success: false,
      errorCode: 4001,
      errorMessage: t('tasks.service.errors.notAuthenticated', 'No autenticado'),
    };
  }

  const searchParams = new URLSearchParams();
  if (params.fecha_desde) searchParams.set('fecha_desde', params.fecha_desde);
  if (params.fecha_hasta) searchParams.set('fecha_hasta', params.fecha_hasta);

  const queryString = searchParams.toString();
  const url = `${API_BASE_URL}/v1/reports/by-date${queryString ? `?${queryString}` : ''}`;

  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
    });

    const data = await response.json();

    if (!response.ok) {
      const errorData = data as ApiError;
      return {
        success: false,
        errorCode: errorData.error,
        errorMessage: errorData.respuesta,
      };
    }

    const result = data as ApiResponse<{
      grupos: ByDateGroup[];
      total_general_horas: number;
      total_general_tareas: number;
    }>;
    const r = result.resultado;

    return {
      success: true,
      grupos: r.grupos,
      totalGeneralHoras: r.total_general_horas,
      totalGeneralTareas: r.total_general_tareas,
    };
  } catch (error) {
    console.error('Error en getReportByDate:', error);
    return {
      success: false,
      errorCode: 9999,
      errorMessage: t('tasks.service.errors.connection', 'Error de conexión. Intente nuevamente.'),
    };
  }
}

/**
 * Obtiene una tarea por ID para edición (TR-029)
 *
 * @param id ID de la tarea
 * @returns Resultado con datos de la tarea o error (404, 2110, 4030)
 */
export async function getTask(id: number): Promise<GetTaskResult> {
  const token = getToken();

  if (!token) {
    return {
      success: false,
      errorCode: 4001,
      errorMessage: t('tasks.service.errors.notAuthenticated', 'No autenticado'),
    };
  }

  try {
    const response = await fetch(`${API_BASE_URL}/v1/tasks/${id}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
    });

    const data = await response.json();

    if (!response.ok) {
      const errorData = data as ApiError;
      return {
        success: false,
        errorCode: errorData.error,
        errorMessage: errorData.respuesta,
      };
    }

    const taskResponse = data as ApiResponse<TaskForEdit>;
    return {
      success: true,
      task: taskResponse.resultado,
    };
  } catch (error) {
    console.error('Error en getTask:', error);
    return {
      success: false,
      errorCode: 9999,
      errorMessage: t('tasks.service.errors.connection', 'Error de conexión. Intente nuevamente.'),
    };
  }
}

/**
 * Actualiza una tarea existente (TR-029)
 *
 * @param id ID de la tarea
 * @param payload Datos a actualizar (sin usuario_id)
 * @returns Resultado con tarea actualizada o error (2110, 4030, 4220)
 */
export async function updateTask(id: number, payload: UpdateTaskData): Promise<UpdateTaskResult> {
  const token = getToken();

  if (!token) {
    return {
      success: false,
      errorCode: 4001,
      errorMessage: t('tasks.service.errors.notAuthenticated', 'No autenticado'),
    };
  }

  try {
    const response = await fetch(`${API_BASE_URL}/v1/tasks/${id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify(payload),
    });

    const data = await response.json();

    if (!response.ok) {
      const errorData = data as ApiError;
      return {
        success: false,
        errorCode: errorData.error,
        errorMessage: errorData.respuesta,
        validationErrors: errorData.resultado?.errors,
      };
    }

    const taskResponse = data as ApiResponse<Task>;
    return {
      success: true,
      task: taskResponse.resultado,
    };
  } catch (error) {
    console.error('Error en updateTask:', error);
    return {
      success: false,
      errorCode: 9999,
      errorMessage: t('tasks.service.errors.connection', 'Error de conexión. Intente nuevamente.'),
    };
  }
}

/**
 * Elimina una tarea existente (TR-030)
 *
 * @param id ID de la tarea
 * @returns Resultado o error (2111 cerrada, 4030 sin permisos, 4040 no encontrada)
 */
export async function deleteTask(id: number): Promise<DeleteTaskResult> {
  const token = getToken();

  if (!token) {
    return {
      success: false,
      errorCode: 4001,
      errorMessage: t('tasks.service.errors.notAuthenticated', 'No autenticado'),
    };
  }

  try {
    const response = await fetch(`${API_BASE_URL}/v1/tasks/${id}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
    });

    const data = await response.json();

    if (!response.ok) {
      const errorData = data as ApiError;
      return {
        success: false,
        errorCode: errorData.error,
        errorMessage: errorData.respuesta,
      };
    }

    return { success: true };
  } catch (error) {
    console.error('Error en deleteTask:', error);
    return {
      success: false,
      errorCode: 9999,
      errorMessage: t('tasks.service.errors.connection', 'Error de conexión. Intente nuevamente.'),
    };
  }
}

/**
 * Obtiene datos del dashboard (TR-051)
 *
 * @param params fecha_desde, fecha_hasta (YYYY-MM-DD)
 * @returns Datos del dashboard o error (1305 período inválido)
 */
export async function getDashboard(
  params: DashboardParams
): Promise<GetDashboardResult> {
  const token = getToken();

  if (!token) {
    return {
      success: false,
      errorCode: 4001,
      errorMessage: t('tasks.service.errors.notAuthenticated', 'No autenticado'),
    };
  }

  const searchParams = new URLSearchParams();
  searchParams.set('fecha_desde', params.fecha_desde);
  searchParams.set('fecha_hasta', params.fecha_hasta);
  const url = `${API_BASE_URL}/v1/dashboard?${searchParams.toString()}`;

  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), 15000);

  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
      signal: controller.signal,
    });
    clearTimeout(timeoutId);

    const data = await response.json();

    if (!response.ok) {
      const errorData = data as ApiError;
      return {
        success: false,
        errorCode: errorData.error,
        errorMessage: errorData.respuesta,
      };
    }

    const result = data as ApiResponse<DashboardData>;
    return {
      success: true,
      data: result.resultado,
    };
  } catch (error) {
    clearTimeout(timeoutId);
    const isAbort = error instanceof Error && error.name === 'AbortError';
    console.error('Error en getDashboard:', error);
    return {
      success: false,
      errorCode: 9999,
      errorMessage: isAbort
        ? 'Tiempo de espera agotado. Compruebe que el backend esté en marcha.'
        : t('tasks.service.errors.connection', 'Error de conexión. Intente nuevamente.'),
    };
  }
}
