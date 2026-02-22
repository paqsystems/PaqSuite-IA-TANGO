/**
 * Component: TiposTareaPage
 *
 * Listado paginado de tipos de tarea (solo supervisores). TR-023(MH).
 * Tabla con búsqueda, filtros (genérico, por defecto, activo, inhabilitado), paginación, total.
 * Acciones: Crear, Editar, Eliminar, Ver (TR-024, TR-025, TR-026, TR-027).
 *
 * @see TR-023(MH)-listado-de-tipos-de-tarea.md
 */

import React, { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  getTiposTareaList,
  deleteTipoTarea,
  TipoTareaListItem,
  ERROR_EN_USO,
} from '../services/tipoTarea.service';
import { TaskPagination } from '../../tasks/components/TaskPagination';
import './TiposTareaPage.css';

const DEFAULT_PAGE_SIZE = 15;

export interface TiposTareaFilters {
  search: string;
  is_generico: '' | 'true' | 'false';
  is_default: '' | 'true' | 'false';
  activo: '' | 'true' | 'false';
  inhabilitado: '' | 'true' | 'false';
}

const defaultFilters: TiposTareaFilters = {
  search: '',
  is_generico: '',
  is_default: '',
  activo: '',
  inhabilitado: '',
};

function buildParams(
  page: number,
  filters: TiposTareaFilters,
  pageSize: number
): Parameters<typeof getTiposTareaList>[0] {
  const params: Parameters<typeof getTiposTareaList>[0] = {
    page,
    page_size: pageSize,
    sort: 'descripcion',
    sort_dir: 'asc',
  };
  if (filters.search.trim()) params.search = filters.search.trim();
  if (filters.is_generico === 'true') params.is_generico = true;
  if (filters.is_generico === 'false') params.is_generico = false;
  if (filters.is_default === 'true') params.is_default = true;
  if (filters.is_default === 'false') params.is_default = false;
  if (filters.activo === 'true') params.activo = true;
  if (filters.activo === 'false') params.activo = false;
  if (filters.inhabilitado === 'true') params.inhabilitado = true;
  if (filters.inhabilitado === 'false') params.inhabilitado = false;
  return params;
}

