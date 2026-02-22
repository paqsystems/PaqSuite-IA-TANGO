/**
 * Component: ClientesNuevaPage
 *
 * Formulario de creación de cliente (solo supervisores). TR-009(MH).
 * Ruta: /clientes/nueva. Campos: código, nombre, tipo cliente, email,
 * habilitar acceso (checkbox), contraseña condicional, activo, inhabilitado.
 *
 * @see TR-009(MH)-creación-de-cliente.md
 */

import React, { useState, useEffect, useCallback, FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  createCliente,
  getTiposCliente,
  CreateClienteBody,
  TipoClienteItem,
} from '../services/client.service';
import './ClientesNuevaPage.css';

type FormState = 'initial' | 'loading' | 'error' | 'success';

export function ClientesNuevaPage(): React.ReactElement {
  const navigate = useNavigate();
  const [code, setCode] = useState('');
  const [nombre, setNombre] = useState('');
  const [tipoClienteId, setTipoClienteId] = useState<number | null>(null);
  const [email, setEmail] = useState('');
  const [habilitarAcceso, setHabilitarAcceso] = useState(false);
  const [password, setPassword] = useState('');
  const [activo, setActivo] = useState(true);
  const [inhabilitado, setInhabilitado] = useState(false);

  const [tiposCliente, setTiposCliente] = useState<TipoClienteItem[]>([]);
  const [formState, setFormState] = useState<FormState>('initial');
  const [errorMessage, setErrorMessage] = useState('');
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
  const [successMessage, setSuccessMessage] = useState('');

  const loadTiposCliente = useCallback(async () => {
    const result = await getTiposCliente();
    if (result.success && result.data) {
      setTiposCliente(result.data);
      if (result.data.length > 0 && tipoClienteId === null) {
        setTipoClienteId(result.data[0].id);
      }
    }
  }, [tipoClienteId]);

  useEffect(() => {
    loadTiposCliente();
  }, [loadTiposCliente]);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setErrorMessage('');
    setFieldErrors({});
    if (!tipoClienteId || tipoClienteId <= 0) {
      setFieldErrors((prev) => ({ ...prev, tipo_cliente_id: 'Seleccione un tipo de cliente.' }));
      return;
    }
    if (habilitarAcceso && (!password || password.length < 8)) {
      setFieldErrors((prev) => ({
        ...prev,
        password: 'La contraseña es obligatoria y debe tener al menos 8 caracteres.',
      }));
      return;
    }

    setFormState('loading');
    const body: CreateClienteBody = {
      code: code.trim(),
      nombre: nombre.trim(),
      tipo_cliente_id: tipoClienteId,
      email: email.trim() || null,
      habilitar_acceso: habilitarAcceso,
      password: habilitarAcceso ? password : undefined,
      activo,
      inhabilitado,
    };
    const result = await createCliente(body);
    setFormState(result.success ? 'success' : 'error');
    if (result.success) {
      setSuccessMessage('Cliente creado correctamente.');
      setTimeout(() => navigate('/clientes'), 1500);
      return;
    }
    setErrorMessage(result.errorMessage || 'Error al crear cliente');
    if (result.validationErrors) {
      const map: Record<string, string> = {};
      for (const [key, messages] of Object.entries(result.validationErrors)) {
        if (Array.isArray(messages) && messages[0]) map[key] = messages[0];
      }
      setFieldErrors(map);
    }
  };

  const handleCancel = () => {
    navigate('/clientes');
  };

  return (
    <div className="clientes-nueva-page" data-testid="clientes.create.page">
      <header className="clientes-nueva-header">
        <h1 className="clientes-nueva-title">Crear cliente</h1>
      </header>

      <form
        onSubmit={handleSubmit}
        className="clientes-nueva-form"
        data-testid="clientes.create.form"
        noValidate
      >
        <div className="clientes-nueva-field">
          <label htmlFor="clientes-create-code" className="clientes-nueva-label">
            Código <span className="clientes-nueva-required">*</span>
          </label>
          <input
            id="clientes-create-code"
            type="text"
            value={code}
            onChange={(e) => setCode(e.target.value)}
            required
            maxLength={50}
            className="clientes-nueva-input"
            data-testid="clientes.create.code"
            aria-required="true"
            aria-invalid={!!fieldErrors.code}
            aria-describedby={fieldErrors.code ? 'clientes-create-code-error' : undefined}
          />
          {fieldErrors.code && (
            <span id="clientes-create-code-error" className="clientes-nueva-error" role="alert">
              {fieldErrors.code}
            </span>
          )}
        </div>

        <div className="clientes-nueva-field">
          <label htmlFor="clientes-create-nombre" className="clientes-nueva-label">
            Nombre <span className="clientes-nueva-required">*</span>
          </label>
          <input
            id="clientes-create-nombre"
            type="text"
            value={nombre}
            onChange={(e) => setNombre(e.target.value)}
            required
            maxLength={255}
            className="clientes-nueva-input"
            data-testid="clientes.create.nombre"
            aria-required="true"
            aria-invalid={!!fieldErrors.nombre}
          />
          {fieldErrors.nombre && (
            <span className="clientes-nueva-error" role="alert">
              {fieldErrors.nombre}
            </span>
          )}
        </div>

        <div className="clientes-nueva-field">
          <label htmlFor="clientes-create-tipoCliente" className="clientes-nueva-label">
            Tipo de cliente <span className="clientes-nueva-required">*</span>
          </label>
          <select
            id="clientes-create-tipoCliente"
            value={tipoClienteId ?? ''}
            onChange={(e) => setTipoClienteId(e.target.value === '' ? null : Number(e.target.value))}
            required
            className="clientes-nueva-select"
            data-testid="clientes.create.tipoCliente"
            aria-required="true"
            aria-invalid={!!fieldErrors.tipo_cliente_id}
          >
            <option value="">Seleccione...</option>
            {tiposCliente.map((t) => (
              <option key={t.id} value={t.id}>
                {t.descripcion}
              </option>
            ))}
          </select>
          {fieldErrors.tipo_cliente_id && (
            <span className="clientes-nueva-error" role="alert">
              {fieldErrors.tipo_cliente_id}
            </span>
          )}
        </div>

        <div className="clientes-nueva-field">
          <label htmlFor="clientes-create-email" className="clientes-nueva-label">
            Email
          </label>
          <input
            id="clientes-create-email"
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            className="clientes-nueva-input"
            data-testid="clientes.create.email"
            aria-invalid={!!fieldErrors.email}
          />
          {fieldErrors.email && (
            <span className="clientes-nueva-error" role="alert">
              {fieldErrors.email}
            </span>
          )}
        </div>

        <div className="clientes-nueva-field clientes-nueva-checkbox-row">
          <label className="clientes-nueva-label-inline">
            <input
              type="checkbox"
              checked={habilitarAcceso}
              onChange={(e) => setHabilitarAcceso(e.target.checked)}
              className="clientes-nueva-checkbox"
              data-testid="clientes.create.habilitarAcceso"
              aria-describedby="clientes-create-habilitar-desc"
            />
            <span>Habilitar acceso al sistema</span>
          </label>
          <span id="clientes-create-habilitar-desc" className="clientes-nueva-hint">
            Si se marca, se creará un usuario con el mismo código y la contraseña indicada.
          </span>
        </div>

        {habilitarAcceso && (
          <div className="clientes-nueva-field">
            <label htmlFor="clientes-create-password" className="clientes-nueva-label">
              Contraseña <span className="clientes-nueva-required">*</span>
            </label>
            <input
              id="clientes-create-password"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              minLength={8}
              className="clientes-nueva-input"
              data-testid="clientes.create.password"
              aria-required="true"
              aria-invalid={!!fieldErrors.password}
            />
            {fieldErrors.password && (
              <span className="clientes-nueva-error" role="alert">
                {fieldErrors.password}
              </span>
            )}
            <span className="clientes-nueva-hint">Mínimo 8 caracteres.</span>
          </div>
        )}

        <div className="clientes-nueva-field clientes-nueva-checkbox-row">
          <label className="clientes-nueva-label-inline">
            <input
              type="checkbox"
              checked={activo}
              onChange={(e) => setActivo(e.target.checked)}
              className="clientes-nueva-checkbox"
              data-testid="clientes.create.activo"
            />
            <span>Activo</span>
          </label>
        </div>

        <div className="clientes-nueva-field clientes-nueva-checkbox-row">
          <label className="clientes-nueva-label-inline">
            <input
              type="checkbox"
              checked={inhabilitado}
              onChange={(e) => setInhabilitado(e.target.checked)}
              className="clientes-nueva-checkbox"
              data-testid="clientes.create.inhabilitado"
            />
            <span>Inhabilitado</span>
          </label>
        </div>

        {errorMessage && (
          <div className="clientes-nueva-form-error" data-testid="clientes.create.error" role="alert">
            {errorMessage}
          </div>
        )}

        {successMessage && (
          <div className="clientes-nueva-form-success" data-testid="clientes.create.success" role="status">
            {successMessage}
          </div>
        )}

        <div className="clientes-nueva-actions">
          <button
            type="submit"
            className="clientes-nueva-btn-submit"
            disabled={formState === 'loading'}
            data-testid="clientes.create.submit"
            aria-busy={formState === 'loading'}
          >
            {formState === 'loading' ? 'Guardando...' : 'Guardar'}
          </button>
          <button
            type="button"
            className="clientes-nueva-btn-cancel"
            onClick={handleCancel}
            disabled={formState === 'loading'}
            data-testid="clientes.create.cancel"
          >
            Cancelar
          </button>
        </div>
      </form>
    </div>
  );
}

export default ClientesNuevaPage;
