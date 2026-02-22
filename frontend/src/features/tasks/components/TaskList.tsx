/**
 * Component: TaskList
 *
 * Lista paginada de tareas propias con filtros, totales y acciones editar/eliminar.
 * TR-033(MH)-visualización-de-lista-de-tareas-propias.
 * TR-030(MH)-eliminación-de-tarea-propia: modal de confirmación y deleteTask.
 */

import React, { useCallback, useEffect, useState, useRef } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { getTasks, deleteTask, TaskListItem, TaskListParams } from '../services/task.service';
import { TaskFilters, type TaskFiltersValues } from './TaskFilters';
import { TaskPagination } from './TaskPagination';
import { TaskTotals } from './TaskTotals';
import { DeleteTaskModal } from './DeleteTaskModal';
import { t } from '../../../shared/i18n';
import './TaskList.css';

const DEFAULT_PER_PAGE = 15;
const DEFAULT_FILTERS: TaskFiltersValues = {
  fechaDesde: '',
  fechaHasta: '',
  clienteId: null,
  tipoTareaId: null,
  empleadoId: null,
  busqueda: '',
  orden: 'desc',
};

function buildParams(
  page: number,
  filters: TaskFiltersValues,
  perPage: number
): TaskListParams {
  const params: TaskListParams = {
    page,
    per_page: perPage,
    ordenar_por: 'fecha',
    orden: filters.orden,
  };
  if (filters.fechaDesde) params.fecha_desde = filters.fechaDesde;
  if (filters.fechaHasta) params.fecha_hasta = filters.fechaHasta;
  if (filters.clienteId != null) params.cliente_id = filters.clienteId;
  if (filters.tipoTareaId != null) params.tipo_tarea_id = filters.tipoTareaId;
  if (filters.empleadoId != null) params.usuario_id = filters.empleadoId;
  if (filters.busqueda.trim()) params.busqueda = filters.busqueda.trim();
  return params;
}

export interface TaskListReturnState {
  returnFilters: TaskFiltersValues;
  returnPage: number;
}

function getInitialStateFromLocation(location: ReturnType<typeof useLocation>) {
  const state = location.state as TaskListReturnState | null;
  return {
    filters: state?.returnFilters ?? DEFAULT_FILTERS,
    appliedFilters: state?.returnFilters ?? DEFAULT_FILTERS,
    page: state?.returnPage ?? 1,
  };
}

