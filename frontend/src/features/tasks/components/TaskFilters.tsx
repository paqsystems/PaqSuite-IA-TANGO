/**
 * Component: TaskFilters
 *
 * Filtros para la lista de tareas: rango de fechas, cliente, tipo de tarea, búsqueda, orden y (si supervisor) empleado.
 *
 * @see TR-033(MH)-visualización-de-lista-de-tareas-propias.md
 * @see TR-033(MH)-visualización-de-lista-de-tareas-propias-update.md
 */

import React from 'react';
import { ClientSelector } from './ClientSelector';
import { TaskTypeSelector } from './TaskTypeSelector';
import { EmployeeSelector } from './EmployeeSelector';
import { getUserData } from '../../../shared/utils/tokenStorage';
import { t } from '../../../shared/i18n';
import './TaskList.css';

export interface TaskFiltersValues {
  fechaDesde: string;
  fechaHasta: string;
  clienteId: number | null;
  tipoTareaId: number | null;
  empleadoId: number | null;
  busqueda: string;
  orden: 'asc' | 'desc';
}

export interface TaskFiltersProps {
  values: TaskFiltersValues;
  onChange: (values: TaskFiltersValues) => void;
  onApply: () => void;
  disabled?: boolean;
}

export function TaskFilters({
  values,
  onChange,
  onApply,
  disabled = false,
}: TaskFiltersProps): React.ReactElement {
  const handleChange = (field: keyof TaskFiltersValues) => (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>
  ) => {
    const v = e.target.value;
    if (field === 'orden') {
      onChange({ ...values, orden: v === 'asc' ? 'asc' : 'desc' });
    } else if (field === 'busqueda') {
      onChange({ ...values, busqueda: v });
    } else if (field === 'fechaDesde') {
      onChange({ ...values, fechaDesde: v });
    } else if (field === 'fechaHasta') {
      onChange({ ...values, fechaHasta: v });
    }
  };

  const userData = getUserData();
  const isSupervisor = userData?.esSupervisor ?? false;

  return (
    <div className="task-list-filters" data-testid="task.list.filters" role="search">
      <div className="task-list-filters-row">
        <label className="task-list-filter-label">
          {t('tasks.list.filters.fechaDesde', 'Fecha desde')}
          <input
            type="date"
            value={values.fechaDesde}
            onChange={handleChange('fechaDesde')}
            disabled={disabled}
            aria-label={t('tasks.list.filters.fechaDesde', 'Fecha desde')}
          />
        </label>
        <label className="task-list-filter-label">
          {t('tasks.list.filters.fechaHasta', 'Fecha hasta')}
          <input
            type="date"
            value={values.fechaHasta}
            onChange={handleChange('fechaHasta')}
            disabled={disabled}
            aria-label={t('tasks.list.filters.fechaHasta', 'Fecha hasta')}
          />
        </label>
        <div className="task-list-filter-label task-list-filter-cliente">
          <span className="task-list-filter-label-text">{t('tasks.list.filters.cliente', 'Cliente')}</span>
          <ClientSelector
            value={values.clienteId}
            onChange={(clienteId) => onChange({ ...values, clienteId })}
            disabled={disabled}
            showLabel={false}
            allowAll={true}
          />
        </div>
        <div className="task-list-filter-label task-list-filter-tipo">
          <span className="task-list-filter-label-text">{t('tasks.list.filters.tipoTarea', 'Tipo de tarea')}</span>
          <TaskTypeSelector
            value={values.tipoTareaId}
            onChange={(tipoTareaId) => onChange({ ...values, tipoTareaId })}
            clienteId={values.clienteId}
            disabled={disabled}
            showLabel={false}
            allowAll={true}
          />
        </div>
        {isSupervisor && (
          <div className="task-list-filter-label task-list-filter-empleado">
            <span className="task-list-filter-label-text">{t('tasks.list.filters.empleado', 'Empleado')}</span>
            <EmployeeSelector
              value={values.empleadoId}
              onChange={(empleadoId) => onChange({ ...values, empleadoId })}
              disabled={disabled}
              showLabel={false}
              allowAll={true}
            />
          </div>
        )}
      </div>
      <div className="task-list-filters-row">
        <label className="task-list-filter-label task-list-filter-busqueda">
          {t('tasks.list.filters.busqueda', 'Buscar en observación')}
          <input
            type="text"
            value={values.busqueda}
            onChange={handleChange('busqueda')}
            placeholder={t('tasks.list.filters.busquedaPlaceholder', 'Texto...')}
            disabled={disabled}
            aria-label={t('tasks.list.filters.busqueda', 'Buscar en observación')}
          />
        </label>
        <label className="task-list-filter-label">
          {t('tasks.list.filters.orden', 'Ordenar fecha')}
          <select
            value={values.orden}
            onChange={handleChange('orden')}
            disabled={disabled}
            aria-label={t('tasks.list.filters.orden', 'Ordenar fecha')}
          >
            <option value="desc">{t('tasks.list.filters.ordenDesc', 'Más reciente primero')}</option>
            <option value="asc">{t('tasks.list.filters.ordenAsc', 'Más antigua primero')}</option>
          </select>
        </label>
        <button
          type="button"
          className="task-list-filter-apply"
          onClick={onApply}
          disabled={disabled}
          data-testid="task.list.filters.apply"
        >
          {t('tasks.list.filters.aplicar', 'Aplicar')}
        </button>
      </div>
    </div>
  );
}
