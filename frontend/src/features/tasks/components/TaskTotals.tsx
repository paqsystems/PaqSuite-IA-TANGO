/**
 * Component: TaskTotals
 *
 * Muestra totales del período filtrado: cantidad de tareas y total de horas.
 *
 * @see TR-033(MH)-visualización-de-lista-de-tareas-propias.md
 */

import React from 'react';
import { t } from '../../../shared/i18n';
import './TaskList.css';

export interface TaskTotalsProps {
  cantidadTareas: number;
  totalHoras: number;
}

export function TaskTotals({ cantidadTareas, totalHoras }: TaskTotalsProps): React.ReactElement {
  return (
    <div className="task-list-totals" data-testid="task.list.totals" role="status">
      <span className="task-list-totals-item">
        <strong>{cantidadTareas}</strong> {t('tasks.list.totals.tareas', 'tareas')}
      </span>
      <span className="task-list-totals-item">
        <strong>{totalHoras.toFixed(2)}</strong> {t('tasks.list.totals.horas', 'horas')}
      </span>
    </div>
  );
}
