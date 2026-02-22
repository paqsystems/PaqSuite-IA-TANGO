/**
 * Component: DeleteTaskModal
 *
 * Modal de confirmación para eliminar una tarea (TR-030, TR-032).
 * Muestra información de la tarea (fecha, cliente, tipo, duración) y, si existe, empleado propietario (supervisor).
 */

import React from 'react';
import { TaskListItem } from '../services/task.service';
import { t } from '../../../shared/i18n';
import './DeleteTaskModal.css';

export interface DeleteTaskModalProps {
  /** Tarea a eliminar (null = modal cerrado) */
  task: TaskListItem | null;
  /** Si está en curso la petición de eliminación */
  loading?: boolean;
  /** Mensaje de error a mostrar (2111, 4030, 4040) */
  errorMessage?: string;
  /** Callback al confirmar eliminación */
  onConfirm: () => void;
  /** Callback al cancelar */
  onCancel: () => void;
}

export function DeleteTaskModal({
  task,
  loading = false,
  errorMessage = '',
  onConfirm,
  onCancel,
}: DeleteTaskModalProps): React.ReactElement | null {
  if (!task) return null;

  return (
    <div
      className="delete-task-modal-overlay"
      role="dialog"
      aria-modal="true"
      aria-labelledby="delete-task-modal-title"
      aria-describedby="delete-task-modal-desc"
      data-testid="task.delete.modal"
    >
      <div className="delete-task-modal-content">
        <h2 id="delete-task-modal-title" className="delete-task-modal-title">
          {t('tasks.delete.modal.title', 'Eliminar tarea')}
        </h2>
        <p id="delete-task-modal-desc" className="delete-task-modal-desc">
          {t('tasks.delete.modal.confirmQuestion', '¿Está seguro de que desea eliminar esta tarea?')}
        </p>
        <dl className="delete-task-modal-info">
          <div className="delete-task-modal-info-row">
            <dt>{t('tasks.list.col.fecha', 'Fecha')}</dt>
            <dd>{task.fecha}</dd>
          </div>
          <div className="delete-task-modal-info-row">
            <dt>{t('tasks.list.col.cliente', 'Cliente')}</dt>
            <dd>{task.cliente.nombre}</dd>
          </div>
          <div className="delete-task-modal-info-row">
            <dt>{t('tasks.list.col.tipoTarea', 'Tipo tarea')}</dt>
            <dd>{task.tipo_tarea.nombre}</dd>
          </div>
          <div className="delete-task-modal-info-row">
            <dt>{t('tasks.list.col.duracion', 'Duración')}</dt>
            <dd>{task.duracion_horas}</dd>
          </div>
          {task.empleado && (
            <div className="delete-task-modal-info-row" data-testid="task.delete.employee">
              <dt>{t('tasks.form.fields.empleado.label', 'Empleado')}</dt>
              <dd>{task.empleado.nombre} ({task.empleado.code})</dd>
            </div>
          )}
        </dl>
        {errorMessage && (
          <div className="delete-task-modal-error" role="alert" data-testid="task.delete.error">
            {errorMessage}
          </div>
        )}
        <div className="delete-task-modal-actions">
          <button
            type="button"
            className="delete-task-modal-btn delete-task-modal-btn-cancel"
            onClick={onCancel}
            disabled={loading}
            data-testid="task.delete.cancel"
            aria-label={t('tasks.delete.cancelLabel', 'Cancelar eliminación')}
          >
            {t('tasks.delete.cancel', 'Cancelar')}
          </button>
          <button
            type="button"
            className="delete-task-modal-btn delete-task-modal-btn-confirm"
            onClick={onConfirm}
            disabled={loading}
            data-testid="task.delete.confirm"
            aria-label={t('tasks.delete.confirmLabel', 'Confirmar eliminación')}
          >
            {loading ? t('tasks.delete.deleting', 'Eliminando...') : t('tasks.delete.confirm', 'Eliminar')}
          </button>
        </div>
      </div>
    </div>
  );
}

export default DeleteTaskModal;
