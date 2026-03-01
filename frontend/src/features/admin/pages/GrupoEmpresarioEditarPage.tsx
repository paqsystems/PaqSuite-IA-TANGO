/**
 * Página de edición de grupo empresario.
 * @see docs/04-tareas/002-GruposEmpresarios/TR-003-edicion-grupo-empresario.md
 */

import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { TagBox } from 'devextreme-react/tag-box';
import { adminGruposEmpresariosApi, adminEmpresasApi } from '../services/admin.service';
import './AdminPage.css';

export function GrupoEmpresarioEditarPage(): React.ReactElement {
  const navigate = useNavigate();
  const { id } = useParams<{ id: string }>();
  const [descripcion, setDescripcion] = useState('');
  const [empresaIds, setEmpresaIds] = useState<number[]>([]);
  const [empresas, setEmpresas] = useState<{ id: number; nombreEmpresa: string }[]>([]);
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
    Promise.all([
      adminGruposEmpresariosApi.get(idNum),
      adminEmpresasApi.list({ habilita: '1' }),
    ])
      .then(([grupoRes, empresasRes]) => {
        const grupo = grupoRes.resultado as { descripcion?: string; empresaIds?: number[] };
        const items = (empresasRes.resultado as { items?: { id: number; nombreEmpresa: string }[] }).items ?? [];
        setDescripcion(grupo.descripcion ?? '');
        setEmpresaIds(grupo.empresaIds ?? []);
        setEmpresas(items);
      })
      .catch(() => {
        setError('Error al cargar grupo');
        navigate('/admin/grupos-empresarios');
      })
      .finally(() => setLoading(false));
  }, [id, navigate]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!id) return;
    setError(null);
    const desc = descripcion.trim();
    if (!desc) {
      setError('La descripción es obligatoria.');
      return;
    }
    if (empresaIds.length === 0) {
      setError('Debe conservar al menos una empresa.');
      return;
    }
    setSubmitting(true);
    adminGruposEmpresariosApi
      .update(parseInt(id, 10), { descripcion: desc, empresaIds })
      .then(() => {
        navigate(`/admin/grupos-empresarios/${id}`);
      })
      .catch((err) => {
        setError(err.respuesta ?? err.resultado?.errors ? 'Errores de validación' : 'Error al actualizar grupo');
        setSubmitting(false);
      });
  };

  if (loading) {
    return (
      <div className="admin-page" data-testid="grupos-empresarios.editar">
        <p>Cargando...</p>
      </div>
    );
  }

  return (
    <div className="admin-page" data-testid="grupos-empresarios.editar">
      <div className="admin-page-header">
        <h1>Editar grupo empresario</h1>
        <button
          type="button"
          className="admin-btn-primary"
          onClick={() => navigate(`/admin/grupos-empresarios/${id}`)}
        >
          Volver al detalle
        </button>
      </div>
      <form
        onSubmit={handleSubmit}
        className="grupo-empresario-form"
        data-testid="grupoEmpresario.edit.form"
      >
        {error && (
          <div className="admin-error" role="alert">
            {error}
          </div>
        )}
        <div className="form-group">
          <label htmlFor="descripcion">Descripción</label>
          <input
            id="descripcion"
            type="text"
            value={descripcion}
            onChange={(e) => setDescripcion(e.target.value)}
            maxLength={100}
            placeholder="Ej: Grupo Norte"
            disabled={loading}
          />
        </div>
        <div className="form-group">
          <label htmlFor="empresas">Empresas (al menos una)</label>
          <div>
            <TagBox
              dataSource={empresas}
              value={empresaIds}
              onValueChange={(ids: number[]) => setEmpresaIds(ids ?? [])}
              valueExpr="id"
              displayExpr="nombreEmpresa"
              showSelectionControls={true}
              searchEnabled={true}
              placeholder="Seleccione empresas..."
              disabled={loading}
            />
          </div>
        </div>
        <div className="form-actions">
          <button
            type="submit"
            className="admin-btn-primary"
            data-testid="grupoEmpresario.edit.submit"
            disabled={loading || submitting}
          >
            {submitting ? 'Guardando...' : 'Guardar'}
          </button>
        </div>
      </form>
    </div>
  );
}
