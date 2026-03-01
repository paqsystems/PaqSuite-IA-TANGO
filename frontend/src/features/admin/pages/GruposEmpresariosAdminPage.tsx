/**
 * Página de administración de grupos empresarios.
 * @see docs/04-tareas/002-GruposEmpresarios/TR-001-listado-grupos-empresarios.md
 */

import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { adminGruposEmpresariosApi } from '../services/admin.service';
import { DataGridDX } from '../../../shared/ui/DataGridDX';
import type { Column } from '../../../shared/ui/DataTable/DataTable';
import './AdminPage.css';

export function GruposEmpresariosAdminPage(): React.ReactElement {
  const navigate = useNavigate();
  const [data, setData] = useState<Record<string, unknown>[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    adminGruposEmpresariosApi
      .list()
      .then((res) => {
        const items = (res.resultado as { items?: Record<string, unknown>[] }).items ?? [];
        setData(items);
      })
      .catch((err) => setError(err.respuesta ?? 'Error al cargar grupos empresarios'))
      .finally(() => setLoading(false));
  }, []);

  const columns: Column<Record<string, unknown>>[] = [
    { key: 'id', header: 'ID', width: '80px', render: (r: Record<string, unknown>) => String(r.id ?? '') },
    { key: 'descripcion', header: 'Descripción', width: '300px', render: (r: Record<string, unknown>) => String(r.descripcion ?? '') },
    { key: 'cantidadEmpresas', header: 'Cant. empresas', width: '120px', render: (r: Record<string, unknown>) => String(r.cantidadEmpresas ?? 0) },
    {
      key: 'acciones',
      header: 'Acciones',
      width: '200px',
      sortable: false,
      render: (r: Record<string, unknown>) => (
        <span key={`acc-${r.id}`}>
          <button
            type="button"
            className="admin-btn-link"
            onClick={() => navigate(`/admin/grupos-empresarios/${r.id}`)}
          >
            Ver
          </button>
          {' | '}
          <button
            type="button"
            className="admin-btn-link"
            onClick={() => navigate(`/admin/grupos-empresarios/${r.id}/editar`)}
          >
            Editar
          </button>
          {' | '}
          <button
            type="button"
            className="admin-btn-link admin-btn-link-danger"
            data-testid="grupoEmpresario.delete"
            onClick={() => {
              if (window.confirm(`¿Está seguro de eliminar el grupo "${r.descripcion}"?`)) {
                adminGruposEmpresariosApi
                  .delete(Number(r.id))
                  .then(() => {
                    setData((prev) => prev.filter((item) => item.id !== r.id));
                  })
                  .catch((err) => {
                    if (err.status === 409) {
                      alert(err.respuesta ?? 'El grupo tiene dependencias y no puede eliminarse.');
                    } else {
                      alert(err.respuesta ?? 'Error al eliminar.');
                    }
                  });
              }
            }}
          >
            Eliminar
          </button>
        </span>
      ),
    },
  ];

  if (error) {
    return (
      <div className="admin-page" data-testid="grupos-empresarios.admin">
        <h1>Grupos empresarios</h1>
        <div className="admin-error" role="alert">
          {error}
        </div>
      </div>
    );
  }

  return (
    <div className="admin-page" data-testid="grupos-empresarios.admin">
      <div className="admin-page-header">
        <h1>Grupos empresarios</h1>
        <div className="admin-page-actions">
          <button
            type="button"
            className="admin-btn-primary"
            data-testid="grupos-empresarios.create"
            onClick={() => navigate('/admin/grupos-empresarios/crear')}
          >
            Crear
          </button>
        </div>
      </div>
      <div data-testid="grupos-empresarios.grid">
        <DataGridDX
          testId="grupos-empresarios.grid"
          data={data}
          columns={columns}
          loading={loading}
          emptyMessage="No hay grupos empresarios"
        />
      </div>
    </div>
  );
}
