/**
 * Component: TareasPorFechaPage
 *
 * Pantalla de consulta agrupada por fecha (TR-048). Empleado, supervisor y cliente.
 * Filtros de período; resultados filtrados por rol; grupos por fecha con total horas y cantidad;
 * accordion expandible con detalle; total general; orden cronológico.
 */

import React, { useCallback, useEffect, useState } from 'react';
import {
  getReportByDate,
  ByDateGroup,
  ByDateReportParams,
} from '../services/task.service';
import { t } from '../../../shared/i18n';
import { buildExportFileName, exportGroupedToExcel, type GroupedExportGroup } from '../utils/exportToExcel';
import './TareasPorFechaPage.css';

export interface TareasPorFechaFilters {
  fechaDesde: string;
  fechaHasta: string;
}

const defaultFilters: TareasPorFechaFilters = {
  fechaDesde: '',
  fechaHasta: '',
};

function buildParams(filters: TareasPorFechaFilters): ByDateReportParams {
  const params: ByDateReportParams = {};
  if (filters.fechaDesde) params.fecha_desde = filters.fechaDesde;
  if (filters.fechaHasta) params.fecha_hasta = filters.fechaHasta;
  return params;
}

export function TareasPorFechaPage(): React.ReactElement {
  const [grupos, setGrupos] = useState<ByDateGroup[]>([]);
  const [totalGeneralHoras, setTotalGeneralHoras] = useState(0);
  const [totalGeneralTareas, setTotalGeneralTareas] = useState(0);
  const [filters, setFilters] = useState<TareasPorFechaFilters>(defaultFilters);
  const [appliedFilters, setAppliedFilters] = useState<TareasPorFechaFilters>(defaultFilters);
  const [loading, setLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState<string>('');
  const [expandedFecha, setExpandedFecha] = useState<string | null>(null);

  const loadReport = useCallback(async (params: ByDateReportParams) => {
    setLoading(true);
    setErrorMessage('');
    const result = await getReportByDate(params);
    setLoading(false);
    if (result.success && result.grupos !== undefined) {
      setGrupos(result.grupos);
      setTotalGeneralHoras(result.totalGeneralHoras ?? 0);
      setTotalGeneralTareas(result.totalGeneralTareas ?? 0);
    } else {
      setErrorMessage(
        result.errorMessage || t('report.byDate.error.load', 'Error al cargar el reporte')
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

  const toggleGroup = (fecha: string) => {
    setExpandedFecha((prev) => (prev === fecha ? null : fecha));
  };

  const hasData = grupos.length > 0;
  const handleExportExcel = () => {
    const exportGroups: GroupedExportGroup[] = grupos.map((g) => ({
      groupTitle: g.fecha,
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
    const filename = buildExportFileName(appliedFilters.fechaDesde, appliedFilters.fechaHasta, 'por-fecha');
    exportGroupedToExcel(exportGroups, filename);
  };

  return (
    <div className="tareas-por-fecha-container" data-testid="tareasPorFecha.page">
      <header className="tareas-por-fecha-header">
        <h1 className="tareas-por-fecha-title">
          {t('report.byDate.title', 'Tareas por Fecha')}
        </h1>
      </header>

      <div
        className="tareas-por-fecha-filters"
        data-testid="tareasPorFecha.filtros"
        role="search"
      >
        <div className="tareas-por-fecha-filters-row">
          <label className="tareas-por-fecha-label">
            {t('tasks.list.filters.fechaDesde', 'Fecha desde')}
            <input
              type="date"
              value={filters.fechaDesde}
              onChange={(e) => setFilters({ ...filters, fechaDesde: e.target.value })}
              disabled={loading}
              data-testid="tareasPorFecha.filtroFechaDesde"
              aria-label={t('tasks.list.filters.fechaDesde', 'Fecha desde')}
            />
          </label>
          <label className="tareas-por-fecha-label">
            {t('tasks.list.filters.fechaHasta', 'Fecha hasta')}
            <input
              type="date"
              value={filters.fechaHasta}
              onChange={(e) => setFilters({ ...filters, fechaHasta: e.target.value })}
              disabled={loading}
              data-testid="tareasPorFecha.filtroFechaHasta"
              aria-label={t('tasks.list.filters.fechaHasta', 'Fecha hasta')}
            />
          </label>
          <button
            type="button"
            onClick={handleApplyFilters}
            disabled={loading}
            className="tareas-por-fecha-btn-apply"
            data-testid="tareasPorFecha.aplicarFiltros"
          >
            {t('report.detail.applyFilters', 'Aplicar Filtros')}
          </button>
        </div>
      </div>

      {errorMessage && (
        <div className="tareas-por-fecha-error" data-testid="tareasPorFecha.mensajeError" role="alert">
          {errorMessage}
        </div>
      )}

      {!errorMessage && (
        <>
          <div className="tareas-por-fecha-total-row">
            <div
              className="tareas-por-fecha-total"
              data-testid="tareasPorFecha.totalGeneral"
              role="status"
            >
              {t('report.byClient.totalHoras', 'Total horas')}:{' '}
              <strong>{totalGeneralHoras.toFixed(2)}</strong>
              {' · '}
              {t('report.byClient.totalTareas', 'Total tareas')}:{' '}
              <strong>{totalGeneralTareas}</strong>
            </div>
            <div className="tareas-por-fecha-export">
              {!hasData && !loading && (
                <span className="tareas-por-fecha-export-no-data" data-testid="exportarExcel.mensajeSinDatos">
                  {t('report.export.noData', 'No hay datos para exportar')}
                </span>
              )}
              <button
                type="button"
                onClick={handleExportExcel}
                disabled={!hasData || loading}
                className="tareas-por-fecha-btn-export"
                data-testid="exportarExcel.boton"
                aria-label={t('report.export.aria', 'Exportar a Excel')}
              >
                {t('report.export.button', 'Exportar a Excel')}
              </button>
            </div>
          </div>

          {loading ? (
            <div className="tareas-por-fecha-loading" data-testid="tareasPorFecha.loading">
              {t('tasks.list.loading', 'Cargando...')}
            </div>
          ) : grupos.length === 0 ? (
            <div
              className="tareas-por-fecha-empty"
              data-testid="tareasPorFecha.empty"
              role="status"
            >
              {t('report.detail.empty', 'No se encontraron tareas para los filtros seleccionados.')}
            </div>
          ) : (
            <div
              className="tareas-por-fecha-groups"
              data-testid="tareasPorFecha.grupos"
              role="list"
            >
              {grupos.map((grupo) => {
                const isExpanded = expandedFecha === grupo.fecha;
                return (
                  <div
                    key={grupo.fecha}
                    className="tareas-por-fecha-group"
                    data-testid={`tareasPorFecha.grupo.${grupo.fecha}`}
                  >
                    <button
                      type="button"
                      className="tareas-por-fecha-group-header"
                      onClick={() => toggleGroup(grupo.fecha)}
                      aria-expanded={isExpanded}
                      aria-controls={`tareasPorFecha.detail.${grupo.fecha}`}
                      id={`tareasPorFecha.header.${grupo.fecha}`}
                      data-testid={`tareasPorFecha.grupoExpandir.${grupo.fecha}`}
                    >
                      <span className="tareas-por-fecha-group-title">
                        {grupo.fecha}
                      </span>
                      <span className="tareas-por-fecha-group-meta">
                        {grupo.total_horas.toFixed(2)} h · {grupo.cantidad_tareas}{' '}
                        {t('report.byClient.tareas', 'tareas')}
                      </span>
                      <span className="tareas-por-fecha-group-chevron" aria-hidden>
                        {isExpanded ? '▼' : '▶'}
                      </span>
                    </button>
                    {isExpanded && (
                      <div
                        id={`tareasPorFecha.detail.${grupo.fecha}`}
                        className="tareas-por-fecha-group-detail"
                        role="region"
                        aria-labelledby={`tareasPorFecha.header.${grupo.fecha}`}
                      >
                        <table
                          className="tareas-por-fecha-detail-table"
                          data-testid={`tareasPorFecha.tabla.${grupo.fecha}`}
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
                              <tr key={tarea.id} data-testid={`tareasPorFecha.fila.${tarea.id}`}>
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
