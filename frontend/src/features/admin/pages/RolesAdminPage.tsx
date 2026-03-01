/**
 * Página de administración de roles.
 * @see docs/04-tareas/001-Seguridad/TR-012-administracion-roles.md
 */

import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { adminRolesApi } from '../services/admin.service';
import { DataGridDX } from '../../../shared/ui/DataGridDX';
import type { Column } from '../../../shared/ui/DataTable/DataTable';
import './AdminPage.css';

export function RolesAdminPage(): React.ReactElement {
  const navigate = useNavigate();
  const [data, setData] = useState<Record<string, unknown>[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    adminRolesApi
      .list()
      .then((res) => {
        const items = (res.resultado as { items?: Record<string, unknown>[] }).items ?? [];
        setData(items);
      })
      .catch((err) => setError(err.respuesta ?? 'Error al cargar roles'))
      .finally(() => setLoading(false));
  }, []);

  const columns: Column<Record<string, unknown>>[] = [
    { key: 'id', header: 'ID', width: '80px', render: (r: Record<string, unknown>) => String(r.id ?? '') },
    { key: 'nombreRol', header: 'Nombre', width: '150px', render: (r: Record<string, unknown>) => String(r.nombreRol ?? '') },
    { key: 'descripcionRol', header: 'Descripción', width: '200px', render: (r: Record<string, unknown>) => String(r.descripcionRol ?? '') },
    { key: 'accesoTotal', header: 'Acceso total', width: '120px', render: (row: Record<string, unknown>) => (row.accesoTotal ? 'Sí' : 'No') },
    {
      key: 'acciones',
      header: 'Acciones',
      width: '120px',
      sortable: false,
      render: (r: Record<string, unknown>) => (
        <button
          type="button"
          className="admin-btn-link"
          onClick={() => navigate(`/admin/roles/${r.id}/atributos`)}
        >
          Atributos
        </button>
      ),
    },
  ];

  if (error) {
    return (
      <div className="admin-page" data-testid="roles.admin">
        <h1>Roles</h1>
        <div className="admin-error" role="alert">
          {error}
        </div>
      </div>
    );
  }

  return (
    <div className="admin-page" data-testid="roles.admin">
      <div className="admin-page-header">
        <h1>Roles</h1>
        <button type="button" className="admin-btn-primary" data-testid="roles.create">
          Crear rol
        </button>
      </div>
      <div data-testid="roles.grid">
        <DataGridDX
          testId="roles.grid"
          data={data}
          columns={columns}
          loading={loading}
          emptyMessage="No hay roles"
        />
      </div>
    </div>
  );
}
