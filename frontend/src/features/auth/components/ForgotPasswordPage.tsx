/**
 * Página: Solicitar recuperación de contraseña (forgot).
 * Formulario con código o email; mensaje genérico de éxito.
 *
 * @see TR-004(SH)-recuperación-de-contraseña.md
 */

import React, { useState, FormEvent } from 'react';
import { Link } from 'react-router-dom';
import { forgotPassword } from '../services/auth.service';
import './ForgotPasswordPage.css';

export function ForgotPasswordPage(): React.ReactElement {
  const [codeOrEmail, setCodeOrEmail] = useState('');
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState('');
  const [fieldError, setFieldError] = useState('');

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError('');
    setFieldError('');
    const value = codeOrEmail.trim();
    if (!value) {
      setFieldError('El código de usuario o email es requerido.');
      return;
    }
    setLoading(true);
    const result = await forgotPassword(value);
    setLoading(false);
    if (result.success) {
      setSuccess(true);
      setCodeOrEmail('');
    } else {
      setError(result.errorMessage || 'Error al solicitar recuperación.');
    }
  };

  return (
    <div className="forgot-password-container">
      <div className="forgot-password-card">
        <h1 className="forgot-password-title">Recuperar contraseña</h1>
        <p className="forgot-password-intro">
          Ingrese su código de usuario o email. Si existe una cuenta con email configurado, recibirá un enlace para restablecer la contraseña.
        </p>

        {success ? (
          <div className="forgot-password-success" role="alert" data-testid="forgotPassword.success">
            <p>Si el usuario existe y tiene email configurado, recibirá un enlace para restablecer la contraseña.</p>
            <Link to="/login" className="forgot-password-back-link">Volver al inicio de sesión</Link>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="forgot-password-form" data-testid="forgotPassword.form">
            {error && (
              <div className="forgot-password-error" role="alert" data-testid="forgotPassword.error">
                {error}
              </div>
            )}
            <div className="form-group">
              <label htmlFor="codeOrEmail" className="form-label">
                Código de usuario o email
              </label>
              <input
                type="text"
                id="codeOrEmail"
                value={codeOrEmail}
                onChange={(e) => setCodeOrEmail(e.target.value)}
                disabled={loading}
                data-testid="forgotPassword.codeOrEmail"
                className={`form-input ${fieldError ? 'input-error' : ''}`}
                aria-invalid={!!fieldError}
                autoComplete="username"
                autoFocus
              />
              {fieldError && (
                <span className="field-error" role="alert">
                  {fieldError}
                </span>
              )}
            </div>
            <button
              type="submit"
              disabled={loading}
              data-testid="forgotPassword.submit"
              className="forgot-password-submit"
              aria-busy={loading}
            >
              {loading ? 'Enviando...' : 'Enviar'}
            </button>
            <Link to="/login" className="forgot-password-cancel" data-testid="forgotPassword.cancel">
              Volver al inicio de sesión
            </Link>
          </form>
        )}
      </div>
    </div>
  );
}

export default ForgotPasswordPage;
