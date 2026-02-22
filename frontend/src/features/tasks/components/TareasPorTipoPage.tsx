/**
 * Component: TareasPorTipoPage
 *
 * Pantalla de consulta agrupada por tipo de tarea (TR-047). Solo supervisores.
 * Filtros: período, cliente, empleado; grupos por tipo de tarea con total horas y cantidad;
 * accordion expandible con detalle de tareas (mismas columnas que consulta detallada); total general.
 */

import React, { useCallback, useEffect, useState } from 'react';
import {
  getReportByTaskType,
  ByTaskTypeGroup,
  ByTaskTypeReportParams,
} from '../services/task.service';
import { ClientSelector } from './ClientSelector';
import { EmployeeSelector } from './EmployeeSelector';
import { t } from '../../../shared/i18n';
import { buildExportFileName, exportGroupedToExcel, type GroupedExportGroup } from '../utils/exportToExcel';
import './TareasPorTipoPage.css';

export interface TareasPorTipoFilters {
  fechaDesde: string;
  fechaHasta: string;
  clienteId: number | null;
  usuarioId: number | null;
}

const defaultFilters: TareasPorTipoFilters = {
  fechaDesde: '',
  fechaHasta: '',
  clienteId: null,
  usuarioId: null,
};

function buildParams(filters: TareasPorTipoFilters): ByTaskTypeReportParams {
  const params: ByTaskTypeReportParams = {};
  if (filters.fechaDesde) params.fecha_desde = filters.fechaDesde;
  if (filters.fechaHasta) params.fecha_hasta = filters.fechaHasta;
  if (filters.clienteId != null) params.cliente_id = filters.clienteId;
  if (filters.usuarioId != null) params.usuario_id = filters.usuarioId;
  return params;
}

