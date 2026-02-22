/**
 * Component: ClientesPage
 *
 * Listado paginado de clientes (solo supervisores). TR-008(MH).
 * Tabla con búsqueda, filtros (tipo cliente, estado, inhabilitado), paginación y total.
 */

import React, { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  getClientes,
  getTiposCliente,
  deleteCliente,
  ClienteListItem,
  TipoClienteItem,
} from '../services/client.service';
import { TaskPagination } from '../../tasks/components/TaskPagination';
import './ClientesPage.css';

const DEFAULT_PAGE_SIZE = 15;

export interface ClientesFilters {
  search: string;
  tipoClienteId: number | null;
  activo: '' | 'true' | 'false';
  inhabilitado: '' | 'true' | 'false';
}

const defaultFilters: ClientesFilters = {
  search: '',
  tipoClienteId: null,
  activo: '',
  inhabilitado: '',
};

function buildParams(
  page: number,
  filters: ClientesFilters,
  pageSize: number
): Parameters<typeof getClientes>[0] {
  const params: Parameters<typeof getClientes>[0] = {
    page,
    page_size: pageSize,
    sort: 'nombre',
    sort_dir: 'asc',
  };
  if (filters.search.trim()) params.search = filters.search.trim();
  if (filters.tipoClienteId != null) params.tipo_cliente_id = filters.tipoClienteId;
  if (filters.activo === 'true') params.activo = true;
  if (filters.activo === 'false') params.activo = false;
  if (filters.inhabilitado === 'true') params.inhabilitado = true;
  if (filters.inhabilitado === 'false') params.inhabilitado = false;
  return params;
}

