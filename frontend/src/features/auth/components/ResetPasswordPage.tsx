/**
 * Página: Restablecer contraseña con token (reset).
 * Token por query; formulario con nueva contraseña y confirmación.
 *
 * @see TR-004(SH)-recuperación-de-contraseña.md
 */

import React, { useState, FormEvent, useEffect } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { resetPassword } from '../services/auth.service';
import './ResetPasswordPage.css';

const MIN_PASSWORD_LENGTH = 8;

export function ResetPasswordPage(): React.ReactElement {
  const [searchParams] = useSearchParams();
  const tokenFromUrl = searchParams.get('token') ?? '';
  const [token, setToken] = useState(tokenFromUrl);
  const [password, setPassword] = useState('');
  const [passwordConfirm, setPasswordConfirm] = useState('');
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState('');
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});

  useEffect(() => {
    setToken(tokenFromUrl);
  }, [tokenFromUrl]);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError('');
    setFieldErrors({});
    const err: Record<string, string> = {};
    if (!token.trim()) err.token = 'El enlace de recuperación no es válido.';
    if (!password.trim()) err.password = 'La contraseña es requerida.';
    else if (password.length < MIN_PASSWORD_LENGTH)
      err.password = `La contraseña debe tener al menos ${MIN_PASSWORD_LENGTH} caracteres.`;
    if (password !== passwordConfirm) err.passwordConfirm = 'Las contraseñas no coinciden.';
    if (Object.keys(err).length > 0) {
      setFieldErrors(err);
      return;
    }
    setLoading(true);
    const result = await resetPassword(token.trim(), password, passwordConfirm);
    setLoading(false);
    if (result.success) {
      setSuccess(true);
    } else {
      setError(result.errorMessage || 'Error al restablecer la contraseña.');
    }
  };

  if (!tokenFromUrl) {
    return (
      <div className="reset-password-container">
        <div className="reset-password-card">
          <h1 className="reset-password-title">Restablecer contraseña</h1>
          <p className="reset-password-message">
            No se encontró un enlace válido. Solicite nuevamente la recuperación de contraseña desde la página de inicio de sesión.
          </p>
          <Link to="/forgot-password" className="reset-password-link" data-testid="resetPassword.requestAgain">
            Solicitar recuperación
          </Link>
          <Link to="/login" className="reset-password-link">Volver al login</Link>
        </div>
      </div>
    );
  }

  if (success) {
    return (
      <div className="reset-password-container">
        <div className="reset-password-card">
          <h1 className="reset-password-title">Contraseña restablecida</h1>
          <p className="reset-password-success" role="alert" data-testid="resetPassword.success">
            Su contraseña se ha actualizado correctamente. Ya puede iniciar sesión con la nueva contraseña.
          </p>
          <Link to="/login" className="reset-password-primary-link" data-testid="resetPassword.goToLogin">
            Ir a iniciar sesión
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="reset-password-container">
      <div className="reset-password-card">
        <h1 className="reset-password-title">Nueva contraseña</h1>
        <p className="reset-password-intro">Ingrese su nueva contraseña y la confirmación.</p>

        <form onSubmit={handleSubmit} className="reset-password-form" data-testid="resetPassword.form">
          {error && (
            <div className="reset-password-error" role="alert" data-testid="resetPassword.error">
              {error}
            </div>
          )}
          <div className="form-group">
            <label htmlFor="password" className="form-label">Nueva contraseña</label>
            <input
              type="password"
              id="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              disabled={loading}
              data-testid="resetPassword.password"
              className={`form-input ${fieldErrors.password ? 'input-error' : ''}`}
              autoComplete="new-password"
              minLength={MIN_PASSWORD_LENGTH}
            />
            {fieldErrors.password && (
              <span className="field-error" role="alert">{fieldErrors.password}</span>
            )}
          </div>
          <div className="form-group">
            <label htmlFor="passwordConfirm" className="form-label">Confirmar contraseña</label>
            <input
              type="password"
              id="passwordConfirm"
              value={passwordConfirm}
              onChange={(e) => setPasswordConfirm(e.target.value)}
              disabled={loading}
              data-testid="resetPassword.passwordConfirm"
              className={`form-input ${fieldErrors.passwordConfirm ? 'input-error' : ''}`}
              autoComplete="new-password"
              minLength={MIN_PASSWORD_LENGTH}
            />
            {fieldErrors.passwordConfirm && (
              <span className="field-error" role="alert">{fieldErrors.passwordConfirm}</span>
            )}
          </div>
          <button
            type="submit"
            disabled={loading}
            data-testid="resetPassword.submit"
            className="reset-password-submit"
            aria-busy={loading}
          >
            {loading ? 'Guardando...' : 'Guardar'}
          </button>
          <Link to="/login" className="reset-password-cancel">Cancelar</Link>
        </form>
      </div>
    </div>
  );
}

export default ResetPasswordPage;