export function TareasPorTipoPage(): React.ReactElement {
  const [grupos, setGrupos] = useState<ByTaskTypeGroup[]>([]);
  const [totalGeneralHoras, setTotalGeneralHoras] = useState(0);
  const [totalGeneralTareas, setTotalGeneralTareas] = useState(0);
  const [filters, setFilters] = useState<TareasPorTipoFilters>(defaultFilters);
  const [appliedFilters, setAppliedFilters] = useState<TareasPorTipoFilters>(defaultFilters);
  const [loading, setLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState<string>('');
  const [expandedTipoTareaId, setExpandedTipoTareaId] = useState<number | null>(null);

  const loadReport = useCallback(async (params: ByTaskTypeReportParams) => {
    setLoading(true);
    setErrorMessage('');
    const result = await getReportByTaskType(params);
    setLoading(false);
    if (result.success && result.grupos !== undefined) {
      setGrupos(result.grupos);
      setTotalGeneralHoras(result.totalGeneralHoras ?? 0);
      setTotalGeneralTareas(result.totalGeneralTareas ?? 0);
    } else {
      setErrorMessage(
        result.errorMessage || t('report.byTaskType.error.load', 'Error al cargar el reporte')
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

  const toggleGroup = (tipoTareaId: number) => {
    setExpandedTipoTareaId((prev) => (prev === tipoTareaId ? null : tipoTareaId));
  };

  const hasData = grupos.length > 0;
  const handleExportExcel = () => {
    const exportGroups: GroupedExportGroup[] = grupos.map((g) => ({
      groupTitle: g.descripcion,
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
    const filename = buildExportFileName(appliedFilters.fechaDesde, appliedFilters.fechaHasta, 'por-tipo');
    exportGroupedToExcel(exportGroups, filename);
  };

  return (
    <div className="tareas-por-tipo-container" data-testid="tareasPorTipo.page">
      <header className="tareas-por-tipo-header">
        <h1 className="tareas-por-tipo-title">
          {t('report.byTaskType.title', 'Tareas por Tipo')}
        </h1>
      </header>

      <div
        className="tareas-por-tipo-filters"
        data-testid="tareasPorTipo.filtros"
        role="search"
      >
        <div className="tareas-por-tipo-filters-row">
          <label className="tareas-por-tipo-label">
            {t('tasks.list.filters.fechaDesde', 'Fecha desde')}
            <input
              type="date"
              value={filters.fechaDesde}
              onChange={(e) => setFilters({ ...filters, fechaDesde: e.target.value })}
              disabled={loading}
              data-testid="tareasPorTipo.filtroFechaDesde"
              aria-label={t('tasks.list.filters.fechaDesde', 'Fecha desde')}
            />
          </label>
          <label className="tareas-por-tipo-label">
            {t('tasks.list.filters.fechaHasta', 'Fecha hasta')}
            <input
              type="date"
              value={filters.fechaHasta}
              onChange={(e) => setFilters({ ...filters, fechaHasta: e.target.value })}
              disabled={loading}
              data-testid="tareasPorTipo.filtroFechaHasta"
              aria-label={t('tasks.list.filters.fechaHasta', 'Fecha hasta')}
            />
          </label>
          <div className="tareas-por-tipo-label tareas-por-tipo-cliente">
            <span className="tareas-por-tipo-label-text">{t('tasks.list.filters.cliente', 'Cliente')}</span>
            <ClientSelector
              value={filters.clienteId}
              onChange={(clienteId) => setFilters((f) => ({ ...f, clienteId }))}
              disabled={loading}
              showLabel={false}
              allowAll={true}
            />
          </div>
          <div className="tareas-por-tipo-label tareas-por-tipo-empleado">
            <span className="tareas-por-tipo-label-text">{t('tasks.list.filters.empleado', 'Empleado')}</span>
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
            className="tareas-por-tipo-btn-apply"
            data-testid="tareasPorTipo.aplicarFiltros"
          >
            {t('report.detail.applyFilters', 'Aplicar Filtros')}
          </button>
        </div>
      </div>

      {errorMessage && (
        <div className="tareas-por-tipo-error" data-testid="tareasPorTipo.mensajeError" role="alert">
          {errorMessage}
        </div>
      )}

      {!errorMessage && (
        <>
          <div className="tareas-por-tipo-total-row">
            <div
              className="tareas-por-tipo-total"
              data-testid="tareasPorTipo.totalGeneral"
              role="status"
            >
              {t('report.byClient.totalHoras', 'Total horas')}:{' '}
              <strong>{totalGeneralHoras.toFixed(2)}</strong>
              {' · '}
              {t('report.byClient.totalTareas', 'Total tareas')}:{' '}
              <strong>{totalGeneralTareas}</strong>
            </div>
            <div className="tareas-por-tipo-export">
              {!hasData && !loading && (
                <span className="tareas-por-tipo-export-no-data" data-testid="exportarExcel.mensajeSinDatos">
                  {t('report.export.noData', 'No hay datos para exportar')}
                </span>
              )}
              <button
                type="button"
                onClick={handleExportExcel}
                disabled={!hasData || loading}
                className="tareas-por-tipo-btn-export"
                data-testid="exportarExcel.boton"
                aria-label={t('report.export.aria', 'Exportar a Excel')}
              >
                {t('report.export.button', 'Exportar a Excel')}
              </button>
            </div>
          </div>

          {loading ? (
            <div className="tareas-por-tipo-loading" data-testid="tareasPorTipo.loading">
              {t('tasks.list.loading', 'Cargando...')}
            </div>
          ) : grupos.length === 0 ? (
            <div
              className="tareas-por-tipo-empty"
              data-testid="tareasPorTipo.empty"
              role="status"
            >
              {t('report.detail.empty', 'No se encontraron tareas para los filtros seleccionados.')}
            </div>
          ) : (
            <div
              className="tareas-por-tipo-groups"
              data-testid="tareasPorTipo.grupos"
              role="list"
            >
              {grupos.map((grupo) => {
                const isExpanded = expandedTipoTareaId === grupo.tipo_tarea_id;
                return (
                  <div
                    key={grupo.tipo_tarea_id}
                    className="tareas-por-tipo-group"
                    data-testid={`tareasPorTipo.grupo.${grupo.tipo_tarea_id}`}
                  >
                    <button
                      type="button"
                      className="tareas-por-tipo-group-header"
                      onClick={() => toggleGroup(grupo.tipo_tarea_id)}
                      aria-expanded={isExpanded}
                      aria-controls={`tareasPorTipo.detail.${grupo.tipo_tarea_id}`}
                      id={`tareasPorTipo.header.${grupo.tipo_tarea_id}`}
                      data-testid={`tareasPorTipo.grupoExpandir.${grupo.tipo_tarea_id}`}
                    >
                      <span className="tareas-por-tipo-group-title">
                        {grupo.descripcion}
                      </span>
                      <span className="tareas-por-tipo-group-meta">
                        {grupo.total_horas.toFixed(2)} h · {grupo.cantidad_tareas}{' '}
                        {t('report.byClient.tareas', 'tareas')}
                      </span>
                      <span className="tareas-por-tipo-group-chevron" aria-hidden>
                        {isExpanded ? '▼' : '▶'}
                      </span>
                    </button>
                    {isExpanded && (
                      <div
                        id={`tareasPorTipo.detail.${grupo.tipo_tarea_id}`}
                        className="tareas-por-tipo-group-detail"
                        role="region"
                        aria-labelledby={`tareasPorTipo.header.${grupo.tipo_tarea_id}`}
                      >
                        <table
                          className="tareas-por-tipo-detail-table"
                          data-testid={`tareasPorTipo.tabla.${grupo.tipo_tarea_id}`}
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
                              <tr key={tarea.id} data-testid={`tareasPorTipo.fila.${tarea.id}`}>
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
