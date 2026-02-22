/**
 * Component: ConsultaDetalladaPage
 *
 * Pantalla de consulta detallada de tareas (TR-044).
 * Tabla con filtros por período; filtro por cliente para empleado y supervisor;
 * filtro por empleado solo para supervisor. Sin acciones editar/eliminar;
 * total de horas y paginación.
 */

import React, { useCallback, useEffect, useState } from 'react';
import { getUserData } from '../../../shared/utils/tokenStorage';
import {
  getDetailReport,
  getClients,
  getEmployees,
  DetailReportItem,
  DetailReportParams,
  Client,
  Employee,
} from '../services/task.service';
import { TaskPagination } from './TaskPagination';
import { buildExportFileName, exportDetailToExcel } from '../utils/exportToExcel';
import { t } from '../../../shared/i18n';
import './ConsultaDetalladaPage.css';

const DEFAULT_PER_PAGE = 15;

export interface ConsultaDetalladaFilters {
  fechaDesde: string;
  fechaHasta: string;
  clienteId: number | null;
  usuarioId: number | null;
  ordenarPor: string;
  orden: 'asc' | 'desc';
}

const defaultFilters: ConsultaDetalladaFilters = {
  fechaDesde: '',
  fechaHasta: '',
  clienteId: null,
  usuarioId: null,
  ordenarPor: 'fecha',
  orden: 'desc',
};

function buildParams(
  page: number,
  filters: ConsultaDetalladaFilters,
  isSupervisor: boolean,
  isCliente: boolean
): DetailReportParams {
  const params: DetailReportParams = {
    page,
    per_page: DEFAULT_PER_PAGE,
    ordenar_por: filters.ordenarPor,
    orden: filters.orden,
  };
  if (filters.fechaDesde) params.fecha_desde = filters.fechaDesde;
  if (filters.fechaHasta) params.fecha_hasta = filters.fechaHasta;
  if (!isCliente && filters.clienteId != null) params.cliente_id = filters.clienteId;
  if (isSupervisor && filters.usuarioId != null) params.usuario_id = filters.usuarioId;
  return params;
}

