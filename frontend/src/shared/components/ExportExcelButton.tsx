/**
 * Botón de exportación a Excel para grillas.
 * @see docs/04-tareas/000-Generalidades/TR-006-exportacion-excel.md
 */

import React, { useState } from 'react';
import { exportBasic, exportFormatted, buildExportFilename, ExportColumn } from '../utils/exportToExcel';
import { t } from '../i18n';

export interface ExportExcelButtonProps {
  proceso: string;
  gridId?: string;
  data: Record<string, unknown>[];
  columns: ExportColumn[];
  testId?: string;
}

export function ExportExcelButton({
  proceso,
  gridId: _gridId = 'default',
  data,
  columns,
  testId = 'exportExcel',
}: ExportExcelButtonProps): React.ReactElement {
  const [menuOpen, setMenuOpen] = useState(false);

  const hasData = data.length > 0;
  const filename = buildExportFilename(proceso);

  const handleExport = (mode: 'basic' | 'formatted') => {
    setMenuOpen(false);
    if (!hasData) return;
    if (mode === 'basic') {
      exportBasic(data, columns, filename);
    } else {
      exportFormatted(data, columns, filename);
    }
  };

  return (
    <div className="export-excel-wrapper">
      <button
        type="button"
        onClick={() => setMenuOpen((v) => !v)}
        disabled={!hasData}
        data-testid={testId}
        aria-label={t('grid.export.aria', 'Exportar a Excel')}
        aria-haspopup="menu"
        aria-expanded={menuOpen}
      >
        {t('grid.export.button', 'Exportar Excel')}
      </button>
      {menuOpen && (
        <ul
          className="export-excel-menu"
          role="menu"
          data-testid={`${testId}.menu`}
        >
          <li>
            <button
              type="button"
              role="menuitem"
              onClick={() => handleExport('basic')}
              data-testid={`${testId}.option.basic`}
            >
              {t('grid.export.option.basic', 'Planilla básica')}
            </button>
          </li>
          <li>
            <button
              type="button"
              role="menuitem"
              onClick={() => handleExport('formatted')}
              data-testid={`${testId}.option.formatted`}
            >
              {t('grid.export.option.formatted', 'Planilla formateada')}
            </button>
          </li>
        </ul>
      )}
    </div>
  );
}
