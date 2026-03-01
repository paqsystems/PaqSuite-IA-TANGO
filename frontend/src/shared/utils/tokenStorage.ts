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
const EMPRESAS_KEY = 'auth_empresas';
const EMPRESA_ACTIVA_KEY = 'auth_empresa_activa';
const LOCALE_KEY = 'locale';

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
  esAdmin?: boolean;
  nombre: string;
  email: string | null;
  locale?: string;
  menuAbrirNuevaPestana?: boolean;
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
 * Empresa del usuario (del login)
 */
export interface EmpresaItem {
  id: number;
  nombreEmpresa: string;
  nombreBd: string;
  theme?: string;
  imagen?: string | null;
}

/**
 * Guarda las empresas del usuario
 */
export function setEmpresas(empresas: EmpresaItem[]): void {
  localStorage.setItem(EMPRESAS_KEY, JSON.stringify(empresas));
}

/**
 * Obtiene las empresas del usuario
 */
export function getEmpresas(): EmpresaItem[] {
  const data = localStorage.getItem(EMPRESAS_KEY);
  if (!data) return [];
  try {
    return JSON.parse(data) as EmpresaItem[];
  } catch {
    return [];
  }
}

/**
 * Guarda la empresa activa (id, nombreEmpresa)
 */
export function setEmpresaActiva(empresa: EmpresaItem): void {
  localStorage.setItem(EMPRESA_ACTIVA_KEY, JSON.stringify(empresa));
}

/**
 * Obtiene la empresa activa
 */
export function getEmpresaActiva(): EmpresaItem | null {
  const data = localStorage.getItem(EMPRESA_ACTIVA_KEY);
  if (!data) return null;
  try {
    return JSON.parse(data) as EmpresaItem;
  } catch {
    return null;
  }
}

/**
 * Limpia todos los datos de autenticación
 */
export function clearAuth(): void {
  removeToken();
  removeUserData();
  localStorage.removeItem(EMPRESAS_KEY);
  localStorage.removeItem(EMPRESA_ACTIVA_KEY);
}

export function getLocale(): string {
  return localStorage.getItem(LOCALE_KEY) || 'es';
}

export function setLocale(locale: string): void {
  localStorage.setItem(LOCALE_KEY, locale);
}

export function getMenuAbrirNuevaPestana(): boolean {
  const user = getUserData();
  return user?.menuAbrirNuevaPestana ?? false;
}
