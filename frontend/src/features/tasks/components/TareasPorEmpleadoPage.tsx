/**
 * Component: TareasPorEmpleadoPage
 *
 * Pantalla de consulta agrupada por empleado (TR-045). Solo supervisores.
 * Filtros: período, cliente, empleado; grupos por empleado con total horas y cantidad;
 * accordion expandible con detalle de tareas (mismas columnas que consulta detallada); total general.
 */

import React, { useCallback, useEffect, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import {
  getReportByEmployee,
  ByEmployeeGroup,
  ByEmployeeReportParams,
} from '../services/task.service';
import { ClientSelector } from './ClientSelector';
import { EmployeeSelector } from './EmployeeSelector';
import { t } from '../../../shared/i18n';
import { buildExportFileName, exportGroupedToExcel, type GroupedExportGroup } from '../utils/exportToExcel';
import './TareasPorEmpleadoPage.css';

export interface TareasPorEmpleadoFilters {
  fechaDesde: string;
  fechaHasta: string;
  clienteId: number | null;
  usuarioId: number | null;
}

const defaultFilters: TareasPorEmpleadoFilters = {
  fechaDesde: '',
  fechaHasta: '',
  clienteId: null,
  usuarioId: null,
};

function buildParams(filters: TareasPorEmpleadoFilters): ByEmployeeReportParams {
  const params: ByEmployeeReportParams = {};
  if (filters.fechaDesde) params.fecha_desde = filters.fechaDesde;
  if (filters.fechaHasta) params.fecha_hasta = filters.fechaHasta;
  if (filters.clienteId != null) params.cliente_id = filters.clienteId;
  if (filters.usuarioId != null) params.usuario_id = filters.usuarioId;
  return params;
}

export function TareasPorEmpleadoPage(): React.ReactElement {
  const [searchParams] = useSearchParams();
  const usuarioIdFromUrl = searchParams.get('usuario_id');
  const fechaDesdeFromUrl = searchParams.get('fecha_desde');
  const fechaHastaFromUrl = searchParams.get('fecha_hasta');

  const parsedUsuarioId =
    usuarioIdFromUrl != null ? parseInt(usuarioIdFromUrl, 10) : null;
  const validInitialFilters: TareasPorEmpleadoFilters = {
    fechaDesde: fechaDesdeFromUrl ?? defaultFilters.fechaDesde,
    fechaHasta: fechaHastaFromUrl ?? defaultFilters.fechaHasta,
    clienteId: defaultFilters.clienteId,
    usuarioId: parsedUsuarioId != null && !Number.isNaN(parsedUsuarioId) ? parsedUsuarioId : null,
  };

  const [grupos, setGrupos] = useState<ByEmployeeGroup[]>([]);
  const [totalGeneralHoras, setTotalGeneralHoras] = useState(0);
  const [totalGeneralTareas, setTotalGeneralTareas] = useState(0);
  const [filters, setFilters] = useState<TareasPorEmpleadoFilters>(validInitialFilters);
  const [appliedFilters, setAppliedFilters] = useState<TareasPorEmpleadoFilters>(validInitialFilters);
  const [loading, setLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState<string>('');
  const [expandedUsuarioId, setExpandedUsuarioId] = useState<number | null>(null);

  const loadReport = useCallback(async (params: ByEmployeeReportParams) => {
    setLoading(true);
    setErrorMessage('');
    const result = await getReportByEmployee(params);
    setLoading(false);
    if (result.success && result.grupos !== undefined) {
      setGrupos(result.grupos);
      setTotalGeneralHoras(result.totalGeneralHoras ?? 0);
      setTotalGeneralTareas(result.totalGeneralTareas ?? 0);
    } else {
      setErrorMessage(
        result.errorMessage || t('report.byEmployee.error.load', 'Error al cargar el reporte')
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

  const toggleGroup = (usuarioId: number) => {
    setExpandedUsuarioId((prev) => (prev === usuarioId ? null : usuarioId));
  };

  const hasData = grupos.length > 0;
  const handleExportExcel = () => {
    const exportGroups: GroupedExportGroup[] = grupos.map((g) => ({
      groupTitle: `${g.nombre}${g.code ? ` (${g.code})` : ''}`,
      totalHoras: g.total_horas,
      cantidadTareas: g.cantidad_tareas,
      tareas: g.tareas.map((t) => ({
        fecha: t.fecha,
        cliente: t.cliente.nombre,
        tipoTarea: t.tipo_tarea.descripcion,
        horas: t.horas,
        sinCargo: t.sin_cargo,
        presencial: t.presencial,
        descripcion: t.descripcion ?? '',
      })),
    }));
    const filename = buildExportFileName(appliedFilters.fechaDesde, appliedFilters.fechaHasta, 'por-empleado');
    exportGroupedToExcel(exportGroups, filename);
  };

  return (
    <div className="tareas-por-empleado-container" data-testid="tareasPorEmpleado.page">
      <header className="tareas-por-empleado-header">
        <h1 className="tareas-por-empleado-title">
          {t('report.byEmployee.title', 'Tareas por Empleado')}
        </h1>
      </header>

      <div
        className="tareas-por-empleado-filters"
        data-testid="tareasPorEmpleado.filtros"
        role="search"
      >
        <div className="tareas-por-empleado-filters-row">
          <label className="tareas-por-empleado-label">
            {t('tasks.list.filters.fechaDesde', 'Fecha desde')}
            <input
              type="date"
              value={filters.fechaDesde}
              onChange={(e) => setFilters({ ...filters, fechaDesde: e.target.value })}
              disabled={loading}
              data-testid="tareasPorEmpleado.filtroFechaDesde"
              aria-label={t('tasks.list.filters.fechaDesde', 'Fecha desde')}
            />
          </label>
          <label className="tareas-por-empleado-label">
            {t('tasks.list.filters.fechaHasta', 'Fecha hasta')}
            <input
              type="date"
              value={filters.fechaHasta}
              onChange={(e) => setFilters({ ...filters, fechaHasta: e.target.value })}
              disabled={loading}
              data-testid="tareasPorEmpleado.filtroFechaHasta"
              aria-label={t('tasks.list.filters.fechaHasta', 'Fecha hasta')}
            />
          </label>
          <div className="tareas-por-empleado-label tareas-por-empleado-cliente">
            <span className="tareas-por-empleado-label-text">{t('tasks.list.filters.cliente', 'Cliente')}</span>
            <ClientSelector
              value={filters.clienteId}
              onChange={(clienteId) => setFilters((f) => ({ ...f, clienteId }))}
              disabled={loading}
              showLabel={false}
              allowAll={true}
            />
          </div>
          <div className="tareas-por-empleado-label tareas-por-empleado-empleado">
            <span className="tareas-por-empleado-label-text">{t('tasks.list.filters.empleado', 'Empleado')}</span>
            <EmployeeSelector
              value={filters.usuarioId}
              onChange={(usuarioId) => setFilters((f) => ({ ...f, usuarioId }))}
              disabled={loading}
              showLabel={false}
              allowAll={true}
            />
          </div>
          <button
            type="button"
            onClick={handleApplyFilters}
            disabled={loading}
            className="tareas-por-empleado-btn-apply"
            data-testid="tareasPorEmpleado.aplicarFiltros"
          >
            {t('report.detail.applyFilters', 'Aplicar Filtros')}
          </button>
        </div>
      </div>

      {errorMessage && (
        <div className="tareas-por-empleado-error" data-testid="tareasPorEmpleado.mensajeError" role="alert">
          {errorMessage}
        </div>
      )}

      {!errorMessage && (
        <>
          <div className="tareas-por-empleado-total-row">
            <div
              className="tareas-por-empleado-total"
              data-testid="tareasPorEmpleado.totalGeneral"
              role="status"
            >
              {t('report.byClient.totalHoras', 'Total horas')}:{' '}
              <strong>{totalGeneralHoras.toFixed(2)}</strong>
              {' · '}
              {t('report.byClient.totalTareas', 'Total tareas')}:{' '}
              <strong>{totalGeneralTareas}</strong>
            </div>
            <div className="tareas-por-empleado-export">
              {!hasData && !loading && (
                <span className="tareas-por-empleado-export-no-data" data-testid="exportarExcel.mensajeSinDatos">
                  {t('report.export.noData', 'No hay datos para exportar')}
                </span>
              )}
              <button
                type="button"
                onClick={handleExportExcel}
                disabled={!hasData || loading}
                className="tareas-por-empleado-btn-export"
                data-testid="exportarExcel.boton"
                aria-label={t('report.export.aria', 'Exportar a Excel')}
              >
                {t('report.export.button', 'Exportar a Excel')}
              </button>
            </div>
          </div>

          {loading ? (
            <div className="tareas-por-empleado-loading" data-testid="tareasPorEmpleado.loading">
              {t('tasks.list.loading', 'Cargando...')}
            </div>
          ) : grupos.length === 0 ? (
            <div
              className="tareas-por-empleado-empty"
              data-testid="tareasPorEmpleado.empty"
              role="status"
            >
              {t('report.detail.empty', 'No se encontraron tareas para los filtros seleccionados.')}
            </div>
          ) : (
            <div
              className="tareas-por-empleado-groups"
              data-testid="tareasPorEmpleado.grupos"
              role="list"
            >
              {grupos.map((grupo) => {
                const isExpanded = expandedUsuarioId === grupo.usuario_id;
                return (
                  <div
                    key={grupo.usuario_id}
                    className="tareas-por-empleado-group"
                    data-testid={`tareasPorEmpleado.grupo.${grupo.usuario_id}`}
                  >
                    <button
                      type="button"
                      className="tareas-por-empleado-group-header"
                      onClick={() => toggleGroup(grupo.usuario_id)}
                      aria-expanded={isExpanded}
                      aria-controls={`tareasPorEmpleado.detail.${grupo.usuario_id}`}
                      id={`tareasPorEmpleado.header.${grupo.usuario_id}`}
                      data-testid={`tareasPorEmpleado.grupoExpandir.${grupo.usuario_id}`}
                    >
                      <span className="tareas-por-empleado-group-title">
                        {grupo.nombre} {grupo.code ? `(${grupo.code})` : ''}
                      </span>
                      <span className="tareas-por-empleado-group-meta">
                        {grupo.total_horas.toFixed(2)} h · {grupo.cantidad_tareas}{' '}
                        {t('report.byClient.tareas', 'tareas')}
                      </span>
                      <span className="tareas-por-empleado-group-chevron" aria-hidden>
                        {isExpanded ? '▼' : '▶'}
                      </span>
                    </button>
                    {isExpanded && (
                      <div
                        id={`tareasPorEmpleado.detail.${grupo.usuario_id}`}
                        className="tareas-por-empleado-group-detail"
                        role="region"
                        aria-labelledby={`tareasPorEmpleado.header.${grupo.usuario_id}`}
                      >
                        <table
                          className="tareas-por-empleado-detail-table"
                          data-testid={`tareasPorEmpleado.tabla.${grupo.usuario_id}`}
                          role="table"
                        >
                          <thead>
                            <tr>
                              <th scope="col">{t('tasks.list.col.fecha', 'Fecha')}</th>
                              <th scope="col">{t('tasks.list.col.cliente', 'Cliente')}</th>
                              <th scope="col">{t('tasks.list.col.tipoTarea', 'Tipo tarea')}</th>
                              <th scope="col">{t('report.detail.col.horas', 'Horas')}</th>
                              <th scope="col">{t('tasks.list.col.sinCargo', 'Sin cargo')}</th>
                              <th scope="col">{t('tasks.list.col.presencial', 'Presencial')}</th>
                              <th scope="col">{t('report.detail.col.descripcion', 'Descripción')}</th>
                            </tr>
                          </thead>
                          <tbody>
                            {grupo.tareas.map((tarea) => (
                              <tr key={tarea.id} data-testid={`tareasPorEmpleado.fila.${tarea.id}`}>
                                <td>{tarea.fecha}</td>
                                <td>{tarea.cliente.nombre}</td>
                                <td>{tarea.tipo_tarea.descripcion}</td>
                                <td>{tarea.horas.toFixed(2)}</td>
                                <td>{tarea.sin_cargo ? t('tasks.list.si', 'Sí') : t('tasks.list.no', 'No')}</td>
                                <td>{tarea.presencial ? t('tasks.list.si', 'Sí') : t('tasks.list.no', 'No')}</td>
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
