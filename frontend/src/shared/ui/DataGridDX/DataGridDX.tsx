/**
 * DataGridDX - Wrapper de DevExtreme DataGrid con testId e i18n
 * Permite migración incremental desde DataTable a DevExtreme
 * Soporta layouts persistentes (TR-001) con stateStoring custom.
 */

import React, { useRef } from 'react';
import { DataGrid, Column, Scrolling, Paging, Pager } from 'devextreme-react/data-grid';
import { Column as ColumnDef } from '../DataTable/DataTable';

export interface DataGridDXProps<T> {
  testId: string;
  data: T[];
  columns: ColumnDef<T>[];
  emptyMessage?: string;
  emptyMessageKey?: string;
  loading?: boolean;
  loadingMessage?: string;
  loadingMessageKey?: string;
  rowTestId?: string;
  className?: string;
  /** Carga inicial de layout (last-used). Si se provee, habilita stateStoring custom. */
  layoutLoad?: () => Promise<Record<string, unknown> | null>;
  /** Ref para acceder al instance y obtener state() al guardar. */
  gridRef?: React.RefObject<{ instance: () => { state: (s?: Record<string, unknown>) => Record<string, unknown> } }>;
}

export function DataGridDX<T extends Record<string, any>>({
  testId,
  data,
  columns,
  emptyMessage,
  loading = false,
  loadingMessage,
  layoutLoad,
  gridRef,
}: DataGridDXProps<T>) {
  const internalRef = useRef<{ instance: () => { state: (s?: Record<string, unknown>) => Record<string, unknown> } }>(null);
  const ref = gridRef ?? internalRef;

  if (loading) {
    return (
      <div data-testid={`${testId}-loading`} role="status">
        {loadingMessage || 'Cargando...'}
      </div>
    );
  }

  const stateStoringConfig = layoutLoad
    ? {
        enabled: true,
        type: 'custom' as const,
        customLoad: async () => {
          const state = await layoutLoad();
          return state ?? undefined;
        },
      }
    : undefined;

  return (
    <div data-testid={testId} className="dx-datagrid-wrapper">
      <DataGrid
        ref={ref as React.RefObject<never>}
        dataSource={data}
        showBorders={true}
        noDataText={emptyMessage || 'No hay datos disponibles'}
        height="400px"
        stateStoring={stateStoringConfig}
      >
        {columns.map((col, idx) => (
          <Column
            key={col.key}
            dataField={col.key}
            caption={col.header || col.key}
            width={col.width}
            allowSorting={col.sortable ?? true}
            cellRender={
              col.render
                ? (cellData: { data: T; rowIndex: number }) =>
                    col.render(cellData.data, cellData.rowIndex ?? idx)
                : undefined
            }
          />
        ))}
        <Scrolling mode="standard" />
        <Paging enabled={data.length > 10} defaultPageSize={10} />
        <Pager showPageSizeSelector={true} allowedPageSizes={[10, 25, 50]} showInfo={true} />
      </DataGrid>
    </div>
  );
}
