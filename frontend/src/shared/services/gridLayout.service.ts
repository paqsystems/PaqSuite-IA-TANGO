/**
 * Servicio API para layouts persistentes de grillas.
 *
 * @see docs/04-tareas/000-Generalidades/TR-001-layouts-grilla.md
 */

import { apiFetch } from '../api/apiClient';

const BASE = '/v1/grid-layouts';

export interface GridLayoutItem {
  id: number;
  userId: number;
  proceso: string;
  gridId: string;
  layoutName: string;
  layoutData: Record<string, unknown> | null;
  isDefault: boolean;
  createdAt: string;
  updatedAt: string;
  isOwner: boolean;
}

export interface GridLayoutListResponse {
  items: GridLayoutItem[];
}

export async function listLayouts(proceso: string, gridId = 'default'): Promise<GridLayoutItem[]> {
  const params = new URLSearchParams({ proceso, gridId });
  const res = await apiFetch(`${BASE}?${params}`);
  const json = (await res.json()) as { error: number; resultado?: GridLayoutListResponse };
  if (json.error !== 0 || !json.resultado?.items) {
    throw new Error('Error al cargar layouts');
  }
  return json.resultado.items;
}

export async function getLastUsed(proceso: string, gridId = 'default'): Promise<GridLayoutItem | null> {
  const params = new URLSearchParams({ proceso, gridId });
  const res = await apiFetch(`${BASE}/last-used?${params}`);
  const json = (await res.json()) as { error: number; resultado?: GridLayoutItem };
  if (res.status === 404 || json.error === 404) {
    return null;
  }
  if (json.error !== 0 || !json.resultado) {
    throw new Error('Error al cargar último layout');
  }
  return json.resultado;
}

export async function createLayout(params: {
  proceso: string;
  gridId?: string;
  layoutName: string;
  layoutData?: Record<string, unknown>;
  isDefault?: boolean;
}): Promise<GridLayoutItem> {
  const res = await apiFetch(BASE, {
    method: 'POST',
    body: JSON.stringify({
      proceso: params.proceso,
      gridId: params.gridId ?? 'default',
      layoutName: params.layoutName,
      layoutData: params.layoutData ?? null,
      isDefault: params.isDefault ?? false,
    }),
  });
  const json = (await res.json()) as { error: number; resultado?: GridLayoutItem };
  if (json.error !== 0 || !json.resultado) {
    throw new Error(json.resultado && typeof json.resultado === 'object' && 'errors' in json.resultado
      ? 'Errores de validación'
      : 'Error al crear layout');
  }
  return json.resultado;
}

export async function updateLayout(
  id: number,
  params: { layoutName?: string; layoutData?: Record<string, unknown>; isDefault?: boolean }
): Promise<GridLayoutItem> {
  const res = await apiFetch(`${BASE}/${id}`, {
    method: 'PUT',
    body: JSON.stringify(params),
  });
  const json = (await res.json()) as { error: number; resultado?: GridLayoutItem };
  if (json.error !== 0 || !json.resultado) {
    throw new Error(res.status === 403 ? 'Solo el creador puede modificar' : 'Error al actualizar layout');
  }
  return json.resultado;
}

export async function deleteLayout(id: number): Promise<void> {
  const res = await apiFetch(`${BASE}/${id}`, { method: 'DELETE' });
  if (res.status !== 200 && res.status !== 204) {
    const json = (await res.json()) as { respuesta?: string };
    throw new Error(json.respuesta ?? 'Error al eliminar layout');
  }
}

export async function markLayoutAsUsed(id: number): Promise<void> {
  const res = await apiFetch(`${BASE}/${id}/use`, { method: 'POST' });
  if (res.status !== 200) {
    throw new Error('Error al registrar uso');
  }
}
