/**
 * Component: EmpleadosEditarPage
 *
 * Formulario de edición de empleado (solo supervisores). TR-020(MH).
 * Ruta: /empleados/:id/editar. Código solo lectura; campos editables: nombre, email,
 * supervisor, activo, inhabilitado; opción cambiar contraseña (opcional).
 *
 * @see TR-020(MH)-edición-de-empleado.md
 */

import React, { useState, useEffect, useCallback, FormEvent } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import {
  getEmpleado,
  updateEmpleado,
  UpdateEmpleadoBody,
  EmpleadoItem,
} from '../services/empleado.service';
import './EmpleadosNuevoPage.css';

type FormState = 'initial' | 'loading' | 'loadError' | 'saving' | 'error' | 'success';

export function EmpleadosEditarPage(): React.ReactElement {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const empleadoId = id ? parseInt(id, 10) : NaN;

  const [empleado, setEmpleado] = useState<EmpleadoItem | null>(null);
  const [nombre, setNombre] = useState('');
  const [email, setEmail] = useState('');
  const [showChangePassword, setShowChangePassword] = useState(false);
  const [password, setPassword] = useState('');
  const [passwordConfirm, setPasswordConfirm] = useState('');
  const [supervisor, setSupervisor] = useState(false);
  const [activo, setActivo] = useState(true);
  const [inhabilitado, setInhabilitado] = useState(false);

  const [formState, setFormState] = useState<FormState>('loading');
  const [errorMessage, setErrorMessage] = useState('');
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
  const [successMessage, setSuccessMessage] = useState('');

  const loadEmpleado = useCallback(async (eid: number) => {
    setFormState('loading');
    setErrorMessage('');
    const result = await getEmpleado(eid);
    if (result.success && result.data) {
      const e = result.data;
      setEmpleado(e);
      setNombre(e.nombre);
      setEmail(e.email ?? '');
      setSupervisor(e.supervisor);
      setActivo(e.activo);
      setInhabilitado(e.inhabilitado);
      setPassword('');
      setPasswordConfirm('');
      setShowChangePassword(false);
      setFormState('initial');
    } else {
      setFormState('loadError');
      setErrorMessage(result.errorMessage ?? 'Error al cargar empleado');
    }
  }, []);

  useEffect(() => {
    if (!Number.isNaN(empleadoId) && empleadoId > 0) {
      loadEmpleado(empleadoId);
    } else {
      setFormState('loadError');
      setErrorMessage('ID de empleado inválido');
    }
  }, [empleadoId, loadEmpleado]);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    if (Number.isNaN(empleadoId) || empleadoId <= 0 || !empleado) return;
    setErrorMessage('');
    setFieldErrors({});

    // Validaciones frontend
    if (!nombre.trim()) {
      setFieldErrors((prev) => ({
        ...prev,
        nombre: 'El nombre es obligatorio.',
      }));
      return;
    }
    if (showChangePassword && password) {
      if (password.length < 8) {
        setFieldErrors((prev) => ({
          ...prev,
          password: 'La contraseña debe tener al menos 8 caracteres.',
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
    }

    setFormState('saving');
    const body: UpdateEmpleadoBody = {
      nombre: nombre.trim(),
      email: email.trim() || null,
      password: showChangePassword && password ? password : undefined,
      supervisor,
      activo,
      inhabilitado,
    };
    const result = await updateEmpleado(empleadoId, body);
    setFormState(result.success ? 'success' : 'error');
    if (result.success) {
      setSuccessMessage('Empleado actualizado correctamente.');
      setTimeout(() => navigate('/empleados'), 1500);
      return;
    }
    setErrorMessage(result.errorMessage ?? 'Error al actualizar empleado');
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

  if (formState === 'loading') {
    return (
      <div className="empleados-nueva-page" data-testid="empleados.edit.page">
        <p>Cargando empleado...</p>
      </div>
    );
  }

  if (formState === 'loadError' || !empleado) {
    return (
      <div className="empleados-nueva-page" data-testid="empleados.edit.page">
        <div className="empleados-nueva-error" role="alert">
          {errorMessage || 'Error al cargar empleado'}
        </div>
        <button type="button" onClick={handleCancel} className="empleados-nueva-button empleados-nueva-button-secondary">
          Volver al listado
        </button>
      </div>
    );
  }

  return (
    <div className="empleados-nueva-page" data-testid="empleados.edit.page">
      <header className="empleados-nueva-header">
        <h1 className="empleados-nueva-title">Editar empleado</h1>
      </header>

      {successMessage && (
        <div className="empleados-nueva-success" role="alert" data-testid="empleados.edit.success">
          {successMessage}
        </div>
      )}

      {errorMessage && (
        <div className="empleados-nueva-error" role="alert" data-testid="empleados.edit.error">
          {errorMessage}
        </div>
      )}

      <form
        onSubmit={handleSubmit}
        className="empleados-nueva-form"
        data-testid="empleados.edit.form"
        noValidate
      >
        <div className="empleados-nueva-field">
          <label htmlFor="empleados-edit-code" className="empleados-nueva-label">
            Código
          </label>
          <input
            id="empleados-edit-code"
            type="text"
            value={empleado.code}
            readOnly
            disabled
            className="empleados-nueva-input empleados-nueva-input-readonly"
            data-testid="empleados.edit.code"
            aria-readonly="true"
          />
        </div>

        <div className="empleados-nueva-field">
          <label htmlFor="empleados-edit-nombre" className="empleados-nueva-label">
            Nombre <span className="empleados-nueva-required">*</span>
          </label>
          <input
            id="empleados-edit-nombre"
            type="text"
            value={nombre}
            onChange={(e) => setNombre(e.target.value)}
            required
            maxLength={255}
            className="empleados-nueva-input"
            data-testid="empleados.edit.nombre"
            aria-required="true"
            aria-invalid={!!fieldErrors.nombre}
            aria-describedby={fieldErrors.nombre ? 'empleados-edit-nombre-error' : undefined}
          />
          {fieldErrors.nombre && (
            <span id="empleados-edit-nombre-error" className="empleados-nueva-error" role="alert">
              {fieldErrors.nombre}
            </span>
          )}
        </div>

        <div className="empleados-nueva-field">
          <label htmlFor="empleados-edit-email" className="empleados-nueva-label">
            Email
          </label>
          <input
            id="empleados-edit-email"
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            maxLength={255}
            className="empleados-nueva-input"
            data-testid="empleados.edit.email"
            aria-invalid={!!fieldErrors.email}
            aria-describedby={fieldErrors.email ? 'empleados-edit-email-error' : undefined}
          />
          {fieldErrors.email && (
            <span id="empleados-edit-email-error" className="empleados-nueva-error" role="alert">
              {fieldErrors.email}
            </span>
          )}
        </div>

        <div className="empleados-nueva-field">
          <label className="empleados-nueva-checkbox-label">
            <input
              type="checkbox"
              checked={showChangePassword}
              onChange={(e) => {
                setShowChangePassword(e.target.checked);
                if (!e.target.checked) {
                  setPassword('');
                  setPasswordConfirm('');
                  setFieldErrors((prev) => {
                    const next = { ...prev };
                    delete next.password;
                    delete next.passwordConfirm;
                    return next;
                  });
                }
              }}
              className="empleados-nueva-checkbox"
              data-testid="empleados.edit.showChangePassword"
            />
            <span>Cambiar contraseña</span>
          </label>
        </div>

        {showChangePassword && (
          <>
            <div className="empleados-nueva-field">
              <label htmlFor="empleados-edit-password" className="empleados-nueva-label">
                Nueva contraseña
              </label>
              <input
                id="empleados-edit-password"
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                minLength={8}
                className="empleados-nueva-input"
                data-testid="empleados.edit.password"
                aria-invalid={!!fieldErrors.password}
                aria-describedby={fieldErrors.password ? 'empleados-edit-password-error' : undefined}
              />
              {fieldErrors.password && (
                <span id="empleados-edit-password-error" className="empleados-nueva-error" role="alert">
                  {fieldErrors.password}
                </span>
              )}
            </div>

            <div className="empleados-nueva-field">
              <label htmlFor="empleados-edit-passwordConfirm" className="empleados-nueva-label">
                Confirmar contraseña
              </label>
              <input
                id="empleados-edit-passwordConfirm"
                type="password"
                value={passwordConfirm}
                onChange={(e) => setPasswordConfirm(e.target.value)}
                minLength={8}
                className="empleados-nueva-input"
                data-testid="empleados.edit.passwordConfirm"
                aria-invalid={!!fieldErrors.passwordConfirm}
                aria-describedby={fieldErrors.passwordConfirm ? 'empleados-edit-passwordConfirm-error' : undefined}
              />
              {fieldErrors.passwordConfirm && (
                <span id="empleados-edit-passwordConfirm-error" className="empleados-nueva-error" role="alert">
                  {fieldErrors.passwordConfirm}
                </span>
              )}
            </div>
          </>
        )}

        <div className="empleados-nueva-field">
          <label className="empleados-nueva-checkbox-label">
            <input
              type="checkbox"
              checked={supervisor}
              onChange={(e) => setSupervisor(e.target.checked)}
              className="empleados-nueva-checkbox"
              data-testid="empleados.edit.supervisor"
            />
            <span>Supervisor</span>
          </label>
        </div>

        <div className="empleados-nueva-field">
          <label className="empleados-nueva-checkbox-label">
            <input
              type="checkbox"
              checked={activo}
              onChange={(e) => setActivo(e.target.checked)}
              className="empleados-nueva-checkbox"
              data-testid="empleados.edit.activo"
            />
            <span>Activo</span>
          </label>
        </div>

        <div className="empleados-nueva-field">
          <label className="empleados-nueva-checkbox-label">
            <input
              type="checkbox"
              checked={inhabilitado}
              onChange={(e) => setInhabilitado(e.target.checked)}
              className="empleados-nueva-checkbox"
              data-testid="empleados.edit.inhabilitado"
            />
            <span>Inhabilitado</span>
          </label>
        </div>

        <div className="empleados-nueva-actions">
          <button
            type="submit"
            disabled={formState === 'saving'}
            className="empleados-nueva-button empleados-nueva-button-primary"
            data-testid="empleados.edit.submit"
          >
            {formState === 'saving' ? 'Guardando...' : 'Guardar'}
          </button>
          <button
            type="button"
            onClick={handleCancel}
            className="empleados-nueva-button empleados-nueva-button-secondary"
            data-testid="empleados.edit.cancel"
          >
            Cancelar
          </button>
        </div>
      </form>
    </div>
  );
}
