/**
 * Servicio API para parámetros generales por módulo.
 *
 * @see docs/04-tareas/000-Generalidades/TR-007-Parametros-generales.md
 */

import { apiFetch } from '../api/apiClient';

const BASE = '/v1/parametros-gral';

export interface ParametroItem {
  programa: string;
  clave: string;
  tipoValor: string;
  valorString: string | null;
  valorText: string | null;
  valorInt: number | null;
  valorDateTime: string | null;
  valorBool: boolean | null;
  valorDecimal: number | null;
}

export async function listParametros(programa: string): Promise<ParametroItem[]> {
  const params = new URLSearchParams({ programa });
  const res = await apiFetch(`${BASE}?${params}`);
  const json = (await res.json()) as { error: number; resultado?: { items: ParametroItem[] } };
  if (json.error !== 0 || !json.resultado?.items) {
    throw new Error('Error al cargar parámetros');
  }
  return json.resultado.items;
}

export async function updateParametro(
  programa: string,
  clave: string,
  valor: string | number | boolean | null
): Promise<ParametroItem> {
  const res = await apiFetch(`${BASE}/${encodeURIComponent(programa)}/${encodeURIComponent(clave)}`, {
    method: 'PUT',
    body: JSON.stringify({ valor }),
  });
  const json = (await res.json()) as { error: number; resultado?: ParametroItem };
  if (json.error !== 0 || !json.resultado) {
    throw new Error('Error al actualizar parámetro');
  }
  return json.resultado;
}
