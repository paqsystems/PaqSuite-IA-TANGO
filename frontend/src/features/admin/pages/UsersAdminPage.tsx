/**
 * Página de administración de usuarios.
 * @see docs/04-tareas/001-Seguridad/TR-010-administracion-usuarios.md
 * @see docs/04-tareas/000-Generalidades/TR-001-layouts-grilla.md
 */

import React, { useEffect, useState, useRef, useCallback } from 'react';
import { adminUsersApi } from '../services/admin.service';
import { DataGridDX } from '../../../shared/ui/DataGridDX';
import { ExportExcelButton } from '../../../shared/components/ExportExcelButton';
import { LayoutSelector } from '../../../shared/components/LayoutSelector';
import { useGridLayout } from '../../../shared/hooks/useGridLayout';
import type { Column } from '../../../shared/ui/DataTable/DataTable';
import './AdminPage.css';

const PROCESO = 'usuarios';
const GRID_ID = 'users';

export function UsersAdminPage(): React.ReactElement {
  const [data, setData] = useState<Record<string, unknown>[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const gridRef = useRef<{ instance: () => { state: (s?: Record<string, unknown>) => Record<string, unknown> } } | null>(null);
  const layout = useGridLayout({ proceso: PROCESO, gridId: GRID_ID });

  useEffect(() => {
    adminUsersApi
      .list()
      .then((res) => {
        const items = (res.resultado as { items?: Record<string, unknown>[] }).items ?? [];
        setData(items);
      })
      .catch((err) => setError(err.respuesta ?? 'Error al cargar usuarios'))
      .finally(() => setLoading(false));
  }, []);

  const getGridState = useCallback((): Record<string, unknown> | null => {
    const refObj = gridRef.current;
    const instance = refObj?.instance?.();
    if (!instance?.state) return null;
    try {
      return instance.state() ?? null;
    } catch {
      return null;
    }
  }, []);

  const applyState = useCallback((state: Record<string, unknown>) => {
    const refObj = gridRef.current;
    const instance = refObj?.instance?.();
    if (!instance?.state) return;
    try {
      instance.state(state);
    } catch {
      // ignore
    }
  }, []);

  const columns: Column<Record<string, unknown>>[] = [
    { key: 'id', header: 'ID', width: '80px', render: (r: Record<string, unknown>) => String(r.id ?? '') },
    { key: 'codigo', header: 'Código', width: '120px', render: (r: Record<string, unknown>) => String(r.codigo ?? '') },
    { key: 'name', header: 'Nombre', width: '200px', render: (r: Record<string, unknown>) => String(r.name ?? '') },
    { key: 'email', header: 'Email', width: '200px', render: (r: Record<string, unknown>) => String(r.email ?? '') },
    { key: 'activo', header: 'Activo', width: '80px', render: (row: Record<string, unknown>) => (row.activo ? 'Sí' : 'No') },
    { key: 'inhabilitado', header: 'Inhabilitado', width: '100px', render: (row: Record<string, unknown>) => (row.inhabilitado ? 'Sí' : 'No') },
  ];

  if (error) {
    return (
      <div className="admin-page" data-testid="users.admin">
        <h1>Usuarios</h1>
        <div className="admin-error" role="alert">
          {error}
        </div>
      </div>
    );
  }

  return (
    <div className="admin-page" data-testid="users.admin">
      <div className="admin-page-header">
        <h1>Usuarios</h1>
        <div className="admin-page-actions">
          <LayoutSelector
            proceso={PROCESO}
            gridId={GRID_ID}
            testIdBase="users.grid"
            layout={layout}
            getGridState={getGridState}
            applyState={applyState}
          />
          <ExportExcelButton
            proceso="usuarios"
            gridId="users"
            data={data}
            columns={columns.map((c) => ({ key: c.key, header: c.header ?? c.key, width: 120 }))}
            testId="users.grid.exportExcel"
          />
          <button type="button" className="admin-btn-primary" data-testid="users.create">
            Crear usuario
          </button>
        </div>
      </div>
      <div data-testid="users.grid">
        <DataGridDX
          testId="users.grid"
          data={data}
          columns={columns}
          loading={loading}
          emptyMessage="No hay usuarios"
          gridRef={gridRef}
          layoutLoad={layout.loadLastUsed}
        />
      </div>
    </div>
  );
}
