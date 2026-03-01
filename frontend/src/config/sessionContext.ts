/**
 * Contexto de sesión para variables del shell (empresa, etc.)
 * Usa getEmpresaActiva de tokenStorage.
 *
 * @see docs/04-tareas/001-Seguridad/TR-002-seleccion-empresa.md
 */

import { getEmpresaActiva, getEmpresas, setEmpresaActiva } from '../shared/utils/tokenStorage';

const DEFAULT_EMPRESA = 'Empresa';

export function getEmpresa(): string {
  const activa = getEmpresaActiva();
  return activa?.nombreEmpresa ?? DEFAULT_EMPRESA;
}

/**
 * Establece la empresa activa por nombre (busca en empresas del usuario).
 * Para establecer por objeto, usar setEmpresaActiva de tokenStorage.
 */
export function setEmpresa(nombre: string): void {
  const empresas = getEmpresas();
  const found = empresas.find((e) => e.nombreEmpresa === nombre);
  if (found) {
    setEmpresaActiva(found);
  }
}
