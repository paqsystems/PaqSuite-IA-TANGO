/**
 * Service: user.service
 *
 * Servicio de usuario del frontend.
 * Maneja las llamadas al API de perfil de usuario, edición de perfil y cambio de contraseña.
 *
 * @see TR-006(MH)-visualización-de-perfil-de-usuario.md
 * @see TR-007(SH)-edición-de-perfil-de-usuario.md
 * @see TR-005(SH)-cambio-de-contraseña-usuario-autenticado.md
 */

import { getToken } from '../../../shared/utils/tokenStorage';

/**
 * URL base del API
 */
const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

/**
 * Datos del perfil de usuario
 */
export interface UserProfile {
  user_code: string;
  nombre: string;
  email: string | null;
  tipo_usuario: 'usuario' | 'cliente' | 'desconocido';
  es_supervisor: boolean;
  created_at: string;
}

/**
 * Respuesta del endpoint de perfil
 */
export interface ProfileResponse {
  error: number;
  respuesta: string;
  resultado: UserProfile;
}

/**
 * Respuesta de error del API
 */
export interface ApiError {
  error: number;
  respuesta: string;
  resultado: {};
}

/**
 * Resultado de obtener perfil
 */
export interface GetProfileResult {
  success: boolean;
  profile?: UserProfile;
  errorCode?: number;
  errorMessage?: string;
}

/**
 * Obtiene el perfil del usuario autenticado
 * 
 * @returns Resultado con datos del perfil o error
 */
export async function getProfile(): Promise<GetProfileResult> {
  const token = getToken();
  
  if (!token) {
    return {
      success: false,
      errorCode: 4001,
      errorMessage: 'No autenticado',
    };
  }

  try {
    const response = await fetch(`${API_BASE_URL}/v1/user/profile`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
    });

    const data = await response.json();

    if (!response.ok) {
      // Error de autenticación o servidor
      const errorData = data as ApiError;
      return {
        success: false,
        errorCode: errorData.error,
        errorMessage: errorData.respuesta,
      };
    }

    // Perfil obtenido exitosamente
    const profileData = data as ProfileResponse;
    
    return {
      success: true,
      profile: profileData.resultado,
    };

  } catch (error) {
    // Error de red o inesperado
    console.error('Error en getProfile:', error);
    return {
      success: false,
      errorCode: 9999,
      errorMessage: 'Error de conexión. Intente nuevamente.',
    };
  }
}

/**
 * Resultado de cambio de contraseña
 */
export interface ChangePasswordResult {
  success: boolean;
  errorCode?: number;
  errorMessage?: string;
  validationErrors?: Record<string, string[]>;
}

/**
 * Cambia la contraseña del usuario autenticado.
 * Requiere contraseña actual, nueva contraseña y confirmación.
 *
 * @param currentPassword Contraseña actual
 * @param password Nueva contraseña
 * @param passwordConfirmation Confirmación de nueva contraseña
 * @returns ChangePasswordResult
 */
export async function changePassword(
  currentPassword: string,
  password: string,
  passwordConfirmation: string
): Promise<ChangePasswordResult> {
  const token = getToken();
  if (!token) {
    return {
      success: false,
      errorCode: 4001,
      errorMessage: 'No autenticado',
    };
  }

  try {
    const response = await fetch(`${API_BASE_URL}/v1/auth/change-password`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({
        current_password: currentPassword,
        password,
        password_confirmation: passwordConfirmation,
      }),
    });

    const data = await response.json();

    if (response.ok && data.error === 0) {
      return { success: true };
    }

    const errData = data as { error: number; respuesta: string; resultado?: { errors?: Record<string, string[]> } };
    return {
      success: false,
      errorCode: errData.error,
      errorMessage: errData.respuesta,
      validationErrors: errData.resultado?.errors,
    };
  } catch (error) {
    console.error('Error en changePassword:', error);
    return {
      success: false,
      errorCode: 9999,
      errorMessage: 'Error de conexión. Intente nuevamente.',
    };
  }
}

/**
 * Resultado de actualizar perfil
 */
export interface UpdateProfileResult {
  success: boolean;
  profile?: UserProfile;
  errorCode?: number;
  errorMessage?: string;
  validationErrors?: Record<string, string[]>;
}

/**
 * Actualiza el perfil del usuario autenticado (nombre, email).
 * Código de usuario no es modificable.
 *
 * @param nombre Nombre completo (obligatorio)
 * @param email Email (opcional)
 * @returns UpdateProfileResult
 */
export async function updateProfile(nombre: string, email: string | null): Promise<UpdateProfileResult> {
  const token = getToken();
  if (!token) {
    return {
      success: false,
      errorCode: 4001,
      errorMessage: 'No autenticado',
    };
  }

  try {
    const response = await fetch(`${API_BASE_URL}/v1/user/profile`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({
        nombre: nombre.trim(),
        email: email && email.trim() !== '' ? email.trim() : null,
      }),
    });

    const data = await response.json();

    if (response.ok && data.error === 0) {
      return {
        success: true,
        profile: data.resultado as UserProfile,
      };
    }

    const errData = data as { error: number; respuesta: string; resultado?: { errors?: Record<string, string[]> } };
    return {
      success: false,
      errorCode: errData.error,
      errorMessage: errData.respuesta,
      validationErrors: errData.resultado?.errors,
    };
  } catch (error) {
    console.error('Error en updateProfile:', error);
    return {
      success: false,
      errorCode: 9999,
      errorMessage: 'Error de conexión. Intente nuevamente.',
    };
  }
}
