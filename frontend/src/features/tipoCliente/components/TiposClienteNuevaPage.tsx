/**
 * TiposClienteNuevaPage – Formulario de creación de tipo de cliente. TR-015(MH).
 */
import React, { useState, FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import { createTipoCliente } from '../services/tipoCliente.service';
import './TiposClientePage.css';

export function TiposClienteNuevaPage(): React.ReactElement {
  const navigate = useNavigate();
  const [code, setCode] = useState('');
  const [descripcion, setDescripcion] = useState('');
  const [activo, setActivo] = useState(true);
  const [inhabilitado, setInhabilitado] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});

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
    const result = await createTipoCliente({ code: code.trim(), descripcion: descripcion.trim(), activo, inhabilitado });
    setLoading(false);
    if (result.success) {
      navigate('/tipos-cliente');
    } else {
      setError(result.errorMessage ?? 'Error al crear tipo de cliente');
    }
  };

  return (
    <div className="tipos-cliente-page">
      <header className="tipos-cliente-page-header">
        <h1 className="tipos-cliente-page-title">Nuevo tipo de cliente</h1>
      </header>
      <form onSubmit={handleSubmit} className="tipos-cliente-form" data-testid="tipoClienteCrear.form">
        {error && <div className="tipos-cliente-page-error" role="alert">{error}</div>}
        <div className="tipos-cliente-form-group">
          <label htmlFor="code" className="tipos-cliente-form-label">Código</label>
          <input
            id="code"
            type="text"
            value={code}
            onChange={(e) => setCode(e.target.value)}
            disabled={loading}
            className={fieldErrors.code ? 'input-error' : ''}
            data-testid="tipoClienteCrear.code"
            maxLength={50}
          />
          {fieldErrors.code && <span className="field-error">{fieldErrors.code}</span>}
        </div>
        <div className="tipos-cliente-form-group">
          <label htmlFor="descripcion" className="tipos-cliente-form-label">Descripción</label>
          <input
            id="descripcion"
            type="text"
            value={descripcion}
            onChange={(e) => setDescripcion(e.target.value)}
            disabled={loading}
            className={fieldErrors.descripcion ? 'input-error' : ''}
            data-testid="tipoClienteCrear.descripcion"
            maxLength={255}
          />
          {fieldErrors.descripcion && <span className="field-error">{fieldErrors.descripcion}</span>}
        </div>
        <div className="tipos-cliente-form-group checkbox">
          <label>
            <input type="checkbox" checked={activo} onChange={(e) => setActivo(e.target.checked)} disabled={loading} />
            Activo
          </label>
        </div>
        <div className="tipos-cliente-form-group checkbox">
          <label>
            <input type="checkbox" checked={inhabilitado} onChange={(e) => setInhabilitado(e.target.checked)} disabled={loading} />
            Inhabilitado
          </label>
        </div>
        <div className="tipos-cliente-form-actions">
          <button type="button" className="tipos-cliente-btn-cancel" onClick={() => navigate('/tipos-cliente')}>Cancelar</button>
          <button type="submit" disabled={loading} data-testid="tipoClienteCrear.submit" className="tipos-cliente-btn-submit">
            {loading ? 'Guardando...' : 'Guardar'}
          </button>
        </div>
      </form>
    </div>
  );
}
