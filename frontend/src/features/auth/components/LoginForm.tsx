/**
 * Component: LoginForm
 * 
 * Formulario de login para empleados.
 * Incluye validaciones del lado del cliente, manejo de estados y accesibilidad.
 * 
 * Estados:
 * - initial: Formulario vacío, listo para input
 * - loading: Enviando credenciales al servidor
 * - error: Error de autenticación o validación
 * - success: Login exitoso (redirige automáticamente)
 * 
 * @see TR-001(MH)-login-de-empleado.md
 */

import React, { useState, FormEvent } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { login } from '../services/auth.service';
import { LanguageSelector } from '../../../shared/components/LanguageSelector';
import { getLocale } from '../../../shared/utils/tokenStorage';
import './LoginForm.css';

/**
 * Estados del formulario
 */
type FormState = 'initial' | 'loading' | 'error' | 'success';

/**
 * Errores de validación
 */
interface ValidationErrors {
  usuario?: string;
  password?: string;
}

/**
 * Componente LoginForm
 */
export function LoginForm(): React.ReactElement {
  // Estados del formulario
  const [usuario, setUsuario] = useState('');
  const [password, setPassword] = useState('');
  const [formState, setFormState] = useState<FormState>('initial');
  const [errorMessage, setErrorMessage] = useState('');
  const [validationErrors, setValidationErrors] = useState<ValidationErrors>({});
  
  const navigate = useNavigate();

  /**
   * Valida los campos del formulario antes de enviar
   */
  const validateForm = (): boolean => {
    const errors: ValidationErrors = {};
    
    if (!usuario.trim()) {
      errors.usuario = 'El código de usuario es requerido';
    }
    
    if (!password) {
      errors.password = 'La contraseña es requerida';
    } else if (password.length < 8) {
      errors.password = 'La contraseña debe tener al menos 8 caracteres';
    }
    
    setValidationErrors(errors);
    return Object.keys(errors).length === 0;
  };

  /**
   * Maneja el envío del formulario
   */
  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    
    // Limpiar errores previos
    setErrorMessage('');
    setValidationErrors({});
    
    // Validar campos
    if (!validateForm()) {
      setFormState('error');
      return;
    }
    
    // Cambiar a estado loading
    setFormState('loading');
    
    try {
      // Intentar login
      const result = await login(usuario, password, getLocale());
      
      if (result.success) {
        setFormState('success');
        const redirectTo = result.redirectTo ?? 'layout';
        navigate(redirectTo === 'selector' ? '/select-empresa' : '/');
      } else {
        // Error de autenticación
        setFormState('error');
        setErrorMessage(result.errorMessage || 'Credenciales inválidas');
      }
    } catch {
      // Error inesperado
      setFormState('error');
      setErrorMessage('Error de conexión. Intente nuevamente.');
    }
  };

  const isLoading = formState === 'loading';

  return (
    <div className="login-container">
      <div className="login-language-wrapper">
        <LanguageSelector />
      </div>
      <form 
        onSubmit={handleSubmit}
        data-testid="login.form"
        className="login-form"
        aria-busy={isLoading}
      >
        <h1 className="login-title">Iniciar Sesión</h1>
        
        {/* Mensaje de error general */}
        {formState === 'error' && errorMessage && (
          <div 
            className="login-error"
            data-testid="auth.login.errorMessage"
            role="alert"
            aria-live="polite"
          >
            {errorMessage}
          </div>
        )}
        
        {/* Campo de código de usuario */}
        <div className="form-group">
          <label htmlFor="usuario" className="form-label">
            Código de Usuario
          </label>
          <input
            type="text"
            id="usuario"
            name="usuario"
            value={usuario}
            onChange={(e) => setUsuario(e.target.value)}
            disabled={isLoading}
            data-testid="login.codigo"
            className={`form-input ${validationErrors.usuario ? 'input-error' : ''}`}
            aria-label="Código de usuario"
            aria-invalid={!!validationErrors.usuario}
            aria-describedby={validationErrors.usuario ? 'usuario-error' : undefined}
            autoComplete="username"
            autoFocus
          />
          {validationErrors.usuario && (
            <span id="usuario-error" className="field-error" role="alert">
              {validationErrors.usuario}
            </span>
          )}
        </div>
        
        {/* Campo de contraseña */}
        <div className="form-group">
          <label htmlFor="password" className="form-label">
            Contraseña
          </label>
          <input
            type="password"
            id="password"
            name="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            disabled={isLoading}
            data-testid="login.password"
            className={`form-input ${validationErrors.password ? 'input-error' : ''}`}
            aria-label="Contraseña"
            aria-invalid={!!validationErrors.password}
            aria-describedby={validationErrors.password ? 'password-error' : undefined}
            autoComplete="current-password"
          />
          {validationErrors.password && (
            <span id="password-error" className="field-error" role="alert">
              {validationErrors.password}
            </span>
          )}
        </div>

        <div className="form-group form-group-forgot">
          <Link
            to="/forgot-password"
            className="forgot-password-link"
            data-testid="auth.forgotPasswordLink"
            aria-label="Recuperar contraseña"
          >
            ¿Olvidaste tu contraseña?
          </Link>
        </div>
        
        {/* Botón de envío */}
        <button
          type="submit"
          disabled={isLoading}
          data-testid="login.submit"
          className="login-button"
          aria-label="Iniciar sesión"
        >
          {isLoading ? (
            <>
              <span 
                className="loading-spinner"
                data-testid="auth.login.loadingSpinner"
                aria-hidden="true"
              />
              Autenticando...
            </>
          ) : (
            'Iniciar Sesión'
          )}
        </button>
      </form>
    </div>
  );
}

export default LoginForm;
