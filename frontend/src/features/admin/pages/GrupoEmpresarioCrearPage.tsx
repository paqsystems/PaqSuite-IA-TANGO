/**
 * Página de creación de grupo empresario.
 * @see docs/04-tareas/002-GruposEmpresarios/TR-002-creacion-grupo-empresario.md
 */

import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { TagBox } from 'devextreme-react/tag-box';
import { adminGruposEmpresariosApi, adminEmpresasApi } from '../services/admin.service';
import './AdminPage.css';

export function GrupoEmpresarioCrearPage(): React.ReactElement {
  const navigate = useNavigate();
  const [descripcion, setDescripcion] = useState('');
  const [empresaIds, setEmpresaIds] = useState<number[]>([]);
  const [empresas, setEmpresas] = useState<{ id: number; nombreEmpresa: string }[]>([]);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    adminEmpresasApi
      .list({ habilita: '1' })
      .then((res) => {
        const items = (res.resultado as { items?: { id: number; nombreEmpresa: string }[] }).items ?? [];
        setEmpresas(items);
      })
      .catch(() => setEmpresas([]))
      .finally(() => setLoading(false));
  }, []);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    const desc = descripcion.trim();
    if (!desc) {
      setError('La descripción es obligatoria.');
      return;
    }
    if (empresaIds.length === 0) {
      setError('Debe seleccionar al menos una empresa.');
      return;
    }
    setSubmitting(true);
    adminGruposEmpresariosApi
      .create({ descripcion: desc, empresaIds })
      .then(() => {
        navigate('/admin/grupos-empresarios');
      })
      .catch((err) => {
        setError(err.respuesta ?? err.resultado?.errors ? 'Errores de validación' : 'Error al crear grupo');
        setSubmitting(false);
      });
  };

  return (
    <div className="admin-page" data-testid="grupos-empresarios.crear">
      <div className="admin-page-header">
        <h1>Crear grupo empresario</h1>
        <button
          type="button"
          className="admin-btn-primary"
          onClick={() => navigate('/admin/grupos-empresarios')}
        >
          Volver al listado
        </button>
      </div>
      <form
        onSubmit={handleSubmit}
        className="grupo-empresario-form"
        data-testid="grupoEmpresario.create.form"
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
            data-testid="grupoEmpresario.create.descripcion"
            disabled={loading}
          />
        </div>
        <div className="form-group">
          <label htmlFor="empresas">Empresas (al menos una)</label>
          <div data-testid="grupoEmpresario.create.empresas">
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
            data-testid="grupoEmpresario.create.submit"
            disabled={loading || submitting}
          >
            {submitting ? 'Guardando...' : 'Guardar'}
          </button>
        </div>
      </form>
    </div>
  );
}
