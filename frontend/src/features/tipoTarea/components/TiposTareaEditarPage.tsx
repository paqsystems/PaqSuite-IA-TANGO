/**
 * TiposTareaEditarPage – Formulario de edición de tipo de tarea. TR-025(MH).
 * Código solo lectura. Si "por defecto" se marca, "genérico" se fuerza y se deshabilita.
 */
import React, { useState, useEffect, useCallback, FormEvent } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { getTipoTarea, updateTipoTarea, ERROR_YA_HAY_POR_DEFECTO } from '../services/tipoTarea.service';
import './TiposTareaPage.css';

export function TiposTareaEditarPage(): React.ReactElement {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const tipoId = id ? parseInt(id, 10) : NaN;
  const [code, setCode] = useState('');
  const [descripcion, setDescripcion] = useState('');
  const [isGenerico, setIsGenerico] = useState(false);
  const [isDefault, setIsDefault] = useState(false);
  const [activo, setActivo] = useState(true);
  const [inhabilitado, setInhabilitado] = useState(false);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});

  const handlePorDefectoChange = (checked: boolean) => {
    setIsDefault(checked);
    if (checked) setIsGenerico(true);
  };

  const loadTipo = useCallback(async (tid: number) => {
    setLoading(true);
    setError('');
    const result = await getTipoTarea(tid);
    setLoading(false);
    if (result.success && result.data) {
      setCode(result.data.code);
      setDescripcion(result.data.descripcion);
      setIsGenerico(result.data.is_generico);
      setIsDefault(result.data.is_default);
      setActivo(result.data.activo);
      setInhabilitado(result.data.inhabilitado);
    } else {
      setError(result.errorMessage ?? 'Error al cargar tipo de tarea');
    }
  }, []);

  useEffect(() => {
    if (Number.isNaN(tipoId)) {
      setError('ID inválido');
      setLoading(false);
      return;
    }
    loadTipo(tipoId);
  }, [tipoId, loadTipo]);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError('');
    setFieldErrors({});
    if (!descripcion.trim()) {
      setFieldErrors({ descripcion: 'La descripción es obligatoria.' });
      return;
    }
    setSaving(true);
    const result = await updateTipoTarea(tipoId, {
      descripcion: descripcion.trim(),
      is_generico: isDefault ? true : isGenerico,
      is_default: isDefault,
      activo,
      inhabilitado,
    });
    setSaving(false);
    if (result.success) {
      navigate('/tipos-tarea');
    } else {
      setError(
        result.errorCode === ERROR_YA_HAY_POR_DEFECTO
          ? 'Solo puede haber un tipo de tarea por defecto. Ya existe otro.'
          : (result.errorMessage ?? 'Error al actualizar')
      );
    }
  };

  if (loading) {
    return <div className="tipos-tarea-page"><p className="tipos-tarea-page-loading">Cargando...</p></div>;
  }

  if (error && !code) {
    return (
      <div className="tipos-tarea-page">
        <p className="tipos-tarea-page-error">{error}</p>
        <button type="button" className="tipos-tarea-btn-cancel" onClick={() => navigate('/tipos-tarea')}>Volver</button>
      </div>
    );
  }

  return (
    <div className="tipos-tarea-page">
      <header className="tipos-tarea-page-header">
        <h1 className="tipos-tarea-page-title">Editar tipo de tarea</h1>
      </header>
      <form onSubmit={handleSubmit} className="tipos-tarea-form" data-testid="tipoTareaEditar.form">
        {error && <div className="tipos-tarea-page-error" role="alert">{error}</div>}
        <div className="tipos-tarea-form-group">
          <label className="tipos-tarea-form-label">Código</label>
          <input type="text" value={code} readOnly disabled className="readonly" data-testid="tipoTareaEditar.code" />
        </div>
        <div className="tipos-tarea-form-group">
          <label htmlFor="descripcion" className="tipos-tarea-form-label">Descripción</label>
          <input
            id="descripcion"
            type="text"
            value={descripcion}
            onChange={(e) => setDescripcion(e.target.value)}
            disabled={saving}
            className={fieldErrors.descripcion ? 'input-error' : ''}
            data-testid="tipoTareaEditar.descripcion"
            maxLength={255}
          />
          {fieldErrors.descripcion && <span className="field-error">{fieldErrors.descripcion}</span>}
        </div>
        <div className="tipos-tarea-form-group checkbox">
          <label>
            <input
              type="checkbox"
              checked={isGenerico}
              onChange={(e) => setIsGenerico(e.target.checked)}
              disabled={saving || isDefault}
            />
            Genérico
          </label>
        </div>
        <div className="tipos-tarea-form-group checkbox">
          <label>
            <input
              type="checkbox"
              checked={isDefault}
              onChange={(e) => handlePorDefectoChange(e.target.checked)}
              disabled={saving}
            />
            Por defecto
          </label>
        </div>
        <div className="tipos-tarea-form-group checkbox">
          <label>
            <input type="checkbox" checked={activo} onChange={(e) => setActivo(e.target.checked)} disabled={saving} />
            Activo
          </label>
        </div>
        <div className="tipos-tarea-form-group checkbox">
          <label>
            <input type="checkbox" checked={inhabilitado} onChange={(e) => setInhabilitado(e.target.checked)} disabled={saving} />
            Inhabilitado
          </label>
        </div>
        <div className="tipos-tarea-form-actions">
          <button type="button" className="tipos-tarea-btn-cancel" onClick={() => navigate('/tipos-tarea')}>Cancelar</button>
          <button type="submit" disabled={saving} data-testid="tipoTareaEditar.submit" className="tipos-tarea-btn-submit">
            {saving ? 'Guardando...' : 'Guardar'}
          </button>
        </div>
      </form>
    </div>
  );
}
