/**
 * Component: ClientSelector
 * 
 * Selector de clientes con carga dinámica desde el API.
 * 
 * @see TR-028(MH)-carga-de-tarea-diaria.md
 */

import React, { useEffect, useState } from 'react';
import { getClients, Client } from '../services/task.service';
import { t } from '../../../shared/i18n';
import './TaskForm.css';

export interface ClientSelectorProps {
  value: number | null;
  onChange: (clientId: number | null) => void;
  error?: string;
  disabled?: boolean;
  /** Si true, no muestra el label (para usar dentro de filtros con label externo). */
  showLabel?: boolean;
  /** Si true, añade opción "Todos" como primera opción. */
  allowAll?: boolean;
}

export function ClientSelector({ value, onChange, error, disabled, showLabel = true, allowAll = false }: ClientSelectorProps): React.ReactElement {
  const [clients, setClients] = useState<Client[]>([]);
  const [loading, setLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState<string>('');

  useEffect(() => {
    loadClients();
  }, []);

  const loadClients = async () => {
    setLoading(true);
    setErrorMessage('');
    
    const result = await getClients();
    
    if (result.success && result.data) {
      setClients(result.data);
    } else {
      setErrorMessage(result.errorMessage || t('tasks.form.selectors.clients.error', 'Error al cargar clientes'));
    }
    
    setLoading(false);
  };

  const handleChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const clientId = e.target.value ? parseInt(e.target.value, 10) : null;
    onChange(clientId);
  };

  const selectEl = (
    <select
      id="cliente-select"
      data-testid="task.form.clientSelect"
      value={value ?? ''}
      onChange={handleChange}
      disabled={disabled || loading}
      className={`form-input form-select ${error ? 'input-error' : ''}`}
      aria-label={t('tasks.form.fields.cliente.ariaLabel', 'Seleccionar cliente')}
      aria-invalid={!!error}
      aria-describedby={error ? 'cliente-error' : undefined}
      aria-required={!allowAll}
    >
      <option value="">{allowAll ? t('tasks.list.filters.todos', 'Todos') : t('tasks.form.selectors.clients.placeholder', '-- Seleccione un cliente --')}</option>
      {clients.map((client) => (
          <option key={client.id} value={client.id}>
            {client.nombre} ({client.code})
          </option>
        ))}
    </select>
  );

  if (!showLabel) {
    return (
      <div className="form-group">
        {selectEl}
        {loading && (
          <div className="field-loading" data-testid="task.form.clientSelect.loading">
            {t('tasks.form.selectors.clients.loading', 'Cargando clientes...')}
          </div>
        )}
        {(error || errorMessage) && (
          <span id="cliente-error" className="field-error" role="alert" data-testid="task.form.clientSelect.error">
            {error || errorMessage}
          </span>
        )}
      </div>
    );
  }

  return (
    <div className="form-group">
      <label htmlFor="cliente-select" className="form-label">
        {t('tasks.form.fields.cliente.label', 'Cliente')} {!allowAll && <span className="required">*</span>}
      </label>
      {selectEl}
      {loading && (
        <div className="field-loading" data-testid="task.form.clientSelect.loading">
          {t('tasks.form.selectors.clients.loading', 'Cargando clientes...')}
        </div>
      )}
      {(error || errorMessage) && (
        <span id="cliente-error" className="field-error" role="alert" data-testid="task.form.clientSelect.error">
          {error || errorMessage}
        </span>
      )}
    </div>
  );
}
