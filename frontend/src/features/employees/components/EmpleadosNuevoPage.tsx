/**
 * Component: EmpleadosNuevoPage
 *
 * Formulario de creación de empleado (solo supervisores). TR-019(MH).
 * Ruta: /empleados/nuevo. Campos: código, nombre, email, contraseña,
 * confirmar contraseña, supervisor, activo, inhabilitado.
 *
 * @see TR-019(MH)-creación-de-empleado.md
 */

import React, { useState, FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  createEmpleado,
  CreateEmpleadoBody,
} from '../services/empleado.service';
import './EmpleadosNuevoPage.css';

type FormState = 'initial' | 'loading' | 'error' | 'success';

export function EmpleadosNuevoPage(): React.ReactElement {
  const navigate = useNavigate();
  const [code, setCode] = useState('');
  const [nombre, setNombre] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirm, setPasswordConfirm] = useState('');
  const [supervisor, setSupervisor] = useState(false);
  const [activo, setActivo] = useState(true);
  const [inhabilitado, setInhabilitado] = useState(false);

  const [formState, setFormState] = useState<FormState>('initial');
  const [errorMessage, setErrorMessage] = useState('');
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
  const [successMessage, setSuccessMessage] = useState('');

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setErrorMessage('');
    setFieldErrors({});

    // Validaciones frontend
    if (!password || password.length < 8) {
      setFieldErrors((prev) => ({
        ...prev,
        password: 'La contraseña es obligatoria y debe tener al menos 8 caracteres.',
      }));
      return;
    }
    if (password !== passwordConfirm) {
      setFieldErrors((prev) => ({
        ...prev,
        passwordConfirm: 'Las contraseñas no coinciden.',
      }));
      return;
    }

    setFormState('loading');
    const body: CreateEmpleadoBody = {
      code: code.trim(),
      nombre: nombre.trim(),
      email: email.trim() || null,
      password: password,
      supervisor,
      activo,
      inhabilitado,
    };
    const result = await createEmpleado(body);
    setFormState(result.success ? 'success' : 'error');
    if (result.success) {
      setSuccessMessage('Empleado creado correctamente.');
      setTimeout(() => navigate('/empleados'), 1500);
      return;
    }
    setErrorMessage(result.errorMessage || 'Error al crear empleado');
    if (result.validationErrors) {
      const map: Record<string, string> = {};
      for (const [key, messages] of Object.entries(result.validationErrors)) {
        if (Array.isArray(messages) && messages[0]) map[key] = messages[0];
      }
      setFieldErrors(map);
    }
  };

  const handleCancel = () => {
    navigate('/empleados');
  };

  return (
    <div className="empleados-nueva-page" data-testid="empleados.create.page">
      <header className="empleados-nueva-header">
        <h1 className="empleados-nueva-title">Crear empleado</h1>
      </header>

      <form
        onSubmit={handleSubmit}
        className="empleados-nueva-form"
        data-testid="empleados.create.form"
        noValidate
      >
        <div className="empleados-nueva-field">
          <label htmlFor="empleados-create-code" className="empleados-nueva-label">
            Código <span className="empleados-nueva-required">*</span>
          </label>
          <input
            id="empleados-create-code"
            type="text"
            value={code}
            onChange={(e) => setCode(e.target.value)}
            required
            maxLength={50}
            className="empleados-nueva-input"
            data-testid="empleados.create.code"
            aria-required="true"
            aria-invalid={!!fieldErrors.code}
            aria-describedby={fieldErrors.code ? 'empleados-create-code-error' : undefined}
          />
          {fieldErrors.code && (
            <span id="empleados-create-code-error" className="empleados-nueva-error" role="alert">
              {fieldErrors.code}
            </span>
          )}
        </div>

        <div className="empleados-nueva-field">
          <label htmlFor="empleados-create-nombre" className="empleados-nueva-label">
            Nombre <span className="empleados-nueva-required">*</span>
          </label>
          <input
            id="empleados-create-nombre"
            type="text"
            value={nombre}
            onChange={(e) => setNombre(e.target.value)}
            required
            maxLength={255}
            className="empleados-nueva-input"
            data-testid="empleados.create.nombre"
            aria-required="true"
            aria-invalid={!!fieldErrors.nombre}
          />
          {fieldErrors.nombre && (
            <span className="empleados-nueva-error" role="alert">
              {fieldErrors.nombre}
            </span>
          )}
        </div>

        <div className="empleados-nueva-field">
          <label htmlFor="empleados-create-email" className="empleados-nueva-label">
            Email
          </label>
          <input
            id="empleados-create-email"
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            className="empleados-nueva-input"
            data-testid="empleados.create.email"
            aria-invalid={!!fieldErrors.email}
          />
          {fieldErrors.email && (
            <span className="empleados-nueva-error" role="alert">
              {fieldErrors.email}
            </span>
          )}
        </div>

        <div className="empleados-nueva-field">
          <label htmlFor="empleados-create-password" className="empleados-nueva-label">
            Contraseña <span className="empleados-nueva-required">*</span>
          </label>
          <input
            id="empleados-create-password"
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            minLength={8}
            required
            className="empleados-nueva-input"
            data-testid="empleados.create.password"
            aria-required="true"
            aria-invalid={!!fieldErrors.password}
          />
          {fieldErrors.password && (
            <span className="empleados-nueva-error" role="alert">
              {fieldErrors.password}
            </span>
          )}
          <span className="empleados-nueva-hint">Mínimo 8 caracteres.</span>
        </div>

        <div className="empleados-nueva-field">
          <label htmlFor="empleados-create-passwordConfirm" className="empleados-nueva-label">
            Confirmar contraseña <span className="empleados-nueva-required">*</span>
          </label>
          <input
            id="empleados-create-passwordConfirm"
            type="password"
            value={passwordConfirm}
            onChange={(e) => setPasswordConfirm(e.target.value)}
            minLength={8}
            required
            className="empleados-nueva-input"
            data-testid="empleados.create.passwordConfirm"
            aria-required="true"
            aria-invalid={!!fieldErrors.passwordConfirm}
          />
          {fieldErrors.passwordConfirm && (
            <span className="empleados-nueva-error" role="alert">
              {fieldErrors.passwordConfirm}
            </span>
          )}
        </div>

        <div className="empleados-nueva-field empleados-nueva-checkbox-row">
          <label className="empleados-nueva-label-inline">
            <input
              type="checkbox"
              checked={supervisor}
              onChange={(e) => setSupervisor(e.target.checked)}
              className="empleados-nueva-checkbox"
              data-testid="empleados.create.supervisor"
            />
            <span>Supervisor</span>
          </label>
        </div>

        <div className="empleados-nueva-field empleados-nueva-checkbox-row">
          <label className="empleados-nueva-label-inline">
            <input
              type="checkbox"
              checked={activo}
              onChange={(e) => setActivo(e.target.checked)}
              className="empleados-nueva-checkbox"
              data-testid="empleados.create.activo"
            />
            <span>Activo</span>
          </label>
        </div>

        <div className="empleados-nueva-field empleados-nueva-checkbox-row">
          <label className="empleados-nueva-label-inline">
            <input
              type="checkbox"
              checked={inhabilitado}
              onChange={(e) => setInhabilitado(e.target.checked)}
              className="empleados-nueva-checkbox"
              data-testid="empleados.create.inhabilitado"
            />
            <span>Inhabilitado</span>
          </label>
        </div>

        {errorMessage && (
          <div className="empleados-nueva-form-error" data-testid="empleados.create.error" role="alert">
            {errorMessage}
          </div>
        )}

        {successMessage && (
          <div className="empleados-nueva-form-success" data-testid="empleados.create.success" role="status">
            {successMessage}
          </div>
        )}

        <div className="empleados-nueva-actions">
          <button
            type="submit"
            className="empleados-nueva-btn-submit"
            disabled={formState === 'loading'}
            data-testid="empleados.create.submit"
            aria-busy={formState === 'loading'}
          >
            {formState === 'loading' ? 'Guardando...' : 'Guardar'}
          </button>
          <button
            type="button"
            className="empleados-nueva-btn-cancel"
            onClick={handleCancel}
            disabled={formState === 'loading'}
            data-testid="empleados.create.cancel"
          >
            Cancelar
          </button>
        </div>
      </form>
    </div>
  );
}

export default EmpleadosNuevoPage;
