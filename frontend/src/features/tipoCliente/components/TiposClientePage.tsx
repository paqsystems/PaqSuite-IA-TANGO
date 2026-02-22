/**
 * Component: TiposClientePage
 *
 * Listado paginado de tipos de cliente (solo supervisores). TR-014(MH).
 * Tabla con búsqueda, filtros (activo, inhabilitado), paginación, total.
 * Acciones: Crear, Editar, Eliminar (TR-015, TR-016, TR-017).
 *
 * @see TR-014(MH)-listado-de-tipos-de-cliente.md
 */

import React, { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  getTiposClienteList,
  deleteTipoCliente,
  TipoClienteListItem,
  ERROR_TIENE_CLIENTES,
} from '../services/tipoCliente.service';
import { TaskPagination } from '../../tasks/components/TaskPagination';
import './TiposClientePage.css';

const DEFAULT_PAGE_SIZE = 15;

export interface TiposClienteFilters {
  search: string;
  activo: '' | 'true' | 'false';
  inhabilitado: '' | 'true' | 'false';
}

const defaultFilters: TiposClienteFilters = {
  search: '',
  activo: '',
  inhabilitado: '',
};

function buildParams(
  page: number,
  filters: TiposClienteFilters,
  pageSize: number
): Parameters<typeof getTiposClienteList>[0] {
  const params: Parameters<typeof getTiposClienteList>[0] = {
    page,
    page_size: pageSize,
    sort: 'descripcion',
    sort_dir: 'asc',
  };
  if (filters.search.trim()) params.search = filters.search.trim();
  if (filters.activo === 'true') params.activo = true;
  if (filters.activo === 'false') params.activo = false;
  if (filters.inhabilitado === 'true') params.inhabilitado = true;
  if (filters.inhabilitado === 'false') params.inhabilitado = false;
  return params;
}

