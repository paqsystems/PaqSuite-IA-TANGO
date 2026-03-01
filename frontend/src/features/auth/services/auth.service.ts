/**
 * Service: auth.service
 * 
 * Servicio de autenticación del frontend.
 * Maneja las llamadas al API de autenticación y el almacenamiento de tokens.
 * 
 * @see TR-001(MH)-login-de-empleado.md
 * @see TR-003(MH)-logout.md
 */

import { setToken, setUserData, setEmpresas, setEmpresaActiva, setLocale, clearAuth, getToken, getLocale, AuthUser, EmpresaItem } from '../../../shared/utils/tokenStorage';
import { apiFetch } from '../../../shared/api/apiClient';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

/**
 * Respuesta del endpoint de login
 */
export interface LoginResponse {
  error: number;
  respuesta: string;
  resultado: {
    token: string;
    user: {
      user_id: number;
      user_code: string;
      tipo_usuario: 'usuario' | 'cliente';
      usuario_id: number | null;
      cliente_id: number | null;
      es_supervisor: boolean;
      nombre: string;
      email: string | null;
    };
    empresas?: EmpresaItem[];
    redirectTo?: 'layout' | 'selector';
  };
}

/**
 * Respuesta de error del API
 */
export interface ApiError {
  error: number;
  respuesta: string;
  resultado: {
    errors?: Record<string, string[]>;
  };
}

/**
 * Resultado del login
 */
export interface LoginResult {
  success: boolean;
  user?: AuthUser;
  redirectTo?: 'layout' | 'selector';
  empresas?: EmpresaItem[];
  errorCode?: number;
  errorMessage?: string;
}

/**
 * Intenta autenticar al usuario con código y contraseña
 * 
 * @param usuario Código de usuario
 * @param password Contraseña
 * @returns Resultado del login con datos del usuario o error
 */
export async function login(usuario: string, password: string, locale?: string): Promise<LoginResult> {
  try {
    const body: Record<string, string> = { usuario, password };
    if (locale) body.locale = locale;
    const response = await fetch(`${API_BASE_URL}/v1/auth/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(body),
    });

    const data = await response.json();

    if (!response.ok) {
      // Error de autenticación o validación
      const errorData = data as ApiError;
      return {
        success: false,
        errorCode: errorData.error,
        errorMessage: errorData.respuesta,
      };
    }

    // Login exitoso
    const loginData = data as LoginResponse;
    const resultado = loginData.resultado;

    const authUser: AuthUser = {
      userId: resultado.user.user_id,
      userCode: resultado.user.user_code,
      tipoUsuario: resultado.user.tipo_usuario,
      usuarioId: resultado.user.usuario_id,
      clienteId: resultado.user.cliente_id,
      esSupervisor: resultado.user.es_supervisor ?? false,
      esAdmin: (resultado.user as { es_admin?: boolean }).es_admin ?? resultado.user.es_supervisor ?? false,
      nombre: resultado.user.nombre,
      email: resultado.user.email,
      locale: (resultado.user as { locale?: string }).locale ?? getLocale(),
      menuAbrirNuevaPestana: (resultado.user as { menu_abrir_nueva_pestana?: boolean }).menu_abrir_nueva_pestana ?? false,
    };

    setToken(resultado.token);
    setUserData(authUser);
    if (authUser.locale) setLocale(authUser.locale);

    const empresas = resultado.empresas ?? [];
    const redirectTo = resultado.redirectTo ?? 'layout';

    setEmpresas(empresas);
    if (empresas.length === 1) {
      setEmpresaActiva(empresas[0]);
    }

    return {
      success: true,
      user: authUser,
      redirectTo,
      empresas,
    };

  } catch (error) {
    // Error de red o inesperado
    console.error('Error en login:', error);
    return {
      success: false,
      errorCode: 9999,
      errorMessage: 'Error de conexión. Intente nuevamente.',
    };
  }
}

/**
 * Resultado del logout
 */
export interface LogoutResult {
  success: boolean;
  errorMessage?: string;
}

/**
 * Cierra la sesión del usuario
 * 
 * Comportamiento fail-safe:
 * - Siempre limpia localStorage aunque el API falle
 * - Si el token ya era inválido (401), igual se considera éxito
 * - Si hay error de red, igual limpia y permite continuar
 * 
 * @returns Resultado del logout
 * @see TR-003(MH)-logout.md
 */
export async function logout(): Promise<LogoutResult> {
  const token = getToken();
  
  try {
    // Intentar llamar al API de logout
    if (token) {
      const response = await apiFetch('/v1/auth/logout', { method: 'POST', skipCompanyId: true });

      // Incluso si retorna 401 (token inválido), consideramos éxito
      // porque el objetivo es cerrar sesión local
      if (!response.ok && response.status !== 401) {
        console.warn('Logout API respondió con error:', response.status);
      }
    }

    // Siempre limpiar localStorage (fail-safe)
    clearAuth();

    return { success: true };

  } catch (error) {
    // Error de red - igual limpiar localStorage (fail-safe)
    console.warn('Error de red en logout, limpiando sesión local:', error);
    clearAuth();

    return { 
      success: true, // Consideramos éxito porque la sesión local se limpió
    };
  }
}

/**
 * Cierra la sesión del usuario de forma síncrona (solo limpia localStorage)
 * Usar cuando no se necesita esperar la respuesta del API
 */
export function logoutSync(): void {
  clearAuth();
}

/**
 * Resultado de solicitar recuperación de contraseña (forgot).
 * El API siempre responde 200 con mensaje genérico.
 */
export interface ForgotPasswordResult {
  success: boolean;
  message?: string;
  errorMessage?: string;
}

/**
 * Solicita enlace de recuperación de contraseña.
 * @see TR-004(SH)-recuperación-de-contraseña.md
 */
export async function forgotPassword(codeOrEmail: string): Promise<ForgotPasswordResult> {
  try {
    const response = await fetch(`${API_BASE_URL}/v1/auth/forgot-password`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ code_or_email: codeOrEmail.trim() }),
    });
    const data = await response.json();
    if (response.ok && data.error === 0) {
      return { success: true, message: data.respuesta };
    }
    return { success: false, errorMessage: data.respuesta || 'Error al solicitar recuperación.' };
  } catch {
    return { success: false, errorMessage: 'Error de conexión. Intente nuevamente.' };
  }
}

/**
 * Resultado de restablecer contraseña (reset).
 */
export interface ResetPasswordResult {
  success: boolean;
  errorMessage?: string;
}

/**
 * Restablece la contraseña con el token recibido por email.
 * @see TR-004(SH)-recuperación-de-contraseña.md
 */
export async function resetPassword(
  token: string,
  password: string,
  passwordConfirmation: string
): Promise<ResetPasswordResult> {
  try {
    const response = await fetch(`${API_BASE_URL}/v1/auth/reset-password`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({
        token: token.trim(),
        password,
        password_confirmation: passwordConfirmation,
      }),
    });
    const data = await response.json();
    if (response.ok && data.error === 0) {
      return { success: true };
    }
    return { success: false, errorMessage: data.respuesta || 'Error al restablecer contraseña.' };
  } catch {
    return { success: false, errorMessage: 'Error de conexión. Intente nuevamente.' };
  }
}
