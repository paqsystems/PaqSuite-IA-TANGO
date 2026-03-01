/**
 * Página de atributos de rol (permisos granulares por opción de menú).
 * @see docs/04-tareas/001-Seguridad/TR-014-administracion-atributos-rol.md
 */

import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { adminRolesApi } from '../services/admin.service';
import { DataGridDX } from '../../../shared/ui/DataGridDX';
import type { Column } from '../../../shared/ui/DataTable/DataTable';
import './AdminPage.css';

interface AtributoItem {
  idOpcionMenu: number;
  text: string;
  procedimiento: string | null;
  permisoAlta: boolean;
  permisoBaja: boolean;
  permisoModi: boolean;
  permisoRepo: boolean;
}

export function RolAtributosPage(): React.ReactElement {
  const navigate = useNavigate();
  const { id } = useParams<{ id: string }>();
  const [rol, setRol] = useState<{ id: number; nombreRol: string; accesoTotal: boolean } | null>(null);
  const [items, setItems] = useState<AtributoItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!id) return;
    const idNum = parseInt(id, 10);
    if (isNaN(idNum)) {
      setError('ID inválido');
      setLoading(false);
      return;
    }
    adminRolesApi
      .getAtributos(idNum)
      .then((res) => {
        const r = res.resultado as { rol?: { id: number; nombreRol: string; accesoTotal: boolean }; items?: AtributoItem[] };
        setRol(r.rol ?? null);
        setItems(r.items ?? []);
      })
      .catch(() => {
        setError('Error al cargar atributos');
        navigate('/admin/roles');
      })
      .finally(() => setLoading(false));
  }, [id, navigate]);

  const handleToggle = (idOpcionMenu: number, field: keyof Pick<AtributoItem, 'permisoAlta' | 'permisoBaja' | 'permisoModi' | 'permisoRepo'>) => {
    setItems((prev) =>
      prev.map((it) =>
        it.idOpcionMenu === idOpcionMenu ? { ...it, [field]: !it[field] } : it
      )
    );
  };

  const handleGuardar = () => {
    if (!id || !rol) return;
    if (rol.accesoTotal) {
      setError('Los roles con acceso total no requieren atributos.');
      return;
    }
    setSubmitting(true);
    setError(null);
    adminRolesApi
      .updateAtributos(parseInt(id, 10), {
        items: items.map((it) => ({
          idOpcionMenu: it.idOpcionMenu,
          permisoAlta: it.permisoAlta,
          permisoBaja: it.permisoBaja,
          permisoModi: it.permisoModi,
          permisoRepo: it.permisoRepo,
        })),
      })
      .then(() => setSubmitting(false))
      .catch((err) => {
        setError(err.respuesta ?? 'Error al guardar');
        setSubmitting(false);
      });
  };

  if (loading) {
    return (
      <div className="admin-page" data-testid="rol-atributos.page">
        <p>Cargando...</p>
      </div>
    );
  }

  if (error && !rol) {
    return (
      <div className="admin-page" data-testid="rol-atributos.page">
        <div className="admin-error">{error}</div>
      </div>
    );
  }

  if (rol?.accesoTotal) {
    return (
      <div className="admin-page" data-testid="rol-atributos.page">
        <div className="admin-page-header">
          <h1>Atributos de rol: {rol.nombreRol}</h1>
          <button type="button" className="admin-btn-primary" onClick={() => navigate('/admin/roles')}>
            Volver a roles
          </button>
        </div>
        <p>Este rol tiene acceso total. No requiere atributos granulares.</p>
      </div>
    );
  }

  const columns: Column<Record<string, unknown>>[] = [
    { key: 'text', header: 'Opción menú', width: '200px', render: (r: Record<string, unknown>) => String(r.text ?? '') },
    { key: 'procedimiento', header: 'Procedimiento', width: '150px', render: (r: Record<string, unknown>) => String(r.procedimiento ?? '') },
    {
      key: 'permisoAlta',
      header: 'Alta',
      width: '80px',
      sortable: false,
      render: (r: Record<string, unknown>) => (
        <input
          type="checkbox"
          checked={!!r.permisoAlta}
          onChange={() => handleToggle(Number(r.idOpcionMenu), 'permisoAlta')}
        />
      ),
    },
    {
      key: 'permisoBaja',
      header: 'Baja',
      width: '80px',
      sortable: false,
      render: (r: Record<string, unknown>) => (
        <input
          type="checkbox"
          checked={!!r.permisoBaja}
          onChange={() => handleToggle(Number(r.idOpcionMenu), 'permisoBaja')}
        />
      ),
    },
    {
      key: 'permisoModi',
      header: 'Modi',
      width: '80px',
      sortable: false,
      render: (r: Record<string, unknown>) => (
        <input
          type="checkbox"
          checked={!!r.permisoModi}
          onChange={() => handleToggle(Number(r.idOpcionMenu), 'permisoModi')}
        />
      ),
    },
    {
      key: 'permisoRepo',
      header: 'Repo',
      width: '80px',
      sortable: false,
      render: (r: Record<string, unknown>) => (
        <input
          type="checkbox"
          checked={!!r.permisoRepo}
          onChange={() => handleToggle(Number(r.idOpcionMenu), 'permisoRepo')}
        />
      ),
    },
  ];

  const gridData = items as unknown as Record<string, unknown>[];

  return (
    <div className="admin-page" data-testid="rol-atributos.page">
      <div className="admin-page-header">
        <h1>Atributos de rol: {rol?.nombreRol}</h1>
        <div className="admin-page-actions">
          <button type="button" className="admin-btn-primary" onClick={() => navigate('/admin/roles')}>
            Volver a roles
          </button>
          <button
            type="button"
            className="admin-btn-primary"
            data-testid="rolAtributos.edit"
            onClick={handleGuardar}
            disabled={submitting}
          >
            {submitting ? 'Guardando...' : 'Guardar'}
          </button>
        </div>
      </div>
      {error && (
        <div className="admin-error" role="alert">
          {error}
        </div>
      )}
      <div data-testid="rolAtributos.grid">
        <DataGridDX
          testId="rolAtributos.grid"
          data={gridData}
          columns={columns}
          loading={false}
          emptyMessage="No hay opciones de menú"
        />
      </div>
    </div>
  );
}
