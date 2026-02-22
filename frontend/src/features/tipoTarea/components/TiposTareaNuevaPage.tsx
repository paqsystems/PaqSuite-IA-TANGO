/**
 * TiposTareaNuevaPage – Formulario de creación de tipo de tarea. TR-024(MH).
 * Si "por defecto" se marca, "genérico" se fuerza a true y se deshabilita.
 */
import React, { useState, FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import { createTipoTarea, ERROR_YA_HAY_POR_DEFECTO } from '../services/tipoTarea.service';
import './TiposTareaPage.css';

export function TiposTareaNuevaPage(): React.ReactElement {
  const navigate = useNavigate();
  const [code, setCode] = useState('');
  const [descripcion, setDescripcion] = useState('');
  const [isGenerico, setIsGenerico] = useState(false);
  const [isDefault, setIsDefault] = useState(false);
  const [activo, setActivo] = useState(true);
  const [inhabilitado, setInhabilitado] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});

  const handlePorDefectoChange = (checked: boolean) => {
    setIsDefault(checked);
    if (checked) setIsGenerico(true);
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError('');
    setFieldErrors({});
    const err: Record<string, string> = {};
    if (!code.trim()) err.code = 'El código es obligatorio.';
    if (!descripcion.trim()) err.descripcion = 'La descripción es obligatoria.';
    if (Object.keys(err).length > 0) {
      setFieldErrors(err);
      return;
    }
    setLoading(true);
    const result = await createTipoTarea({
      code: code.trim(),
      descripcion: descripcion.trim(),
      is_generico: isDefault ? true : isGenerico,
      is_default: isDefault,
      activo,
      inhabilitado,
    });
    setLoading(false);
    if (result.success) {
      navigate('/tipos-tarea');
    } else {
      setError(
        result.errorCode === ERROR_YA_HAY_POR_DEFECTO
          ? 'Solo puede haber un tipo de tarea por defecto. Ya existe otro.'
          : (result.errorMessage ?? 'Error al crear tipo de tarea')
      );
    }
  };

  return (
    <div className="tipos-tarea-page">
      <header className="tipos-tarea-page-header">
        <h1 className="tipos-tarea-page-title">Nuevo tipo de tarea</h1>
      </header>
      <form onSubmit={handleSubmit} className="tipos-tarea-form" data-testid="tipoTareaCrear.form">
        {error && <div className="tipos-tarea-page-error" role="alert">{error}</div>}
        <div className="tipos-tarea-form-group">
          <label htmlFor="code" className="tipos-tarea-form-label">Código</label>
          <input
            id="code"
            type="text"
            value={code}
            onChange={(e) => setCode(e.target.value)}
            disabled={loading}
            className={fieldErrors.code ? 'input-error' : ''}
            data-testid="tipoTareaCrear.code"
            maxLength={50}
          />
          {fieldErrors.code && <span className="field-error">{fieldErrors.code}</span>}
        </div>
        <div className="tipos-tarea-form-group">
          <label htmlFor="descripcion" className="tipos-tarea-form-label">Descripción</label>
          <input
            id="descripcion"
            type="text"
            value={descripcion}
            onChange={(e) => setDescripcion(e.target.value)}
            disabled={loading}
            className={fieldErrors.descripcion ? 'input-error' : ''}
            data-testid="tipoTareaCrear.descripcion"
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
              disabled={loading || isDefault}
              data-testid="tipoTareaCrear.generico"
            />
            Genérico
          </label>
          {isDefault && <span className="tipos-tarea-form-label" style={{ marginLeft: '0.5rem', color: '#6b7280' }}>(obligatorio si es por defecto)</span>}
        </div>
        <div className="tipos-tarea-form-group checkbox">
          <label>
            <input
              type="checkbox"
              checked={isDefault}
              onChange={(e) => handlePorDefectoChange(e.target.checked)}
              disabled={loading}
              data-testid="tipoTareaCrear.porDefecto"
            />
            Por defecto
          </label>
        </div>
        <div className="tipos-tarea-form-group checkbox">
          <label>
            <input type="checkbox" checked={activo} onChange={(e) => setActivo(e.target.checked)} disabled={loading} />
            Activo
          </label>
        </div>
        <div className="tipos-tarea-form-group checkbox">
          <label>
            <input type="checkbox" checked={inhabilitado} onChange={(e) => setInhabilitado(e.target.checked)} disabled={loading} />
            Inhabilitado
          </label>
        </div>
        <div className="tipos-tarea-form-actions">
          <button type="button" className="tipos-tarea-btn-cancel" onClick={() => navigate('/tipos-tarea')}>Cancelar</button>
          <button type="submit" disabled={loading} data-testid="tipoTareaCrear.submit" className="tipos-tarea-btn-submit">
            {loading ? 'Guardando...' : 'Guardar'}
          </button>
        </div>
      </form>
    </div>
  );
}