export function TaskList(): React.ReactElement {
  const navigate = useNavigate();
  const location = useLocation();
  const initialState = getInitialStateFromLocation(location);
  const [data, setData] = useState<TaskListItem[]>([]);
  const [pagination, setPagination] = useState({
    current_page: 1,
    per_page: DEFAULT_PER_PAGE,
    total: 0,
    last_page: 1,
  });
  const [totales, setTotales] = useState({ cantidad_tareas: 0, total_horas: 0 });
  const [filters, setFilters] = useState<TaskFiltersValues>(initialState.filters);
  const [appliedFilters, setAppliedFilters] = useState<TaskFiltersValues>(initialState.appliedFilters);
  const [page, setPage] = useState(initialState.page);
  const [loading, setLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState<string>('');
  const [taskToDelete, setTaskToDelete] = useState<TaskListItem | null>(null);
  const [deleteLoading, setDeleteLoading] = useState(false);
  const [deleteError, setDeleteError] = useState<string>('');
  const [successMessage, setSuccessMessage] = useState<string>('');

  const loadTasks = useCallback(async (params: TaskListParams) => {
    setLoading(true);
    setErrorMessage('');
    const result = await getTasks(params);
    setLoading(false);
    if (result.success && result.data !== undefined) {
      setData(result.data);
      if (result.pagination) setPagination(result.pagination);
      if (result.totales) setTotales(result.totales);
    } else {
      setErrorMessage(result.errorMessage || t('tasks.list.error.load', 'Error al cargar tareas'));
      setData([]);
    }
  }, []);

  useEffect(() => {
    const params = buildParams(page, appliedFilters, DEFAULT_PER_PAGE);
    loadTasks(params);
  }, [page, appliedFilters, loadTasks]);

  // Restaurar filtros y página al volver de edición (location.state puede llegar después del primer render)
  const prevLocationKey = useRef<string | undefined>(undefined);
  useEffect(() => {
    const state = location.state as TaskListReturnState | null;
    const hasReturnState =
      state?.returnFilters != null &&
      typeof state?.returnPage === 'number' &&
      location.pathname === '/tareas';
    if (hasReturnState && prevLocationKey.current !== location.key) {
      prevLocationKey.current = location.key;
      setFilters(state.returnFilters);
      setAppliedFilters(state.returnFilters);
      setPage(state.returnPage);
    }
    if (!hasReturnState) {
      prevLocationKey.current = location.key;
    }
  }, [location.pathname, location.key, location.state]);

  const handleApplyFilters = () => {
    setAppliedFilters(filters);
    setPage(1);
  };

  const handlePageChange = (newPage: number) => {
    setPage(newPage);
  };

  const handleEdit = (id: number) => {
    navigate(`/tareas/${id}/editar`, {
      state: { returnFilters: appliedFilters, returnPage: page } as TaskListReturnState,
    });
  };

  const handleDeleteClick = (row: TaskListItem) => {
    if (row.cerrado) return;
    setDeleteError('');
    setTaskToDelete(row);
  };

  const handleConfirmDelete = async () => {
    if (!taskToDelete) return;
    setDeleteLoading(true);
    setDeleteError('');
    const result = await deleteTask(taskToDelete.id);
    setDeleteLoading(false);
    if (result.success) {
      setTaskToDelete(null);
      setSuccessMessage(t('tasks.delete.success', 'Tarea eliminada correctamente'));
      const params = buildParams(page, appliedFilters, DEFAULT_PER_PAGE);
      await loadTasks(params);
      setTimeout(() => setSuccessMessage(''), 3000);
    } else {
      setDeleteError(result.errorMessage ?? t('tasks.list.error.load', 'Error al eliminar'));
    }
  };

  const handleCancelDelete = () => {
    setTaskToDelete(null);
    setDeleteError('');
  };

  return (
    <div className="task-list-container" data-testid="task.list.container">
      <header className="task-list-header">
        <h1 className="task-list-title">{t('tasks.list.title', 'Mis Tareas')}</h1>
      </header>

      <TaskFilters
        values={filters}
        onChange={setFilters}
        onApply={handleApplyFilters}
        disabled={loading}
      />

      {errorMessage && (
        <div className="task-list-error" data-testid="task.list.error" role="alert">
          {errorMessage}
        </div>
      )}

      {successMessage && (
        <div className="task-list-success" data-testid="task.list.deleteSuccess" role="status">
          {successMessage}
        </div>
      )}

      {taskToDelete && (
        <DeleteTaskModal
          task={taskToDelete}
          loading={deleteLoading}
          errorMessage={deleteError}
          onConfirm={handleConfirmDelete}
          onCancel={handleCancelDelete}
        />
      )}

      {!errorMessage && (
        <>
          <TaskTotals
            cantidadTareas={totales.cantidad_tareas}
            totalHoras={totales.total_horas}
          />

          {loading ? (
            <div className="task-list-loading" data-testid="task.list.loading">
              {t('tasks.list.loading', 'Cargando...')}
            </div>
          ) : data.length === 0 ? (
            <div className="task-list-empty" data-testid="task.list.empty">
              {t('tasks.list.empty', 'No hay tareas que mostrar.')}
            </div>
          ) : (
            <div className="task-list-table-wrapper">
              <table className="task-list-table" data-testid="task.list.table" role="table">
                <thead>
                  <tr>
                    <th scope="col">{t('tasks.list.col.fecha', 'Fecha')}</th>
                    <th scope="col">{t('tasks.list.col.cliente', 'Cliente')}</th>
                    <th scope="col">{t('tasks.list.col.tipoTarea', 'Tipo tarea')}</th>
                    <th scope="col">{t('tasks.list.col.duracion', 'Duración')}</th>
                    <th scope="col">{t('tasks.list.col.sinCargo', 'Sin cargo')}</th>
                    <th scope="col">{t('tasks.list.col.presencial', 'Presencial')}</th>
                    <th scope="col">{t('tasks.list.col.observacion', 'Observación')}</th>
                    <th scope="col">{t('tasks.list.col.cerrado', 'Cerrado')}</th>
                    <th scope="col">{t('tasks.list.col.acciones', 'Acciones')}</th>
                  </tr>
                </thead>
                <tbody>
                  {data.map((row) => (
                    <tr
                      key={row.id}
                      data-testid={`task.list.row.${row.id}`}
                      className={row.cerrado ? 'task-list-row-closed' : ''}
                    >
                      <td>{row.fecha}</td>
                      <td>{row.cliente.nombre}</td>
                      <td>{row.tipo_tarea.nombre}</td>
                      <td>{row.duracion_horas}</td>
                      <td>{row.sin_cargo ? t('tasks.list.si', 'Sí') : t('tasks.list.no', 'No')}</td>
                      <td>{row.presencial ? t('tasks.list.si', 'Sí') : t('tasks.list.no', 'No')}</td>
                      <td>{row.observacion}</td>
                      <td>{row.cerrado ? t('tasks.list.si', 'Sí') : t('tasks.list.no', 'No')}</td>
                      <td>
                        <div className="task-list-cell-actions">
                          <button
                            type="button"
                            className="task-list-btn-action task-list-btn-edit"
                            onClick={() => handleEdit(row.id)}
                            disabled={row.cerrado}
                            data-testid={`task.list.edit.${row.id}`}
                            aria-label={t('tasks.list.editLabel', 'Editar tarea')}
                          >
                            {t('tasks.list.edit', 'Editar')}
                          </button>
                          <button
                            type="button"
                            className="task-list-btn-action task-list-btn-delete"
                            onClick={() => handleDeleteClick(row)}
                            disabled={row.cerrado}
                            data-testid={`task.list.delete.${row.id}`}
                            aria-label={t('tasks.list.deleteLabel', 'Eliminar tarea')}
                          >
                            {t('tasks.list.delete', 'Eliminar')}
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}

          {!loading && data.length > 0 && (
            <TaskPagination
              currentPage={pagination.current_page}
              lastPage={pagination.last_page}
              total={pagination.total}
              perPage={pagination.per_page}
              onPageChange={handlePageChange}
              disabled={loading}
            />
          )}
        </>
      )}
    </div>
  );
}