export function ClientesPage(): React.ReactElement {
  const navigate = useNavigate();
  const [data, setData] = useState<ClienteListItem[]>([]);
  const [tiposCliente, setTiposCliente] = useState<TipoClienteItem[]>([]);
  const [pagination, setPagination] = useState({
    current_page: 1,
    per_page: DEFAULT_PAGE_SIZE,
    total: 0,
    last_page: 1,
  });
  const [filters, setFilters] = useState<ClientesFilters>(defaultFilters);
  const [appliedFilters, setAppliedFilters] = useState<ClientesFilters>(defaultFilters);
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState<string>('');
  const [clienteToDelete, setClienteToDelete] = useState<ClienteListItem | null>(null);
  const [deleteLoading, setDeleteLoading] = useState(false);
  const [deleteError, setDeleteError] = useState('');
  const [successMessage, setSuccessMessage] = useState('');

  const loadClientes = useCallback(
    async (params: Parameters<typeof getClientes>[0]) => {
      setLoading(true);
      setErrorMessage('');
      const result = await getClientes(params);
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
        setErrorMessage(result.errorMessage || 'Error al cargar clientes');
        setData([]);
      }
    },
    []
  );

  const loadTiposCliente = useCallback(async () => {
    const result = await getTiposCliente();
    if (result.success && result.data) setTiposCliente(result.data);
  }, []);

  useEffect(() => {
    loadTiposCliente();
  }, [loadTiposCliente]);

  useEffect(() => {
    const params = buildParams(page, appliedFilters, DEFAULT_PAGE_SIZE);
    loadClientes(params);
  }, [page, appliedFilters, loadClientes]);

  const handleApplyFilters = () => {
    setAppliedFilters(filters);
    setPage(1);
  };

  const handlePageChange = (newPage: number) => {
    setPage(Math.max(1, Math.min(newPage, pagination.last_page)));
  };

  const handleDeleteClick = (cliente: ClienteListItem) => {
    setClienteToDelete(cliente);
    setDeleteError('');
  };

  const handleDeleteCancel = () => {
    setClienteToDelete(null);
    setDeleteError('');
  };

  const handleDeleteConfirm = async () => {
    if (!clienteToDelete) return;
    setDeleteLoading(true);
    setDeleteError('');
    const result = await deleteCliente(clienteToDelete.id);
    setDeleteLoading(false);
    if (result.success) {
      setClienteToDelete(null);
      setSuccessMessage('Cliente eliminado correctamente.');
      const params = buildParams(page, appliedFilters, DEFAULT_PAGE_SIZE);
      await loadClientes(params);
      setTimeout(() => setSuccessMessage(''), 3000);
    } else {
      setDeleteError(result.errorMessage || 'Error al eliminar cliente');
    }
  };

  return (
    <div className="clientes-page" data-testid="clientes.list">
      <header className="clientes-page-header">
        <h1 className="clientes-page-title">Clientes</h1>
        <button
          type="button"
          className="clientes-page-btn-create"
          onClick={() => navigate('/clientes/nueva')}
          data-testid="clientes.create"
          aria-label="Crear cliente"
        >
          Crear cliente
        </button>
      </header>

      <section className="clientes-page-filters" data-testid="clientes.filters">
        <div className="clientes-filters-row">
          <label className="clientes-filter-label">
            <span className="clientes-filter-label-text">Búsqueda (código o nombre)</span>
            <input
              type="text"
              value={filters.search}
              onChange={(e) => setFilters((f) => ({ ...f, search: e.target.value }))}
              placeholder="Buscar..."
              className="clientes-filter-input"
              data-testid="clientes.search"
              aria-label="Búsqueda por código o nombre"
            />
          </label>
          <label className="clientes-filter-label">
            <span className="clientes-filter-label-text">Tipo de cliente</span>
            <select
              value={filters.tipoClienteId ?? ''}
              onChange={(e) =>
                setFilters((f) => ({
                  ...f,
                  tipoClienteId: e.target.value === '' ? null : Number(e.target.value),
                }))
              }
              className="clientes-filter-select"
              data-testid="clientes.filter.tipoCliente"
              aria-label="Filtrar por tipo de cliente"
            >
              <option value="">Todos</option>
              {tiposCliente.map((t) => (
                <option key={t.id} value={t.id}>
                  {t.descripcion}
                </option>
              ))}
            </select>
          </label>
          <label className="clientes-filter-label">
            <span className="clientes-filter-label-text">Estado</span>
            <select
              value={filters.activo}
              onChange={(e) =>
                setFilters((f) => ({
                  ...f,
                  activo: e.target.value as '' | 'true' | 'false',
                }))
              }
              className="clientes-filter-select"
              data-testid="clientes.filter.activo"
              aria-label="Filtrar por estado activo"
            >
              <option value="">Todos</option>
              <option value="true">Activo</option>
              <option value="false">Inactivo</option>
            </select>
          </label>
          <label className="clientes-filter-label">
            <span className="clientes-filter-label-text">Inhabilitado</span>
            <select
              value={filters.inhabilitado}
              onChange={(e) =>
                setFilters((f) => ({
                  ...f,
                  inhabilitado: e.target.value as '' | 'true' | 'false',
                }))
              }
              className="clientes-filter-select"
              data-testid="clientes.filter.inhabilitado"
              aria-label="Filtrar por inhabilitado"
            >
              <option value="">Todos</option>
              <option value="false">No</option>
              <option value="true">Sí</option>
            </select>
          </label>
          <button
            type="button"
            className="clientes-filter-btn-apply"
            onClick={handleApplyFilters}
            data-testid="clientes.filters.apply"
            aria-label="Aplicar filtros"
          >
            Aplicar
          </button>
        </div>
      </section>

      {loading && (
        <div className="clientes-page-loading" data-testid="clientes.loading" role="status">
          Cargando clientes...
        </div>
      )}

      {errorMessage && (
        <div className="clientes-page-error" data-testid="clientes.error" role="alert">
          {errorMessage}
        </div>
      )}

      {successMessage && (
        <div className="clientes-page-success" data-testid="clientes.success" role="status">
          {successMessage}
        </div>
      )}

      {!loading && !errorMessage && (
        <>
          <p className="clientes-page-total" data-testid="clientes.total">
            Total de clientes: <strong>{pagination.total}</strong>
          </p>

          {data.length === 0 ? (
            <p className="clientes-page-empty" data-testid="clientes.empty" role="status">
              No se encontraron clientes.
            </p>
          ) : (
            <div className="clientes-table-wrapper">
              <table className="clientes-table" data-testid="clientes.table" aria-label="Listado de clientes">
                <thead>
                  <tr>
                    <th scope="col">Código</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Tipo de cliente</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Inhabilitado</th>
                    <th scope="col">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {data.map((c) => (
                    <tr
                      key={c.id}
                      className={c.inhabilitado ? 'clientes-table-row-inhabilitado' : ''}
                      data-testid={`clientes.row.${c.id}`}
                    >
                      <td>{c.code}</td>
                      <td>{c.nombre}</td>
                      <td>{c.tipo_cliente?.descripcion ?? '—'}</td>
                      <td>{c.activo ? 'Activo' : 'Inactivo'}</td>
                      <td>{c.inhabilitado ? 'Sí' : 'No'}</td>
                      <td>
                        <button
                          type="button"
                          className="clientes-page-btn-ver"
                          onClick={() => navigate(`/clientes/${c.id}`)}
                          data-testid={`clientes.ver.${c.id}`}
                          aria-label={`Ver detalle de ${c.nombre}`}
                        >
                          Ver
                        </button>
                        <button
                          type="button"
                          className="clientes-page-btn-edit"
                          onClick={() => navigate(`/clientes/${c.id}/editar`)}
                          data-testid={`clientes.edit.${c.id}`}
                          aria-label={`Editar cliente ${c.nombre}`}
                        >
                          Editar
                        </button>
                        <button
                          type="button"
                          className="clientes-page-btn-delete"
                          onClick={() => handleDeleteClick(c)}
                          data-testid={`clientes.row.${c.id}.delete`}
                          aria-label={`Eliminar cliente ${c.nombre}`}
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

      {clienteToDelete && (
        <div
          className="clientes-delete-modal-overlay"
          role="dialog"
          aria-modal="true"
          aria-labelledby="clientes-delete-modal-title"
          data-testid="clientes.delete.modal"
        >
          <div className="clientes-delete-modal">
            <h2 id="clientes-delete-modal-title" className="clientes-delete-modal-title">
              Eliminar cliente
            </h2>
            <p className="clientes-delete-modal-text">
              ¿Está seguro de eliminar el cliente <strong data-testid="clientes.delete.code">{clienteToDelete.code}</strong> – <strong data-testid="clientes.delete.nombre">{clienteToDelete.nombre}</strong>?
            </p>
            {deleteError && (
              <div className="clientes-delete-modal-error" role="alert" data-testid="clientes.delete.error">
                {deleteError}
              </div>
            )}
            <div className="clientes-delete-modal-actions">
              <button
                type="button"
                className="clientes-delete-modal-btn-cancel"
                onClick={handleDeleteCancel}
                disabled={deleteLoading}
                data-testid="clientes.delete.cancel"
              >
                Cancelar
              </button>
              <button
                type="button"
                className="clientes-page-btn-delete"
                onClick={handleDeleteConfirm}
                disabled={deleteLoading}
                data-testid="clientes.delete.confirm"
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

export default ClientesPage;