export function ConsultaDetalladaPage(): React.ReactElement {
  const user = getUserData();
  const isSupervisor = user?.esSupervisor ?? false;
  const isCliente = user?.tipoUsuario === 'cliente';

  const [data, setData] = useState<DetailReportItem[]>([]);
  const [pagination, setPagination] = useState({
    current_page: 1,
    per_page: DEFAULT_PER_PAGE,
    total: 0,
    last_page: 1,
  });
  const [totalHoras, setTotalHoras] = useState(0);
  const [filters, setFilters] = useState<ConsultaDetalladaFilters>(defaultFilters);
  const [appliedFilters, setAppliedFilters] = useState<ConsultaDetalladaFilters>(defaultFilters);
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState<string>('');
  const [clients, setClients] = useState<Client[]>([]);
  const [employees, setEmployees] = useState<Employee[]>([]);

  const loadReport = useCallback(
    async (params: DetailReportParams) => {
      setLoading(true);
      setErrorMessage('');
      const result = await getDetailReport(params);
      setLoading(false);
      if (result.success && result.data !== undefined) {
        setData(result.data);
        if (result.pagination) setPagination(result.pagination);
        if (result.totalHoras !== undefined) setTotalHoras(result.totalHoras);
      } else {
        setErrorMessage(
          result.errorMessage || t('report.detail.error.load', 'Error al cargar la consulta')
        );
        setData([]);
      }
    },
    []
  );

  useEffect(() => {
    const params = buildParams(page, appliedFilters, isSupervisor, isCliente);
    loadReport(params);
  }, [page, appliedFilters, isSupervisor, isCliente, loadReport]);

  useEffect(() => {
    if (!isCliente) {
      getClients().then((r) => r.success && r.data && setClients(r.data));
    }
    if (isSupervisor) {
      getEmployees().then((r) => r.success && r.data && setEmployees(r.data));
    }
  }, [isCliente, isSupervisor]);

  const handleApplyFilters = () => {
    setAppliedFilters(filters);
    setPage(1);
  };

  const handlePageChange = (newPage: number) => {
    setPage(newPage);
  };

  const handleSort = (column: string) => {
    const orden = appliedFilters.ordenarPor === column && appliedFilters.orden === 'desc' ? 'asc' : 'desc';
    setAppliedFilters({ ...appliedFilters, ordenarPor: column, orden });
    setPage(1);
  };

  const hasData = data.length > 0;
  const handleExportExcel = () => {
    const filename = buildExportFileName(appliedFilters.fechaDesde, appliedFilters.fechaHasta);
    exportDetailToExcel(data, filename, isSupervisor && !isCliente);
  };

  return (
    <div className="consulta-detallada-container" data-testid="report.detail.container">
      <header className="consulta-detallada-header">
        <h1 className="consulta-detallada-title">
          {t('report.detail.title', 'Consulta Detallada de Tareas')}
        </h1>
      </header>

      <div className="consulta-detallada-filters" data-testid="report.detail.filters" role="search">
        <div className="consulta-detallada-filters-row">
          <label className="consulta-detallada-label">
            {t('tasks.list.filters.fechaDesde', 'Fecha desde')}
            <input
              type="date"
              value={filters.fechaDesde}
              onChange={(e) => setFilters({ ...filters, fechaDesde: e.target.value })}
              disabled={loading}
              aria-label={t('tasks.list.filters.fechaDesde', 'Fecha desde')}
            />
          </label>
          <label className="consulta-detallada-label">
            {t('tasks.list.filters.fechaHasta', 'Fecha hasta')}
            <input
              type="date"
              value={filters.fechaHasta}
              onChange={(e) => setFilters({ ...filters, fechaHasta: e.target.value })}
              disabled={loading}
              aria-label={t('tasks.list.filters.fechaHasta', 'Fecha hasta')}
            />
          </label>
          {!isCliente && (
            <div className="consulta-detallada-label">
              <span className="consulta-detallada-label-text">
                {t('tasks.list.filters.cliente', 'Cliente')}
              </span>
              <select
                value={filters.clienteId ?? ''}
                onChange={(e) =>
                  setFilters({
                    ...filters,
                    clienteId: e.target.value === '' ? null : Number(e.target.value),
                  })
                }
                disabled={loading}
                aria-label={t('tasks.list.filters.cliente', 'Cliente')}
              >
                <option value="">{t('tasks.list.filters.todos', 'Todos')}</option>
                {clients.map((c) => (
                  <option key={c.id} value={c.id}>
                    {c.nombre}
                  </option>
                ))}
              </select>
            </div>
          )}
          {isSupervisor && (
            <div className="consulta-detallada-label">
              <span className="consulta-detallada-label-text">
                {t('tasks.list.filters.empleado', 'Empleado')}
              </span>
              <select
                value={filters.usuarioId ?? ''}
                onChange={(e) =>
                  setFilters({
                    ...filters,
                    usuarioId: e.target.value === '' ? null : Number(e.target.value),
                  })
                }
                disabled={loading}
                aria-label={t('tasks.list.filters.empleado', 'Empleado')}
              >
                <option value="">{t('tasks.list.filters.todos', 'Todos')}</option>
                {employees.map((e) => (
                  <option key={e.id} value={e.id}>
                    {e.nombre}
                  </option>
                ))}
              </select>
            </div>
          )}
          <button
            type="button"
            onClick={handleApplyFilters}
            disabled={loading}
            className="consulta-detallada-btn-apply"
            data-testid="report.detail.applyFilters"
          >
            {t('report.detail.applyFilters', 'Aplicar Filtros')}
          </button>
        </div>
      </div>

      {errorMessage && (
        <div className="consulta-detallada-error" data-testid="report.detail.error" role="alert">
          {errorMessage}
        </div>
      )}

      {!errorMessage && (
        <>
          <div className="consulta-detallada-total-row">
            <div
              className="consulta-detallada-total"
              data-testid="report.detail.totalHours"
              role="status"
            >
              {t('report.detail.totalHoras', 'Total horas del período')}:{' '}
              <strong>{totalHoras.toFixed(2)}</strong>
            </div>
            <div className="consulta-detallada-export" data-testid="report.detail.export">
              {!hasData && !loading && (
                <span className="consulta-detallada-export-no-data" data-testid="exportarExcel.mensajeSinDatos">
                  {t('report.export.noData', 'No hay datos para exportar')}
                </span>
              )}
              <button
                type="button"
                onClick={handleExportExcel}
                disabled={!hasData || loading}
                className="consulta-detallada-btn-export"
                data-testid="exportarExcel.boton"
                aria-label={t('report.export.aria', 'Exportar a Excel')}
              >
                {t('report.export.button', 'Exportar a Excel')}
              </button>
            </div>
          </div>

          {loading ? (
            <div className="consulta-detallada-loading" data-testid="report.detail.loading">
              {t('tasks.list.loading', 'Cargando...')}
            </div>
          ) : data.length === 0 ? (
            <div
              className="consulta-detallada-empty"
              data-testid="report.detail.empty"
              role="status"
            >
              {t('report.detail.empty', 'No se encontraron tareas para los filtros seleccionados.')}
            </div>
          ) : (
            <div className="consulta-detallada-table-wrapper">
              <table
                className="consulta-detallada-table"
                data-testid="report.detail.table"
                role="table"
              >
                <thead>
                  <tr>
                    {isSupervisor && !isCliente && (
                      <th scope="col">
                        <button
                          type="button"
                          className="consulta-detallada-th-sort"
                          onClick={() => handleSort('empleado')}
                        >
                          {t('tasks.list.col.empleado', 'Empleado')}
                        </button>
                      </th>
                    )}
                    <th scope="col">
                      <button
                        type="button"
                        className="consulta-detallada-th-sort"
                        onClick={() => handleSort('cliente')}
                      >
                        {t('tasks.list.col.cliente', 'Cliente')}
                      </button>
                    </th>
                    <th scope="col">
                      <button
                        type="button"
                        className="consulta-detallada-th-sort"
                        onClick={() => handleSort('fecha')}
                      >
                        {t('tasks.list.col.fecha', 'Fecha')}
                      </button>
                    </th>
                    <th scope="col">
                      <button
                        type="button"
                        className="consulta-detallada-th-sort"
                        onClick={() => handleSort('tipo_tarea')}
                      >
                        {t('tasks.list.col.tipoTarea', 'Tipo tarea')}
                      </button>
                    </th>
                    <th scope="col">
                      <button
                        type="button"
                        className="consulta-detallada-th-sort"
                        onClick={() => handleSort('horas')}
                      >
                        {t('report.detail.col.horas', 'Horas')}
                      </button>
                    </th>
                    <th scope="col">{t('tasks.list.col.sinCargo', 'Sin cargo')}</th>
                    <th scope="col">{t('tasks.list.col.presencial', 'Presencial')}</th>
                    <th scope="col">{t('report.detail.col.descripcion', 'Descripción')}</th>
                  </tr>
                </thead>
                <tbody>
                  {data.map((row) => (
                    <tr key={row.id} data-testid={`report.detail.row.${row.id}`}>
                      {isSupervisor && !isCliente && (
                        <td>{row.empleado ? row.empleado.nombre : '—'}</td>
                      )}
                      <td>
                        {row.cliente.nombre}
                        {row.cliente.tipo_cliente ? ` (${row.cliente.tipo_cliente})` : ''}
                      </td>
                      <td>{row.fecha}</td>
                      <td>{row.tipo_tarea.descripcion}</td>
                      <td>{row.horas.toFixed(2)}</td>
                      <td>{row.sin_cargo ? t('tasks.list.si', 'Sí') : t('tasks.list.no', 'No')}</td>
                      <td>{row.presencial ? t('tasks.list.si', 'Sí') : t('tasks.list.no', 'No')}</td>
                      <td>{row.descripcion}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}

          {!loading && data.length > 0 && (
            <div data-testid="report.detail.pagination">
              <TaskPagination
                currentPage={pagination.current_page}
                lastPage={pagination.last_page}
                total={pagination.total}
                perPage={pagination.per_page}
                onPageChange={handlePageChange}
                disabled={loading}
              />
            </div>
          )}
        </>
      )}
    </div>
  );
}
