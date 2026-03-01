/**
 * Página de administración de empresas.
 * @see docs/04-tareas/001-Seguridad/TR-011-administracion-empresas.md
 */

import React, { useEffect, useState } from 'react';
import { adminEmpresasApi } from '../services/admin.service';
import { DataGridDX } from '../../../shared/ui/DataGridDX';
import type { Column } from '../../../shared/ui/DataTable/DataTable';
import './AdminPage.css';

export function EmpresasAdminPage(): React.ReactElement {
  const [data, setData] = useState<Record<string, unknown>[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    adminEmpresasApi
      .list()
      .then((res) => {
        const items = (res.resultado as { items?: Record<string, unknown>[] }).items ?? [];
        setData(items);
      })
      .catch((err) => setError(err.respuesta ?? 'Error al cargar empresas'))
      .finally(() => setLoading(false));
  }, []);

  const columns: Column<Record<string, unknown>>[] = [
    { key: 'id', header: 'ID', width: '80px', render: (r: Record<string, unknown>) => String(r.id ?? '') },
    { key: 'nombreEmpresa', header: 'Nombre', width: '200px', render: (r: Record<string, unknown>) => String(r.nombreEmpresa ?? '') },
    { key: 'nombreBd', header: 'Base de datos', width: '180px', render: (r: Record<string, unknown>) => String(r.nombreBd ?? '') },
    { key: 'theme', header: 'Theme', width: '100px', render: (r: Record<string, unknown>) => String(r.theme ?? '') },
    { key: 'habilita', header: 'Habilitada', width: '100px', render: (row: Record<string, unknown>) => (row.habilita === 1 || row.habilita === null ? 'Sí' : 'No') },
  ];

  if (error) {
    return (
      <div className="admin-page" data-testid="empresas.admin">
        <h1>Empresas</h1>
        <div className="admin-error" role="alert">
          {error}
        </div>
      </div>
    );
  }

  return (
    <div className="admin-page" data-testid="empresas.admin">
      <div className="admin-page-header">
        <h1>Empresas</h1>
        <button type="button" className="admin-btn-primary" data-testid="empresas.create">
          Crear empresa
        </button>
      </div>
      <div data-testid="empresas.grid">
        <DataGridDX
          testId="empresas.grid"
          data={data}
          columns={columns}
          loading={loading}
          emptyMessage="No hay empresas"
        />
      </div>
    </div>
  );
}