export function TiposTareaPage(): React.ReactElement {
  const navigate = useNavigate();
  const [data, setData] = useState<TipoTareaListItem[]>([]);
  const [pagination, setPagination] = useState({
    current_page: 1,
    per_page: DEFAULT_PAGE_SIZE,
    total: 0,
    last_page: 1,
  });
  const [filters, setFilters] = useState<TiposTareaFilters>(defaultFilters);
  const [appliedFilters, setAppliedFilters] = useState<TiposTareaFilters>(defaultFilters);
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState<string>('');
  const [tipoToDelete, setTipoToDelete] = useState<TipoTareaListItem | null>(null);
  const [deleteLoading, setDeleteLoading] = useState(false);
  const [deleteError, setDeleteError] = useState('');
  const [successMessage, setSuccessMessage] = useState('');

  const loadTipos = useCallback(async (params: Parameters<typeof getTiposTareaList>[0]) => {
    setLoading(true);
    setErrorMessage('');
    const result = await getTiposTareaList(params);
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
      setErrorMessage(result.errorMessage || 'Error al cargar tipos de tarea');
      setData([]);
    }
  }, []);

  useEffect(() => {
    const params = buildParams(page, appliedFilters, DEFAULT_PAGE_SIZE);
    loadTipos(params);
  }, [page, appliedFilters, loadTipos]);

  const handleApplyFilters = () => {
    setAppliedFilters(filters);
    setPage(1);
  };

  const handlePageChange = (newPage: number) => {
    setPage(Math.max(1, Math.min(newPage, pagination.last_page)));
  };

  const handleDeleteClick = (t: TipoTareaListItem) => {
    setTipoToDelete(t);
    setDeleteError('');
  };

  const handleDeleteCancel = () => {
    setTipoToDelete(null);
    setDeleteError('');
  };

  const handleDeleteConfirm = async () => {
    if (!tipoToDelete) return;
    setDeleteLoading(true);
    setDeleteError('');
    const result = await deleteTipoTarea(tipoToDelete.id);
    setDeleteLoading(false);
    if (result.success) {
      setTipoToDelete(null);
      setSuccessMessage('Tipo de tarea eliminado correctamente.');
      loadTipos(buildParams(page, appliedFilters, DEFAULT_PAGE_SIZE));
      setTimeout(() => setSuccessMessage(''), 3000);
    } else {
      setDeleteError(
        result.errorCode === ERROR_EN_USO
          ? 'No se puede eliminar el tipo de tarea porque está en uso (tareas o clientes asociados).'
          : (result.errorMessage ?? 'Error al eliminar')
      );
    }
  };

  return (
    <div className="tipos-tarea-page" data-testid="tiposTarea.list">
      <header className="tipos-tarea-page-header">
        <h1 className="tipos-tarea-page-title">Tipos de Tarea</h1>
        <button
          type="button"
          className="tipos-tarea-page-btn-create"
          onClick={() => navigate('/tipos-tarea/nuevo')}
          data-testid="tiposTarea.crear"
          aria-label="Crear tipo de tarea"
        >
          Crear tipo de tarea
        </button>
      </header>

      <section className="tipos-tarea-page-filters" data-testid="tiposTarea.filters">
        <div className="tipos-tarea-filters-row">
          <label className="tipos-tarea-filter-label">
            <span className="tipos-tarea-filter-label-text">Búsqueda (código o descripción)</span>
            <input
              type="text"
              value={filters.search}
              onChange={(e) => setFilters((f) => ({ ...f, search: e.target.value }))}
              placeholder="Buscar..."
              className="tipos-tarea-filter-input"
              data-testid="tiposTarea.busqueda"
              aria-label="Búsqueda"
            />
          </label>
          <label className="tipos-tarea-filter-label">
            <span className="tipos-tarea-filter-label-text">Genérico</span>
            <select
              value={filters.is_generico}
              onChange={(e) => setFilters((f) => ({ ...f, is_generico: e.target.value as '' | 'true' | 'false' }))}
              className="tipos-tarea-filter-select"
              data-testid="tiposTarea.filtroGenerico"
              aria-label="Filtrar por genérico"
            >
              <option value="">Todos</option>
              <option value="true">Sí</option>
              <option value="false">No</option>
            </select>
          </label>
          <label className="tipos-tarea-filter-label">
            <span className="tipos-tarea-filter-label-text">Por defecto</span>
            <select
              value={filters.is_default}
              onChange={(e) => setFilters((f) => ({ ...f, is_default: e.target.value as '' | 'true' | 'false' }))}
              className="tipos-tarea-filter-select"
              data-testid="tiposTarea.filtroPorDefecto"
              aria-label="Filtrar por defecto"
            >
              <option value="">Todos</option>
              <option value="true">Sí</option>
              <option value="false">No</option>
            </select>
          </label>
          <label className="tipos-tarea-filter-label">
            <span className="tipos-tarea-filter-label-text">Estado</span>
            <select
              value={filters.activo}
              onChange={(e) => setFilters((f) => ({ ...f, activo: e.target.value as '' | 'true' | 'false' }))}
              className="tipos-tarea-filter-select"
              data-testid="tiposTarea.filtroActivo"
              aria-label="Filtrar por activo"
            >
              <option value="">Todos</option>
              <option value="true">Activo</option>
              <option value="false">Inactivo</option>
            </select>
          </label>
          <label className="tipos-tarea-filter-label">
            <span className="tipos-tarea-filter-label-text">Inhabilitado</span>
            <select
              value={filters.inhabilitado}
              onChange={(e) => setFilters((f) => ({ ...f, inhabilitado: e.target.value as '' | 'true' | 'false' }))}
              className="tipos-tarea-filter-select"
              data-testid="tiposTarea.filtroInhabilitado"
              aria-label="Filtrar por inhabilitado"
            >
              <option value="">Todos</option>
              <option value="false">No</option>
              <option value="true">Sí</option>
            </select>
          </label>
          <button
            type="button"
            className="tipos-tarea-filter-btn-apply"
            onClick={handleApplyFilters}
            data-testid="tiposTarea.filters.apply"
            aria-label="Aplicar filtros"
          >
            Aplicar
          </button>
        </div>
      </section>

      {loading && (
        <div className="tipos-tarea-page-loading" data-testid="tiposTarea.loading" role="status">
          Cargando tipos de tarea...
        </div>
      )}

      {errorMessage && (
        <div className="tipos-tarea-page-error" data-testid="tiposTarea.error" role="alert">
          {errorMessage}
        </div>
      )}

      {successMessage && (
        <div className="tipos-tarea-page-success" data-testid="tiposTarea.success" role="status">
          {successMessage}
        </div>
      )}

      {!loading && !errorMessage && (
        <>
          <p className="tipos-tarea-page-total" data-testid="tiposTarea.total">
            Total de tipos de tarea: <strong>{pagination.total}</strong>
          </p>

          {data.length === 0 ? (
            <p className="tipos-tarea-page-empty" data-testid="tiposTarea.empty" role="status">
              No se encontraron tipos de tarea.
            </p>
          ) : (
            <div className="tipos-tarea-table-wrapper">
              <table className="tipos-tarea-table" data-testid="tiposTarea.tabla" aria-label="Listado de tipos de tarea">
                <thead>
                  <tr>
                    <th scope="col">Código</th>
                    <th scope="col">Descripción</th>
                    <th scope="col">Genérico</th>
                    <th scope="col">Por defecto</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Inhabilitado</th>
                    <th scope="col">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {data.map((t) => (
                    <tr
                      key={t.id}
                      className={
                        t.inhabilitado
                          ? 'tipos-tarea-table-row-inhabilitado'
                          : t.is_default
                            ? 'tipos-tarea-table-row-por-defecto'
                            : ''
                      }
                      data-testid={`tiposTarea.row.${t.id}`}
                    >
                      <td>{t.code}</td>
                      <td>{t.descripcion}</td>
                      <td>{t.is_generico ? 'Sí' : 'No'}</td>
                      <td>{t.is_default ? 'Sí' : 'No'}</td>
                      <td>{t.activo ? 'Activo' : 'Inactivo'}</td>
                      <td>{t.inhabilitado ? 'Sí' : 'No'}</td>
                      <td>
                        <button
                          type="button"
                          className="tipos-tarea-page-btn-ver"
                          onClick={() => navigate(`/tipos-tarea/${t.id}`)}
                          data-testid={`tiposTarea.ver.${t.id}`}
                          aria-label={`Ver detalle de ${t.code}`}
                        >
                          Ver
                        </button>
                        <button
                          type="button"
                          className="tipos-tarea-page-btn-edit"
                          onClick={() => navigate(`/tipos-tarea/${t.id}/editar`)}
                          data-testid={`tiposTarea.editar.${t.id}`}
                          aria-label={`Editar tipo ${t.code}`}
                        >
                          Editar
                        </button>
                        <button
                          type="button"
                          className="tipos-tarea-page-btn-delete"
                          onClick={() => handleDeleteClick(t)}
                          data-testid={`tiposTarea.eliminar.${t.id}`}
                          aria-label={`Eliminar tipo ${t.code}`}
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

          {pagination.last_page > 1 && (
            <TaskPagination
              currentPage={pagination.current_page}
              lastPage={pagination.last_page}
              total={pagination.total}
              perPage={pagination.per_page}
              onPageChange={handlePageChange}
              testIdPrefix="tiposTarea"
            />
          )}
        </>
      )}

      {tipoToDelete && (
        <div className="tipos-tarea-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="tipos-tarea-delete-title" data-testid="tiposTarea.delete.modal">
          <div className="tipos-tarea-modal">
            <h2 id="tipos-tarea-delete-title">Confirmar eliminación</h2>
            <p>
              ¿Eliminar el tipo de tarea <strong>{tipoToDelete.code}</strong> – {tipoToDelete.descripcion}?
            </p>
            {deleteError && <p className="tipos-tarea-modal-error" role="alert">{deleteError}</p>}
            <div className="tipos-tarea-modal-actions">
              <button type="button" className="tipos-tarea-btn-cancel" onClick={handleDeleteCancel} disabled={deleteLoading}>
                Cancelar
              </button>
              <button
                type="button"
                className="tipos-tarea-btn-confirm-delete"
                onClick={handleDeleteConfirm}
                disabled={deleteLoading}
                data-testid="tiposTarea.deleteConfirm"
              >
                {deleteLoading ? 'Eliminando...' : 'Eliminar'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
