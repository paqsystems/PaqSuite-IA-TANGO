/**
 * Component: ProcesoMasivoPage
 *
 * Página de proceso masivo de tareas (TR-039 a TR-043). Solo supervisores.
 * Filtros (fecha, cliente, empleado, estado), tabla con checkboxes,
 * Seleccionar/Deseleccionar todos, contador, botón Procesar con confirmación.
 */

import React, { useCallback, useEffect, useState } from 'react';
import {
  getAllTasks,
  bulkToggleClose,
  TaskListItem,
  TaskListParams,
} from '../services/task.service';
import { ClientSelector } from './ClientSelector';
import { EmployeeSelector } from './EmployeeSelector';
import { TaskPagination } from './TaskPagination';
import { TaskTotals } from './TaskTotals';
import { Modal } from '../../../shared/ui/Modal';
import { t } from '../../../shared/i18n';
import './ProcesoMasivoPage.css';

const DEFAULT_PER_PAGE = 15;
type EstadoCerrado = 'all' | 'open' | 'closed';

interface ProcesoMasivoFilters {
  fechaDesde: string;
  fechaHasta: string;
  clienteId: number | null;
  empleadoId: number | null;
  estadoCerrado: EstadoCerrado;
}

const DEFAULT_FILTERS: ProcesoMasivoFilters = {
  fechaDesde: '',
  fechaHasta: '',
  clienteId: null,
  empleadoId: null,
  estadoCerrado: 'all',
};

function buildParams(
  page: number,
  filters: ProcesoMasivoFilters,
  perPage: number
): TaskListParams {
  const params: TaskListParams = {
    page,
    per_page: perPage,
    ordenar_por: 'fecha',
    orden: 'desc',
  };
  if (filters.fechaDesde) params.fecha_desde = filters.fechaDesde;
  if (filters.fechaHasta) params.fecha_hasta = filters.fechaHasta;
  if (filters.clienteId != null) params.cliente_id = filters.clienteId;
  if (filters.empleadoId != null) params.usuario_id = filters.empleadoId;
  if (filters.estadoCerrado === 'open') params.cerrado = false;
  if (filters.estadoCerrado === 'closed') params.cerrado = true;
  return params;
}

