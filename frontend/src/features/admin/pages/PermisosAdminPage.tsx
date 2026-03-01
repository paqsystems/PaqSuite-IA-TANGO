/**
 * Página de administración de permisos (asignaciones usuario-empresa-rol).
 * @see docs/04-tareas/001-Seguridad/TR-013-administracion-permisos.md
 */

import React, { useEffect, useState } from 'react';
import { adminPermisosApi } from '../services/admin.service';
import { DataGridDX } from '../../../shared/ui/DataGridDX';
import type { Column } from '../../../shared/ui/DataTable/DataTable';
import './AdminPage.css';

export function PermisosAdminPage(): React.ReactElement {
  const [data, setData] = useState<Record<string, unknown>[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    adminPermisosApi
      .list()
      .then((res) => {
        const items = (res.resultado as { items?: Record<string, unknown>[] }).items ?? [];
        setData(items);
      })
      .catch((err) => setError(err.respuesta ?? 'Error al cargar permisos'))
      .finally(() => setLoading(false));
  }, []);

  const columns: Column<Record<string, unknown>>[] = [
    { key: 'id', header: 'ID', width: '80px', render: (r: Record<string, unknown>) => String(r.id ?? '') },
    { key: 'usuarioCode', header: 'Usuario', width: '120px', render: (r: Record<string, unknown>) => String(r.usuarioCode ?? '') },
    { key: 'usuarioName', header: 'Nombre usuario', width: '180px', render: (r: Record<string, unknown>) => String(r.usuarioName ?? '') },
    { key: 'nombreEmpresa', header: 'Empresa', width: '180px', render: (r: Record<string, unknown>) => String(r.nombreEmpresa ?? '') },
    { key: 'nombreRol', header: 'Rol', width: '120px', render: (r: Record<string, unknown>) => String(r.nombreRol ?? '') },
  ];

  if (error) {
    return (
      <div className="admin-page" data-testid="permisos.admin">
        <h1>Permisos</h1>
        <div className="admin-error" role="alert">
          {error}
        </div>
      </div>
    );
  }

  return (
    <div className="admin-page" data-testid="permisos.admin">
      <div className="admin-page-header">
        <h1>Permisos</h1>
        <button type="button" className="admin-btn-primary" data-testid="permisos.create">
          Asignar permiso
        </button>
      </div>
      <div data-testid="permisos.grid">
        <DataGridDX
          testId="permisos.grid"
          data={data}
          columns={columns}
          loading={loading}
          emptyMessage="No hay permisos asignados"
        />
      </div>
    </div>
  );
}
