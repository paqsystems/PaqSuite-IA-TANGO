/**
 * Component: TareasPorClientePage
 *
 * Pantalla de consulta agrupada por cliente (TR-046).
 * Filtros por período; grupos por cliente con total horas y cantidad;
 * accordion expandible con detalle de tareas; total general.
 */

import React, { useCallback, useEffect, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import {
  getReportByClient,
  ByClientGroup,
  ByClientReportParams,
} from '../services/task.service';
import { t } from '../../../shared/i18n';
import { getUserData } from '../../../shared/utils/tokenStorage';
import { buildExportFileName, exportGroupedToExcel, type GroupedExportGroup } from '../utils/exportToExcel';
import './TareasPorClientePage.css';

export interface TareasPorClienteFilters {
  fechaDesde: string;
  fechaHasta: string;
}

const defaultFilters: TareasPorClienteFilters = {
  fechaDesde: '',
  fechaHasta: '',
};

function getInitialFiltersFromSearch(searchParams: URLSearchParams): TareasPorClienteFilters {
  const fechaDesde = searchParams.get('fecha_desde') ?? '';
  const fechaHasta = searchParams.get('fecha_hasta') ?? '';
  return { fechaDesde, fechaHasta };
}

function buildParams(filters: TareasPorClienteFilters): ByClientReportParams {
  const params: ByClientReportParams = {};
  if (filters.fechaDesde) params.fecha_desde = filters.fechaDesde;
  if (filters.fechaHasta) params.fecha_hasta = filters.fechaHasta;
  return params;
}

export function TareasPorClientePage(): React.ReactElement {
  const [searchParams] = useSearchParams();
  const user = getUserData();
  const isSupervisor = user?.esSupervisor ?? false;

  const initialFilters = getInitialFiltersFromSearch(searchParams);
  const clienteIdFromUrl = searchParams.get('cliente_id');
  const initialExpandedClienteId =
    clienteIdFromUrl != null && clienteIdFromUrl !== ''
      ? parseInt(clienteIdFromUrl, 10)
      : null;

  const [grupos, setGrupos] = useState<ByClientGroup[]>([]);
  const [totalGeneralHoras, setTotalGeneralHoras] = useState(0);
  const [totalGeneralTareas, setTotalGeneralTareas] = useState(0);
  const [filters, setFilters] = useState<TareasPorClienteFilters>(
    initialFilters.fechaDesde || initialFilters.fechaHasta ? initialFilters : defaultFilters
  );
  const [appliedFilters, setAppliedFilters] = useState<TareasPorClienteFilters>(
    initialFilters.fechaDesde || initialFilters.fechaHasta ? initialFilters : defaultFilters
  );
  const [loading, setLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState<string>('');
  const [expandedClienteId, setExpandedClienteId] = useState<number | null>(
    Number.isNaN(initialExpandedClienteId as number) ? null : initialExpandedClienteId
  );

  const loadReport = useCallback(async (params: ByClientReportParams) => {
    setLoading(true);
    setErrorMessage('');
    const result = await getReportByClient(params);
    setLoading(false);
    if (result.success && result.grupos !== undefined) {
      setGrupos(result.grupos);
      setTotalGeneralHoras(result.totalGeneralHoras ?? 0);
      setTotalGeneralTareas(result.totalGeneralTareas ?? 0);
    } else {
      setErrorMessage(
        result.errorMessage || t('report.byClient.error.load', 'Error al cargar el reporte')
      );
      setGrupos([]);
      setTotalGeneralHoras(0);
      setTotalGeneralTareas(0);
    }
  }, []);

  useEffect(() => {
    const params = buildParams(appliedFilters);
    loadReport(params);
  }, [appliedFilters, loadReport]);

  const handleApplyFilters = () => {
    setAppliedFilters(filters);
  };

  const toggleGroup = (clienteId: number) => {
    setExpandedClienteId((prev) => (prev === clienteId ? null : clienteId));
  };

  const hasData = grupos.length > 0;
  const handleExportExcel = () => {
    const exportGroups: GroupedExportGroup[] = grupos.map((g) => ({
      groupTitle: g.nombre,
      totalHoras: g.total_horas,
      cantidadTareas: g.cantidad_tareas,
      tareas: g.tareas.map((t) => ({
        fecha: t.fecha,
        cliente: g.nombre,
        tipoTarea: t.tipo_tarea.descripcion,
        horas: t.horas,
        sinCargo: false,
        presencial: false,
        descripcion: t.descripcion ?? '',
      })),
    }));
    const filename = buildExportFileName(appliedFilters.fechaDesde, appliedFilters.fechaHasta, 'por-cliente');
    exportGroupedToExcel(exportGroups, filename);
  };

  return (
    <div className="tareas-por-cliente-container" data-testid="report.byClient.container">
      <header className="tareas-por-cliente-header">
        <h1 className="tareas-por-cliente-title">
          {t('report.byClient.title', 'Tareas por Cliente')}
        </h1>
      </header>

      <div
        className="tareas-por-cliente-filters"
        data-testid="report.byClient.filters"
        role="search"
      >
        <div className="tareas-por-cliente-filters-row">
          <label className="tareas-por-cliente-label">
            {t('tasks.list.filters.fechaDesde', 'Fecha desde')}
            <input
              type="date"
              value={filters.fechaDesde}
              onChange={(e) => setFilters({ ...filters, fechaDesde: e.target.value })}
              disabled={loading}
              aria-label={t('tasks.list.filters.fechaDesde', 'Fecha desde')}
            />
          </label>
          <label className="tareas-por-cliente-label">
            {t('tasks.list.filters.fechaHasta', 'Fecha hasta')}
            <input
              type="date"
              value={filters.fechaHasta}
              onChange={(e) => setFilters({ ...filters, fechaHasta: e.target.value })}
              disabled={loading}
              aria-label={t('tasks.list.filters.fechaHasta', 'Fecha hasta')}
            />
          </label>
          <button
            type="button"
            onClick={handleApplyFilters}
            disabled={loading}
            className="tareas-por-cliente-btn-apply"
            data-testid="report.byClient.applyFilters"
          >
            {t('report.detail.applyFilters', 'Aplicar Filtros')}
          </button>
        </div>
      </div>

      {errorMessage && (
        <div className="tareas-por-cliente-error" data-testid="report.byClient.error" role="alert">
          {errorMessage}
        </div>
      )}

      {!errorMessage && (
        <>
          <div className="tareas-por-cliente-total-row">
            <div
              className="tareas-por-cliente-total"
              data-testid="report.byClient.totalGeneral"
              role="status"
            >
              {t('report.byClient.totalHoras', 'Total horas')}:{' '}
              <strong>{totalGeneralHoras.toFixed(2)}</strong>
              {' · '}
              {t('report.byClient.totalTareas', 'Total tareas')}:{' '}
              <strong>{totalGeneralTareas}</strong>
            </div>
            <div className="tareas-por-cliente-export">
              {!hasData && !loading && (
                <span className="tareas-por-cliente-export-no-data" data-testid="exportarExcel.mensajeSinDatos">
                  {t('report.export.noData', 'No hay datos para exportar')}
                </span>
              )}
              <button
                type="button"
                onClick={handleExportExcel}
                disabled={!hasData || loading}
                className="tareas-por-cliente-btn-export"
                data-testid="exportarExcel.boton"
                aria-label={t('report.export.aria', 'Exportar a Excel')}
              >
                {t('report.export.button', 'Exportar a Excel')}
              </button>
            </div>
          </div>

          {loading ? (
            <div className="tareas-por-cliente-loading" data-testid="report.byClient.loading">
              {t('tasks.list.loading', 'Cargando...')}
            </div>
          ) : grupos.length === 0 ? (
            <div
              className="tareas-por-cliente-empty"
              data-testid="report.byClient.empty"
              role="status"
            >
              {t('report.detail.empty', 'No se encontraron tareas para los filtros seleccionados.')}
            </div>
          ) : (
            <div
              className="tareas-por-cliente-groups"
              data-testid="report.byClient.groups"
              role="list"
            >
              {grupos.map((grupo) => {
                const isExpanded = expandedClienteId === grupo.cliente_id;
                return (
                  <div
                    key={grupo.cliente_id}
                    className="tareas-por-cliente-group"
                    data-testid={`report.byClient.group.${grupo.cliente_id}`}
                  >
                    <button
                      type="button"
                      className="tareas-por-cliente-group-header"
                      onClick={() => toggleGroup(grupo.cliente_id)}
                      aria-expanded={isExpanded}
                      aria-controls={`report.byClient.detail.${grupo.cliente_id}`}
                      id={`report.byClient.header.${grupo.cliente_id}`}
                    >
                      <span className="tareas-por-cliente-group-title">
                        {grupo.nombre}
                        {grupo.tipo_cliente ? ` (${grupo.tipo_cliente.descripcion})` : ''}
                      </span>
                      <span className="tareas-por-cliente-group-meta">
                        {grupo.total_horas.toFixed(2)} h · {grupo.cantidad_tareas}{' '}
                        {t('report.byClient.tareas', 'tareas')}
                      </span>
                      <span className="tareas-por-cliente-group-chevron" aria-hidden>
                        {isExpanded ? '▼' : '▶'}
                      </span>
                    </button>
                    {isExpanded && (
                      <div
                        id={`report.byClient.detail.${grupo.cliente_id}`}
                        className="tareas-por-cliente-group-detail"
                        role="region"
                        aria-labelledby={`report.byClient.header.${grupo.cliente_id}`}
                      >
                        <table
                          className="tareas-por-cliente-detail-table"
                          data-testid={`report.byClient.table.${grupo.cliente_id}`}
                          role="table"
                        >
                          <thead>
                            <tr>
                              <th scope="col">
                                {t('tasks.list.col.fecha', 'Fecha')}
                              </th>
                              <th scope="col">
                                {t('tasks.list.col.tipoTarea', 'Tipo tarea')}
                              </th>
                              <th scope="col">
                                {t('report.detail.col.horas', 'Horas')}
                              </th>
                              {isSupervisor && (
                                <th scope="col">
                                  {t('tasks.list.col.empleado', 'Empleado')}
                                </th>
                              )}
                              <th scope="col">
                                {t('report.detail.col.descripcion', 'Descripción')}
                              </th>
                            </tr>
                          </thead>
                          <tbody>
                            {grupo.tareas.map((tarea) => (
                              <tr
                                key={tarea.id}
                                data-testid={`report.byClient.row.${tarea.id}`}
                              >
                                <td>{tarea.fecha}</td>
                                <td>{tarea.tipo_tarea.descripcion}</td>
                                <td>{tarea.horas.toFixed(2)}</td>
                                {isSupervisor && (
                                  <td>
                                    {tarea.empleado ? tarea.empleado.nombre : '—'}
                                  </td>
                                )}
                                <td>{tarea.descripcion}</td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          )}
        </>
      )}
    </div>
  );
}
