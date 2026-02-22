/**
 * Component: EmpleadosPage
 *
 * Listado paginado de empleados (solo supervisores). TR-018(MH), TR-021(MH).
 * Tabla con búsqueda, filtros (supervisor, estado, inhabilitado), paginación y total.
 * Modal de confirmación para eliminar empleados.
 *
 * @see TR-018(MH)-listado-de-empleados.md
 * @see TR-021(MH)-eliminación-de-empleado.md
 */

import React, { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { getEmpleados, deleteEmpleado, EmpleadoListItem } from '../services/empleado.service';
import { TaskPagination } from '../../tasks/components/TaskPagination';
import './EmpleadosPage.css';

const DEFAULT_PAGE_SIZE = 15;

export interface EmpleadosFilters {
  search: string;
  supervisor: '' | 'true' | 'false';
  activo: '' | 'true' | 'false';
  inhabilitado: '' | 'true' | 'false';
}

const defaultFilters: EmpleadosFilters = {
  search: '',
  supervisor: '',
  activo: '',
  inhabilitado: '',
};

function buildParams(
  page: number,
  filters: EmpleadosFilters,
  pageSize: number
): Parameters<typeof getEmpleados>[0] {
  const params: Parameters<typeof getEmpleados>[0] = {
    page,
    page_size: pageSize,
    sort: 'nombre',
    sort_dir: 'asc',
  };
  if (filters.search.trim()) params.search = filters.search.trim();
  if (filters.supervisor === 'true') params.supervisor = true;
  if (filters.supervisor === 'false') params.supervisor = false;
  if (filters.activo === 'true') params.activo = true;
  if (filters.activo === 'false') params.activo = false;
  if (filters.inhabilitado === 'true') params.inhabilitado = true;
  if (filters.inhabilitado === 'false') params.inhabilitado = false;
  return params;
}

export function EmpleadosPage(): React.ReactElement {
  const navigate = useNavigate();
  const [data, setData] = useState<EmpleadoListItem[]>([]);
  const [pagination, setPagination] = useState({
    current_page: 1,
    per_page: DEFAULT_PAGE_SIZE,
    total: 0,
    last_page: 1,
  });
  const [filters, setFilters] = useState<EmpleadosFilters>(defaultFilters);
  const [appliedFilters, setAppliedFilters] = useState<EmpleadosFilters>(defaultFilters);
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState<string>('');
  const [empleadoToDelete, setEmpleadoToDelete] = useState<EmpleadoListItem | null>(null);
  const [deleteLoading, setDeleteLoading] = useState(false);
  const [deleteError, setDeleteError] = useState('');
  const [successMessage, setSuccessMessage] = useState('');

  const loadEmpleados = useCallback(
    async (params: Parameters<typeof getEmpleados>[0]) => {
      setLoading(true);
      setErrorMessage('');
      const result = await getEmpleados(params);
      setLoading(false);
      if (result.success && result.data !== undefined) {
        setData(result.data);
        if (result.pagination) {
          setPagination({
            current_page: result.pagination.page,
            per_page: result.pagination.page_size,
            total: result.pagination.total,
            last_page: result.pagination.total_pages,
          });
        }
      } else {
        setErrorMessage(result.errorMessage || 'Error al cargar empleados');
        setData([]);
      }
    },
    []
  );

  useEffect(() => {
    const params = buildParams(page, appliedFilters, DEFAULT_PAGE_SIZE);
    loadEmpleados(params);
  }, [page, appliedFilters, loadEmpleados]);

  const handleApplyFilters = () => {
    setAppliedFilters(filters);
    setPage(1);
  };

  const handlePageChange = (newPage: number) => {
    setPage(Math.max(1, Math.min(newPage, pagination.last_page)));
  };

  const handleDeleteClick = (e: EmpleadoListItem) => {
    setEmpleadoToDelete(e);
    setDeleteError('');
  };

  const handleDeleteCancel = () => {
    setEmpleadoToDelete(null);
    setDeleteError('');
  };

  const handleDeleteConfirm = async () => {
    if (!empleadoToDelete) return;
    setDeleteLoading(true);
    setDeleteError('');
    const result = await deleteEmpleado(empleadoToDelete.id);
    setDeleteLoading(false);
    if (result.success) {
      setEmpleadoToDelete(null);
      setSuccessMessage('Empleado eliminado correctamente.');
      const params = buildParams(page, appliedFilters, DEFAULT_PAGE_SIZE);
      loadEmpleados(params);
    } else {
      setDeleteError(result.errorMessage ?? 'Error al eliminar empleado');
    }
  };

  return (
    <div className="empleados-page" data-testid="empleados.list">
      <header className="empleados-page-header">
        <h1 className="empleados-page-title">Empleados</h1>
        <button
          type="button"
          className="empleados-page-btn-create"
          onClick={() => navigate('/empleados/nuevo')}
          data-testid="empleados.create"
          aria-label="Crear empleado"
        >
          Crear empleado
        </button>
      </header>

      <section className="empleados-page-filters" data-testid="empleados.filters">
        <div className="empleados-filters-row">
          <label className="empleados-filter-label">
            <span className="empleados-filter-label-text">Búsqueda (código, nombre o email)</span>
            <input
              type="text"
              value={filters.search}
              onChange={(e) => setFilters((f) => ({ ...f, search: e.target.value }))}
              placeholder="Buscar..."
              className="empleados-filter-input"
              data-testid="empleados.search"
              aria-label="Búsqueda por código, nombre o email"
            />
          </label>
          <label className="empleados-filter-label">
            <span className="empleados-filter-label-text">Supervisor</span>
            <select
              value={filters.supervisor}
              onChange={(e) =>
                setFilters((f) => ({
                  ...f,
                  supervisor: e.target.value as '' | 'true' | 'false',
                }))
              }
              className="empleados-filter-select"
              data-testid="empleados.filter.supervisor"
              aria-label="Filtrar por rol supervisor"
            >
              <option value="">Todos</option>
              <option value="true">Sí</option>
              <option value="false">No</option>
            </select>
          </label>
          <label className="empleados-filter-label">
            <span className="empleados-filter-label-text">Estado</span>
            <select
              value={filters.activo}
              onChange={(e) =>
                setFilters((f) => ({
                  ...f,
                  activo: e.target.value as '' | 'true' | 'false',
                }))
              }
              className="empleados-filter-select"
              data-testid="empleados.filter.activo"
              aria-label="Filtrar por estado activo"
            >
              <option value="">Todos</option>
              <option value="true">Activo</option>
              <option value="false">Inactivo</option>
            </select>
          </label>
          <label className="empleados-filter-label">
            <span className="empleados-filter-label-text">Inhabilitado</span>
            <select
              value={filters.inhabilitado}
              onChange={(e) =>
                setFilters((f) => ({
                  ...f,
                  inhabilitado: e.target.value as '' | 'true' | 'false',
                }))
              }
              className="empleados-filter-select"
              data-testid="empleados.filter.inhabilitado"
              aria-label="Filtrar por inhabilitado"
            >
              <option value="">Todos</option>
              <option value="false">No</option>
              <option value="true">Sí</option>
            </select>
          </label>
          <button
            type="button"
            className="empleados-filter-btn-apply"
            onClick={handleApplyFilters}
            data-testid="empleados.filters.apply"
            aria-label="Aplicar filtros"
          >
            Aplicar
          </button>
        </div>
      </section>

      {loading && (
        <div className="empleados-page-loading" data-testid="empleados.loading" role="status">
          Cargando empleados...
        </div>
      )}

      {errorMessage && (
        <div className="empleados-page-error" data-testid="empleados.error" role="alert">
          {errorMessage}
        </div>
      )}

      {successMessage && (
        <div className="empleados-page-success" data-testid="empleados.success" role="alert">
          {successMessage}
        </div>
      )}

      {!loading && !errorMessage && (
        <>
          <p className="empleados-page-total" data-testid="empleados.total">
            Total de usuarios: <strong>{pagination.total}</strong>
          </p>

          {data.length === 0 ? (
            <p className="empleados-page-empty" data-testid="empleados.empty" role="status">
              No se encontraron empleados.
            </p>
          ) : (
            <div className="empleados-table-wrapper">
              <table className="empleados-table" data-testid="empleados.table" aria-label="Listado de empleados">
                <thead>
                  <tr>
                    <th scope="col">Código</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Email</th>
                    <th scope="col">Supervisor</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Inhabilitado</th>
                    <th scope="col">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {data.map((e) => (
                    <tr
                      key={e.id}
                      className={e.inhabilitado ? 'empleados-table-row-inhabilitado' : ''}
                      data-testid={`empleados.row.${e.id}`}
                    >
                      <td>{e.code}</td>
                      <td>{e.nombre}</td>
                      <td>{e.email ?? '—'}</td>
                      <td>{e.supervisor ? 'Sí' : 'No'}</td>
                      <td>{e.activo ? 'Activo' : 'Inactivo'}</td>
                      <td>{e.inhabilitado ? 'Sí' : 'No'}</td>
                      <td>
                        <button
                          type="button"
                          className="empleados-page-btn-detail"
                          onClick={() => navigate(`/empleados/${e.id}`)}
                          data-testid={`empleados.detail.${e.id}`}
                          aria-label={`Ver detalle de ${e.nombre}`}
                        >
                          Ver detalle
                        </button>
                        <button
                          type="button"
                          className="empleados-page-btn-edit"
                          onClick={() => navigate(`/empleados/${e.id}/editar`)}
                          data-testid={`empleados.edit.${e.id}`}
                          aria-label={`Editar empleado ${e.nombre}`}
                        >
                          Editar
                        </button>
                        <button
                          type="button"
                          className="empleados-page-btn-delete"
                          onClick={() => handleDeleteClick(e)}
                          data-testid={`empleados.row.${e.id}.delete`}
                          aria-label={`Eliminar empleado ${e.nombre}`}
                        >
                          Eliminar
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}

          <TaskPagination
            currentPage={pagination.current_page}
            lastPage={pagination.last_page}
            total={pagination.total}
            perPage={pagination.per_page}
            onPageChange={handlePageChange}
            disabled={loading}
          />
        </>
      )}

      {empleadoToDelete && (
        <div
          className="empleados-delete-modal-overlay"
          role="dialog"
          aria-modal="true"
          aria-labelledby="empleados-delete-modal-title"
          data-testid="empleados.delete.modal"
        >
          <div className="empleados-delete-modal">
            <h2 id="empleados-delete-modal-title" className="empleados-delete-modal-title">
              Eliminar empleado
            </h2>
            <p className="empleados-delete-modal-text">
              ¿Está seguro de eliminar el empleado <strong data-testid="empleados.delete.code">{empleadoToDelete.code}</strong> – <strong data-testid="empleados.delete.nombre">{empleadoToDelete.nombre}</strong>?
            </p>
            {deleteError && (
              <div className="empleados-delete-modal-error" role="alert" data-testid="empleados.delete.error">
                {deleteError}
              </div>
            )}
            <div className="empleados-delete-modal-actions">
              <button
                type="button"
                className="empleados-delete-modal-btn-cancel"
                onClick={handleDeleteCancel}
                disabled={deleteLoading}
                data-testid="empleados.delete.cancel"
              >
                Cancelar
              </button>
              <button
                type="button"
                className="empleados-page-btn-delete"
                onClick={handleDeleteConfirm}
                disabled={deleteLoading}
                data-testid="empleados.delete.confirm"
                aria-busy={deleteLoading}
              >
                {deleteLoading ? 'Eliminando...' : 'Confirmar'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default EmpleadosPage;