export function TiposClientePage(): React.ReactElement {
  const navigate = useNavigate();
  const [data, setData] = useState<TipoClienteListItem[]>([]);
  const [pagination, setPagination] = useState({
    current_page: 1,
    per_page: DEFAULT_PAGE_SIZE,
    total: 0,
    last_page: 1,
  });
  const [filters, setFilters] = useState<TiposClienteFilters>(defaultFilters);
  const [appliedFilters, setAppliedFilters] = useState<TiposClienteFilters>(defaultFilters);
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState<string>('');
  const [tipoToDelete, setTipoToDelete] = useState<TipoClienteListItem | null>(null);
  const [deleteLoading, setDeleteLoading] = useState(false);
  const [deleteError, setDeleteError] = useState('');
  const [successMessage, setSuccessMessage] = useState('');

  const loadTipos = useCallback(async (params: Parameters<typeof getTiposClienteList>[0]) => {
    setLoading(true);
    setErrorMessage('');
    const result = await getTiposClienteList(params);
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
      setErrorMessage(result.errorMessage || 'Error al cargar tipos de cliente');
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

  const handleDeleteClick = (t: TipoClienteListItem) => {
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
    const result = await deleteTipoCliente(tipoToDelete.id);
    setDeleteLoading(false);
    if (result.success) {
      setTipoToDelete(null);
      setSuccessMessage('Tipo de cliente eliminado correctamente.');
      loadTipos(buildParams(page, appliedFilters, DEFAULT_PAGE_SIZE));
      setTimeout(() => setSuccessMessage(''), 3000);
    } else {
      setDeleteError(
        result.errorCode === ERROR_TIENE_CLIENTES
          ? 'No se puede eliminar el tipo de cliente porque tiene clientes asociados.'
          : (result.errorMessage ?? 'Error al eliminar')
      );
    }
  };

  return (
    <div className="tipos-cliente-page" data-testid="tiposCliente.list">
      <header className="tipos-cliente-page-header">
        <h1 className="tipos-cliente-page-title">Tipos de Cliente</h1>
        <button
          type="button"
          className="tipos-cliente-page-btn-create"
          onClick={() => navigate('/tipos-cliente/nuevo')}
          data-testid="tiposCliente.crear"
          aria-label="Crear tipo de cliente"
        >
          Crear tipo de cliente
        </button>
      </header>

      <section className="tipos-cliente-page-filters" data-testid="tiposCliente.filters">
        <div className="tipos-cliente-filters-row">
          <label className="tipos-cliente-filter-label">
            <span className="tipos-cliente-filter-label-text">Búsqueda (código o descripción)</span>
            <input
              type="text"
              value={filters.search}
              onChange={(e) => setFilters((f) => ({ ...f, search: e.target.value }))}
              placeholder="Buscar..."
              className="tipos-cliente-filter-input"
              data-testid="tiposCliente.busqueda"
              aria-label="Búsqueda"
            />
          </label>
          <label className="tipos-cliente-filter-label">
            <span className="tipos-cliente-filter-label-text">Estado</span>
            <select
              value={filters.activo}
              onChange={(e) => setFilters((f) => ({ ...f, activo: e.target.value as '' | 'true' | 'false' }))}
              className="tipos-cliente-filter-select"
              data-testid="tiposCliente.filtroActivo"
              aria-label="Filtrar por activo"
            >
              <option value="">Todos</option>
              <option value="true">Activo</option>
              <option value="false">Inactivo</option>
            </select>
          </label>
          <label className="tipos-cliente-filter-label">
            <span className="tipos-cliente-filter-label-text">Inhabilitado</span>
            <select
              value={filters.inhabilitado}
              onChange={(e) => setFilters((f) => ({ ...f, inhabilitado: e.target.value as '' | 'true' | 'false' }))}
              className="tipos-cliente-filter-select"
              data-testid="tiposCliente.filtroInhabilitado"
              aria-label="Filtrar por inhabilitado"
            >
              <option value="">Todos</option>
              <option value="false">No</option>
              <option value="true">Sí</option>
            </select>
          </label>
          <button
            type="button"
            className="tipos-cliente-filter-btn-apply"
            onClick={handleApplyFilters}
            data-testid="tiposCliente.filters.apply"
            aria-label="Aplicar filtros"
          >
            Aplicar
          </button>
        </div>
      </section>

      {loading && (
        <div className="tipos-cliente-page-loading" data-testid="tiposCliente.loading" role="status">
          Cargando tipos de cliente...
        </div>
      )}

      {errorMessage && (
        <div className="tipos-cliente-page-error" data-testid="tiposCliente.error" role="alert">
          {errorMessage}
        </div>
      )}

      {successMessage && (
        <div className="tipos-cliente-page-success" data-testid="tiposCliente.success" role="status">
          {successMessage}
        </div>
      )}

      {!loading && !errorMessage && (
        <>
          <p className="tipos-cliente-page-total" data-testid="tiposCliente.total">
            Total de tipos de cliente: <strong>{pagination.total}</strong>
          </p>

          {data.length === 0 ? (
            <p className="tipos-cliente-page-empty" data-testid="tiposCliente.empty" role="status">
              No se encontraron tipos de cliente.
            </p>
          ) : (
            <div className="tipos-cliente-table-wrapper">
              <table className="tipos-cliente-table" data-testid="tiposCliente.tabla" aria-label="Listado de tipos de cliente">
                <thead>
                  <tr>
                    <th scope="col">Código</th>
                    <th scope="col">Descripción</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Inhabilitado</th>
                    <th scope="col">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {data.map((t) => (
                    <tr
                      key={t.id}
                      className={t.inhabilitado ? 'tipos-cliente-table-row-inhabilitado' : ''}
                      data-testid={`tiposCliente.row.${t.id}`}
                    >
                      <td>{t.code}</td>
                      <td>{t.descripcion}</td>
                      <td>{t.activo ? 'Activo' : 'Inactivo'}</td>
                      <td>{t.inhabilitado ? 'Sí' : 'No'}</td>
                      <td>
                        <button
                          type="button"
                          className="tipos-cliente-page-btn-edit"
                          onClick={() => navigate(`/tipos-cliente/${t.id}/editar`)}
                          data-testid={`tiposCliente.editar.${t.id}`}
                          aria-label={`Editar tipo ${t.code}`}
                        >
                          Editar
                        </button>
                        <button
                          type="button"
                          className="tipos-cliente-page-btn-delete"
                          onClick={() => handleDeleteClick(t)}
                          data-testid={`tiposCliente.eliminar.${t.id}`}
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
              testIdPrefix="tiposCliente"
            />
          )}
        </>
      )}

      {tipoToDelete && (
        <div className="tipos-cliente-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="tipos-cliente-delete-title" data-testid="tiposCliente.delete.modal">
          <div className="tipos-cliente-modal">
            <h2 id="tipos-cliente-delete-title">Confirmar eliminación</h2>
            <p>
              ¿Eliminar el tipo de cliente <strong>{tipoToDelete.code}</strong> – {tipoToDelete.descripcion}?
            </p>
            {deleteError && <p className="tipos-cliente-modal-error" role="alert">{deleteError}</p>}
            <div className="tipos-cliente-modal-actions">
              <button type="button" className="tipos-cliente-btn-cancel" onClick={handleDeleteCancel} disabled={deleteLoading}>
                Cancelar
              </button>
              <button
                type="button"
                className="tipos-cliente-btn-confirm-delete"
                onClick={handleDeleteConfirm}
                disabled={deleteLoading}
                data-testid="tiposCliente.deleteConfirm"
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
