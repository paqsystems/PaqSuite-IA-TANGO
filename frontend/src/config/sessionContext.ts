/**
 * Contexto de sesión para variables del shell (empresa, etc.)
 * La empresa se actualizará con HU-002 (cambio de empresa activa).
 */

const EMPRESA_KEY = 'session_empresa_nombre';

const DEFAULT_EMPRESA = 'Empresa Demo';

export function getEmpresa(): string {
  try {
    const stored = localStorage.getItem(EMPRESA_KEY);
    return stored && stored.trim() ? stored : DEFAULT_EMPRESA;
  } catch {
    return DEFAULT_EMPRESA;
  }
}

export function setEmpresa(nombre: string): void {
  localStorage.setItem(EMPRESA_KEY, nombre.trim() || DEFAULT_EMPRESA);
}
