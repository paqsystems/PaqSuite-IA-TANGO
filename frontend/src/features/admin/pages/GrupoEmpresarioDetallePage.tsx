/**
 * Página de detalle de grupo empresario (solo lectura).
 * @see docs/04-tareas/002-GruposEmpresarios/TR-005-detalle-grupo-empresario.md
 */

import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { adminGruposEmpresariosApi } from '../services/admin.service';
import './AdminPage.css';

export function GrupoEmpresarioDetallePage(): React.ReactElement {
  const navigate = useNavigate();
  const { id } = useParams<{ id: string }>();
  const [grupo, setGrupo] = useState<{ id: number; descripcion: string; empresas: { id: number; nombreEmpresa: string }[] } | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!id) return;
    const idNum = parseInt(id, 10);
    if (isNaN(idNum)) {
      setError('ID inválido');
      setLoading(false);
      return;
    }
    adminGruposEmpresariosApi
      .get(idNum)
      .then((res) => {
        const r = res.resultado as { id: number; descripcion: string; empresas?: { id: number; nombreEmpresa: string }[] };
        setGrupo({
          id: r.id,
          descripcion: r.descripcion,
          empresas: r.empresas ?? [],
        });
      })
      .catch(() => {
        setError('Grupo no encontrado');
        navigate('/admin/grupos-empresarios');
      })
      .finally(() => setLoading(false));
  }, [id, navigate]);

  if (loading) {
    return (
      <div className="admin-page" data-testid="grupos-empresarios.detalle">
        <p>Cargando...</p>
      </div>
    );
  }

  if (error || !grupo) {
    return (
      <div className="admin-page" data-testid="grupos-empresarios.detalle">
        <div className="admin-error">{error ?? 'Error'}</div>
      </div>
    );
  }

  return (
    <div className="admin-page" data-testid="grupos-empresarios.detalle">
      <div className="admin-page-header">
        <h1>Detalle grupo empresario</h1>
        <div className="admin-page-actions">
          <button
            type="button"
            className="admin-btn-primary"
            onClick={() => navigate('/admin/grupos-empresarios')}
          >
            Volver al listado
          </button>
          <button
            type="button"
            className="admin-btn-primary"
            onClick={() => navigate(`/admin/grupos-empresarios/${id}/editar`)}
          >
            Editar
          </button>
          <button
            type="button"
            className="admin-btn-primary admin-btn-danger"
            data-testid="grupoEmpresario.delete"
            onClick={() => {
              if (window.confirm(`¿Está seguro de eliminar el grupo "${grupo.descripcion}"?`)) {
                adminGruposEmpresariosApi
                  .delete(grupo.id)
                  .then(() => navigate('/admin/grupos-empresarios'))
                  .catch((err) => {
                    if (err.status === 409) {
                      setError(err.respuesta ?? 'El grupo tiene dependencias y no puede eliminarse.');
                    } else {
                      setError(err.respuesta ?? 'Error al eliminar.');
                    }
                  });
              }
            }}
          >
            Eliminar
          </button>
        </div>
      </div>
      <div className="grupo-detalle">
        <dl>
          <dt>ID</dt>
          <dd>{grupo.id}</dd>
          <dt>Descripción</dt>
          <dd>{grupo.descripcion}</dd>
          <dt>Empresas ({grupo.empresas.length})</dt>
          <dd>
            <ul className="grupo-empresas-list">
              {grupo.empresas.map((e) => (
                <li key={e.id}>{e.nombreEmpresa}</li>
              ))}
            </ul>
          </dd>
        </dl>
      </div>
    </div>
  );
}
