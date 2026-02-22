/**
 * Component: TaskTypeSelector
 * 
 * Selector de tipos de tarea con carga dinámica según cliente seleccionado.
 * 
 * @see TR-028(MH)-carga-de-tarea-diaria.md
 */

import React, { useEffect, useState } from 'react';
import { getTaskTypes, TaskType } from '../services/task.service';
import { t } from '../../../shared/i18n';
import './TaskForm.css';

export interface TaskTypeSelectorProps {
  clienteId: number | null;
  value: number | null;
  onChange: (tipoTareaId: number | null) => void;
  error?: string;
  disabled?: boolean;
  /** Si true, no muestra el label (para usar dentro de filtros con label externo). */
  showLabel?: boolean;
  /** Si true, añade opción "Todos" y cuando cliente es "Todos" carga todos los tipos (genéricos + no genéricos). */
  allowAll?: boolean;
}

export function TaskTypeSelector({ 
  clienteId, 
  value, 
  onChange, 
  error, 
  disabled,
  showLabel = true,
  allowAll = false,
}: TaskTypeSelectorProps): React.ReactElement {
  const [taskTypes, setTaskTypes] = useState<TaskType[]>([]);
  const [loading, setLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string>('');

  useEffect(() => {
    if (allowAll && (clienteId === null || clienteId === undefined)) {
      loadTaskTypes(undefined);
    } else if (clienteId) {
      loadTaskTypes(clienteId);
    } else {
      setTaskTypes([]);
      onChange(null);
    }
  }, [clienteId, allowAll]);

  const loadTaskTypes = async (clienteIdValue: number | undefined) => {
    setLoading(true);
    setErrorMessage('');
    
    const result = await getTaskTypes(clienteIdValue);
    
    if (result.success && result.data) {
      setTaskTypes(result.data);
    } else {
      setErrorMessage(result.errorMessage || t('tasks.form.selectors.taskTypes.error', 'Error al cargar tipos de tarea'));
      setTaskTypes([]);
    }
    
    setLoading(false);
  };

  const handleChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const tipoTareaId = e.target.value ? parseInt(e.target.value, 10) : null;
    onChange(tipoTareaId);
  };

  const isDisabled = disabled || loading || (!allowAll && !clienteId);
  const placeholder = allowAll
    ? t('tasks.list.filters.todos', 'Todos')
    : !clienteId
      ? t('tasks.form.selectors.taskTypes.placeholder.noClient', '-- Seleccione un cliente primero --')
      : t('tasks.form.selectors.taskTypes.placeholder.select', '-- Seleccione un tipo de tarea --');

  const selectEl = (
    <select
      id="tipo-tarea-select"
      data-testid="task.form.taskTypeSelect"
      value={value ?? ''}
      onChange={handleChange}
      disabled={isDisabled}
      className={`form-input form-select ${error ? 'input-error' : ''}`}
      aria-label={t('tasks.form.fields.tipoTarea.ariaLabel', 'Seleccionar tipo de tarea')}
      aria-invalid={!!error}
      aria-describedby={error ? 'tipo-tarea-error' : undefined}
      aria-required={!allowAll}
    >
      <option value="">{placeholder}</option>
      {taskTypes.map((tipo) => (
        <option key={tipo.id} value={tipo.id}>
          {tipo.descripcion} {tipo.is_generico ? t('tasks.form.selectors.taskTypes.generic', '(Genérico)') : ''}
        </option>
      ))}
    </select>
  );

  if (!showLabel) {
    return (
      <div className="form-group">
        {selectEl}
        {loading && (
          <div className="field-loading" data-testid="task.form.taskTypeSelect.loading">
            {t('tasks.form.selectors.taskTypes.loading', 'Cargando tipos de tarea...')}
          </div>
        )}
        {(error || errorMessage) && (
          <span id="tipo-tarea-error" className="field-error" role="alert" data-testid="task.form.taskTypeSelect.error">
            {error || errorMessage}
          </span>
        )}
      </div>
    );
  }

  return (
    <div className="form-group">
      <label htmlFor="tipo-tarea-select" className="form-label">
        {t('tasks.form.fields.tipoTarea.label', 'Tipo de Tarea')} {!allowAll && <span className="required">*</span>}
      </label>
      {selectEl}
      {loading && (
        <div className="field-loading" data-testid="task.form.taskTypeSelect.loading">
          {t('tasks.form.selectors.taskTypes.loading', 'Cargando tipos de tarea...')}
        </div>
      )}
      {(error || errorMessage) && (
        <span id="tipo-tarea-error" className="field-error" role="alert" data-testid="task.form.taskTypeSelect.error">
          {error || errorMessage}
        </span>
      )}
    </div>
  );
}