export function ProcesoMasivoPage(): React.ReactElement {
  const [data, setData] = useState<TaskListItem[]>([]);
  const [pagination, setPagination] = useState({
    current_page: 1,
    per_page: DEFAULT_PER_PAGE,
    total: 0,
    last_page: 1,
  });
  const [totales, setTotales] = useState({ cantidad_tareas: 0, total_horas: 0 });
  const [filters, setFilters] = useState<ProcesoMasivoFilters>(DEFAULT_FILTERS);
  const [appliedFilters, setAppliedFilters] = useState<ProcesoMasivoFilters>(DEFAULT_FILTERS);
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState<string>('');
  const [selectedIds, setSelectedIds] = useState<Set<number>>(new Set());
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [processing, setProcessing] = useState(false);
  const [successMessage, setSuccessMessage] = useState<string>('');
  const [processError, setProcessError] = useState<string>('');

  const loadTasks = useCallback(async (params: TaskListParams) => {
    setLoading(true);
    setErrorMessage('');
    const result = await getAllTasks(params);
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

  const handleApplyFilters = () => {
    setAppliedFilters(filters);
    setPage(1);
    setSelectedIds(new Set());
  };

  const handlePageChange = (newPage: number) => {
    setPage(newPage);
  };

  const toggleSelect = (id: number) => {
    setSelectedIds((prev) => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  };

  const selectAll = () => {
    setSelectedIds(new Set(data.map((r) => r.id)));
  };

  const deselectAll = () => {
    setSelectedIds(new Set());
  };

  const handleProcesarClick = () => {
    if (selectedIds.size === 0) return;
    setProcessError('');
    setShowConfirmModal(true);
  };

  const handleConfirmProcesar = async () => {
    const ids = Array.from(selectedIds);
    setShowConfirmModal(false);
    setProcessing(true);
    setProcessError('');
    const result = await bulkToggleClose(ids);
    setProcessing(false);
    if (result.success && result.processed != null) {
      setSuccessMessage(
        (t('procesoMasivo.success', 'Se procesaron X registros') as string).replace(
          'X',
          String(result.processed)
        )
      );
      setSelectedIds(new Set());
      const params = buildParams(page, appliedFilters, DEFAULT_PER_PAGE);
      await loadTasks(params);
      setTimeout(() => setSuccessMessage(''), 4000);
    } else {
      setProcessError(result.errorMessage || 'Error al procesar');
    }
  };

  const handleCancelConfirm = () => {
    setShowConfirmModal(false);
    setProcessError('');
  };

  return (
    <div className="proceso-masivo-container" data-testid="procesoMasivo.page">
      <header className="proceso-masivo-header">
        <h1 className="proceso-masivo-title">
          {t('procesoMasivo.title', 'Proceso Masivo de Tareas')}
        </h1>
      </header>

      <div className="proceso-masivo-filtros" data-testid="procesoMasivo.filtros" role="search">
        <div className="proceso-masivo-filtros-row">
          <label className="proceso-masivo-label">
            {t('tasks.list.filters.fechaDesde', 'Fecha desde')}
            <input
              type="date"
              value={filters.fechaDesde}
              onChange={(e) => setFilters((f) => ({ ...f, fechaDesde: e.target.value }))}
              disabled={loading}
              data-testid="procesoMasivo.filtroFechaDesde"
              aria-label="Fecha desde"
            />
          </label>
          <label className="proceso-masivo-label">
            {t('tasks.list.filters.fechaHasta', 'Fecha hasta')}
            <input
              type="date"
              value={filters.fechaHasta}
              onChange={(e) => setFilters((f) => ({ ...f, fechaHasta: e.target.value }))}
              disabled={loading}
              data-testid="procesoMasivo.filtroFechaHasta"
              aria-label="Fecha hasta"
            />
          </label>
          <div className="proceso-masivo-label proceso-masivo-cliente">
            <span className="proceso-masivo-label-text">{t('tasks.list.filters.cliente', 'Cliente')}</span>
            <ClientSelector
              value={filters.clienteId}
              onChange={(clienteId) => setFilters((f) => ({ ...f, clienteId }))}
              disabled={loading}
              showLabel={false}
              allowAll={true}
            />
          </div>
          <div className="proceso-masivo-label proceso-masivo-empleado">
            <span className="proceso-masivo-label-text">{t('tasks.list.filters.empleado', 'Empleado')}</span>
            <EmployeeSelector
              value={filters.empleadoId}
              onChange={(empleadoId) => setFilters((f) => ({ ...f, empleadoId }))}
              disabled={loading}
              showLabel={false}
              allowAll={true}
            />
          </div>
          <label className="proceso-masivo-label">
            {t('procesoMasivo.filtroEstado', 'Estado')}
            <select
              value={filters.estadoCerrado}
              onChange={(e) =>
                setFilters((f) => ({ ...f, estadoCerrado: e.target.value as EstadoCerrado }))
              }
              disabled={loading}
              data-testid="procesoMasivo.filtroEstado"
              aria-label="Estado cerrado/abierto"
            >
              <option value="all">{t('procesoMasivo.estadoTodos', 'Todos')}</option>
              <option value="open">{t('procesoMasivo.estadoAbiertos', 'Abiertos')}</option>
              <option value="closed">{t('procesoMasivo.estadoCerrados', 'Cerrados')}</option>
            </select>
          </label>
          <button
            type="button"
            className="proceso-masivo-btn-apply"
            onClick={handleApplyFilters}
            disabled={loading}
            data-testid="procesoMasivo.aplicarFiltros"
          >
            {t('procesoMasivo.aplicarFiltros', 'Aplicar Filtros')}
          </button>
        </div>
      </div>

      {errorMessage && (
        <div className="proceso-masivo-error" data-testid="procesoMasivo.mensajeError" role="alert">
          {errorMessage}
        </div>
      )}

      {processError && (
        <div className="proceso-masivo-error" data-testid="procesoMasivo.mensajeError" role="alert">
          {processError}
        </div>
      )}

      {successMessage && (
        <div className="proceso-masivo-success" data-testid="procesoMasivo.mensajeExito" role="status">
          {successMessage}
        </div>
      )}

      {processing && (
        <div className="proceso-masivo-loading" data-testid="procesoMasivo.procesando" role="status">
          {t('procesoMasivo.procesando', 'Procesando...')}
        </div>
      )}

      {!errorMessage && (
        <>
          <TaskTotals
            cantidadTareas={totales.cantidad_tareas}
            totalHoras={totales.total_horas}
          />
          <div className="proceso-masivo-total" data-testid="procesoMasivo.total">
            {t('procesoMasivo.totalTareas', 'Total de tareas filtradas')}: {totales.cantidad_tareas}
          </div>

          <div className="proceso-masivo-actions">
            <button
              type="button"
              className="proceso-masivo-btn-link"
              onClick={selectAll}
              disabled={loading || data.length === 0}
              data-testid="procesoMasivo.seleccionarTodos"
            >
              {t('procesoMasivo.seleccionarTodos', 'Seleccionar todos')}
            </button>
            <button
              type="button"
              className="proceso-masivo-btn-link"
              onClick={deselectAll}
              disabled={loading || selectedIds.size === 0}
              data-testid="procesoMasivo.deseleccionarTodos"
            >
              {t('procesoMasivo.deseleccionarTodos', 'Deseleccionar todos')}
            </button>
            <span className="proceso-masivo-contador" data-testid="procesoMasivo.contadorSeleccionadas">
              {selectedIds.size} {t('procesoMasivo.tareasSeleccionadas', 'tareas seleccionadas')}
            </span>
            <button
              type="button"
              className="proceso-masivo-btn-procesar"
              onClick={handleProcesarClick}
              disabled={selectedIds.size === 0 || processing}
              data-testid="procesoMasivo.procesar"
            >
              {t('procesoMasivo.procesar', 'Procesar')}
            </button>
          </div>

          {loading ? (
            <div className="proceso-masivo-loading" data-testid="procesoMasivo.loading">
              {t('tasks.list.loading', 'Cargando...')}
            </div>
          ) : data.length === 0 ? (
            <div className="proceso-masivo-empty" data-testid="procesoMasivo.empty">
              {t('tasks.list.empty', 'No hay tareas que mostrar.')}
            </div>
          ) : (
            <div className="proceso-masivo-table-wrapper">
              <table className="proceso-masivo-table" data-testid="procesoMasivo.tabla" role="table">
                <thead>
                  <tr>
                    <th scope="col" className="proceso-masivo-th-checkbox">
                      {t('procesoMasivo.colSeleccion', 'Sel.')}
                    </th>
                    <th scope="col">{t('tasks.list.col.fecha', 'Fecha')}</th>
                    <th scope="col">{t('tasks.form.fields.empleado.label', 'Empleado')}</th>
                    <th scope="col">{t('tasks.list.col.cliente', 'Cliente')}</th>
                    <th scope="col">{t('tasks.list.col.tipoTarea', 'Tipo tarea')}</th>
                    <th scope="col">{t('tasks.list.col.duracion', 'Duración')}</th>
                    <th scope="col">{t('tasks.list.col.cerrado', 'Cerrado')}</th>
                  </tr>
                </thead>
                <tbody>
                  {data.map((row) => (
                    <tr
                      key={row.id}
                      data-testid={`procesoMasivo.row.${row.id}`}
                      className={row.cerrado ? 'proceso-masivo-row-closed' : ''}
                    >
                      <td className="proceso-masivo-td-checkbox">
                        <input
                          type="checkbox"
                          checked={selectedIds.has(row.id)}
                          onChange={() => toggleSelect(row.id)}
                          disabled={processing}
                          data-testid={`procesoMasivo.checkboxTarea.${row.id}`}
                          aria-label={t('procesoMasivo.seleccionarTarea', 'Seleccionar tarea')}
                        />
                      </td>
                      <td>{row.fecha}</td>
                      <td>
                        {row.empleado
                          ? `${row.empleado.nombre} (${row.empleado.code})`
                          : '—'}
                      </td>
                      <td>{row.cliente.nombre}</td>
                      <td>{row.tipo_tarea.nombre}</td>
                      <td>{row.duracion_horas}</td>
                      <td>{row.cerrado ? t('tasks.list.si', 'Sí') : t('tasks.list.no', 'No')}</td>
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

      {showConfirmModal && (
        <Modal
          testId="procesoMasivo.confirmModal"
          isOpen={showConfirmModal}
          onClose={handleCancelConfirm}
          title={t('procesoMasivo.confirmTitle', 'Confirmar procesamiento')}
        >
          <p>
            {(t('procesoMasivo.confirmMessage', 'Se invertirá el estado de N tareas. ¿Continuar?') as string).replace(
              'N',
              String(selectedIds.size)
            )}
          </p>
          <div className="proceso-masivo-modal-actions">
            <button
              type="button"
              className="proceso-masivo-btn-procesar"
              onClick={handleConfirmProcesar}
              data-testid="procesoMasivo.confirmarProcesar"
            >
              {t('procesoMasivo.confirmar', 'Confirmar')}
            </button>
            <button type="button" className="proceso-masivo-btn-cancel" onClick={handleCancelConfirm}>
              {t('procesoMasivo.cancelar', 'Cancelar')}
            </button>
          </div>
        </Modal>
      )}
    </div>
  );
}
