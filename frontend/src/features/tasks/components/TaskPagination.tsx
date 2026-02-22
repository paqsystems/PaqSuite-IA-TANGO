/**
 * Component: TaskPagination
 *
 * Paginación para la lista de tareas (anterior / siguiente y página actual).
 *
 * @see TR-033(MH)-visualización-de-lista-de-tareas-propias.md
 */

import React from 'react';
import { t } from '../../../shared/i18n';
import './TaskList.css';

export interface TaskPaginationProps {
  currentPage: number;
  lastPage: number;
  total?: number;
  perPage?: number;
  onPageChange: (page: number) => void;
  disabled?: boolean;
  testIdPrefix?: string;
}

export function TaskPagination({
  currentPage,
  lastPage,
  total = 0,
  perPage = 15,
  onPageChange,
  disabled = false,
  testIdPrefix = 'task.list',
}: TaskPaginationProps): React.ReactElement {
  if (lastPage <= 1 && total <= perPage) {
    return (
      <nav
        className="task-list-pagination"
        data-testid={`${testIdPrefix}.pagination`}
        aria-label={t('tasks.list.pagination.label', 'Paginación de tareas')}
      >
        <span className="task-list-pagination-info">
          {t('tasks.list.pagination.showing', 'Mostrando')} {total} {t('tasks.list.pagination.items', 'registros')}
        </span>
      </nav>
    );
  }

  const from = (currentPage - 1) * perPage + 1;
  const to = Math.min(currentPage * perPage, total);

  return (
    <nav
      className="task-list-pagination"
      data-testid={`${testIdPrefix}.pagination`}
      aria-label={t('tasks.list.pagination.label', 'Paginación de tareas')}
    >
      <span className="task-list-pagination-info">
        {t('tasks.list.pagination.showing', 'Mostrando')} {from}-{to} {t('tasks.list.pagination.of', 'de')} {total}
      </span>
      <div className="task-list-pagination-buttons">
        <button
          type="button"
          className="task-list-pagination-btn"
          onClick={() => onPageChange(currentPage - 1)}
          disabled={disabled || currentPage <= 1}
          data-testid="task.list.pagination.prev"
          aria-label={t('tasks.list.pagination.prev', 'Página anterior')}
        >
          {t('tasks.list.pagination.prev', 'Anterior')}
        </button>
        <span className="task-list-pagination-current" data-testid="task.list.pagination.current">
          {t('tasks.list.pagination.page', 'Página')} {currentPage} / {lastPage}
        </span>
        <button
          type="button"
          className="task-list-pagination-btn"
          onClick={() => onPageChange(currentPage + 1)}
          disabled={disabled || currentPage >= lastPage}
          data-testid="task.list.pagination.next"
          aria-label={t('tasks.list.pagination.next', 'Página siguiente')}
        >
          {t('tasks.list.pagination.next', 'Siguiente')}
        </button>
      </div>
    </nav>
  );
}
