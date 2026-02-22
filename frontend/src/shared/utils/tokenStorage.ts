/**
 * Utilidad: tokenStorage
 * 
 * Maneja el almacenamiento y recuperación del token de autenticación.
 * Usa localStorage para persistencia entre sesiones.
 * 
 * @see TR-001(MH)-login-de-empleado.md
 */

const TOKEN_KEY = 'auth_token';
const USER_DATA_KEY = 'auth_user';

/**
 * Datos del usuario autenticado
 */
export interface AuthUser {
  userId: number;
  userCode: string;
  tipoUsuario: 'usuario' | 'cliente';
  usuarioId: number | null;
  clienteId: number | null;
  esSupervisor: boolean;
  nombre: string;
  email: string | null;
}

/**
 * Guarda el token de autenticación en localStorage
 * 
 * @param token Token de autenticación
 */
export function setToken(token: string): void {
  localStorage.setItem(TOKEN_KEY, token);
}

/**
 * Obtiene el token de autenticación de localStorage
 * 
 * @returns Token de autenticación o null si no existe
 */
export function getToken(): string | null {
  return localStorage.getItem(TOKEN_KEY);
}

/**
 * Elimina el token de autenticación de localStorage
 */
export function removeToken(): void {
  localStorage.removeItem(TOKEN_KEY);
}

/**
 * Guarda los datos del usuario autenticado en localStorage
 * 
 * @param user Datos del usuario
 */
export function setUserData(user: AuthUser): void {
  localStorage.setItem(USER_DATA_KEY, JSON.stringify(user));
}

/**
 * Obtiene los datos del usuario autenticado de localStorage
 * 
 * @returns Datos del usuario o null si no existe
 */
export function getUserData(): AuthUser | null {
  const data = localStorage.getItem(USER_DATA_KEY);
  if (!data) return null;
  try {
    return JSON.parse(data) as AuthUser;
  } catch {
    return null;
  }
}

/**
 * Elimina los datos del usuario autenticado de localStorage
 */
export function removeUserData(): void {
  localStorage.removeItem(USER_DATA_KEY);
}

/**
 * Verifica si el usuario está autenticado
 * 
 * @returns true si hay un token almacenado
 */
export function isAuthenticated(): boolean {
  return getToken() !== null;
}

/**
 * Limpia todos los datos de autenticación
 */
export function clearAuth(): void {
  removeToken();
  removeUserData();
}
