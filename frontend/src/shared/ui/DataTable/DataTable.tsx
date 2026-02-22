import React from 'react';
import { t } from '../../i18n';
import styles from './DataTable.module.css';

export interface Column<T> {
  /**
   * Key único de la columna
   */
  key: string;
  /**
   * Header de la columna (usará t() internamente)
   */
  header?: string;
  /**
   * Key de traducción para el header
   */
  headerKey?: string;
  /**
   * Función para renderizar el contenido de la celda
   */
  render: (row: T, index: number) => React.ReactNode;
  /**
   * Si la columna es ordenable
   */
  sortable?: boolean;
  /**
   * Ancho de la columna (opcional)
   */
  width?: string;
}

export interface DataTableProps<T> {
  /**
   * Test ID obligatorio para testing E2E
   */
  testId: string;
  /**
   * Datos a mostrar
   */
  data: T[];
  /**
   * Definición de columnas
   */
  columns: Column<T>[];
  /**
   * Mensaje cuando no hay datos (usará t() internamente)
   */
  emptyMessage?: string;
  /**
   * Key de traducción para el mensaje vacío
   */
  emptyMessageKey?: string;
  /**
   * Si está cargando
   */
  loading?: boolean;
  /**
   * Mensaje de carga (usará t() internamente)
   */
  loadingMessage?: string;
  /**
   * Key de traducción para el mensaje de carga
   */
  loadingMessageKey?: string;
  /**
   * Test ID para filas (se usará con el índice: `${rowTestId}-${index}`)
   */
  rowTestId?: string;
  /**
   * Clase CSS adicional
   */
  className?: string;
}

/**
 * DataTable - Componente wrapper de tabla con i18n y testId obligatorios
 * 
 * Reglas:
 * - testId es obligatorio
 * - Todo texto visible usa t() con fallback
 * - Separación CSS/JSX/JS
 * - Accesibilidad completa (tabla semántica, headers, roles)
 */
export function DataTable<T extends Record<string, any>>({
  testId,
  data,
  columns,
  emptyMessage,
  emptyMessageKey,
  loading = false,
  loadingMessage,
  loadingMessageKey,
  rowTestId,
  className
}: DataTableProps<T>) {
  // Textos traducidos
  const emptyText = emptyMessageKey 
    ? t(emptyMessageKey, emptyMessage || 'No hay datos disponibles')
    : emptyMessage || 'No hay datos disponibles';
  
  const loadingText = loadingMessageKey
    ? t(loadingMessageKey, loadingMessage || 'Cargando...')
    : loadingMessage || 'Cargando...';

  // Headers traducidos
  const getHeaderText = (column: Column<T>): string => {
    if (column.headerKey) {
      return t(column.headerKey, column.header || '');
    }
    return column.header || '';
  };

  const tableClasses = [styles.table, className].filter(Boolean).join(' ');

  if (loading) {
    return (
      <div 
        data-testid={`${testId}-loading`}
        className={styles.loadingContainer}
        role="status"
        aria-live="polite"
      >
        {loadingText}
      </div>
    );
  }

  if (data.length === 0) {
    return (
      <div 
        data-testid={`${testId}-empty`}
        className={styles.emptyContainer}
        role="status"
        aria-live="polite"
      >
        {emptyText}
      </div>
    );
  }

  return (
    <div className={styles.container} data-testid={testId}>
      <table className={tableClasses} role="table">
        <thead>
          <tr>
            {columns.map((column) => (
              <th
                key={column.key}
                scope="col"
                style={column.width ? { width: column.width } : undefined}
                className={column.sortable ? styles.sortable : undefined}
              >
                {getHeaderText(column)}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {data.map((row, rowIndex) => (
            <tr
              key={rowIndex}
              data-testid={rowTestId ? `${rowTestId}-${rowIndex}` : `${testId}-row-${rowIndex}`}
            >
              {columns.map((column) => (
                <td key={column.key}>
                  {column.render(row, rowIndex)}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

