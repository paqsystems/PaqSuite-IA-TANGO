/**
 * Hook para gestionar layouts persistentes de grillas.
 *
 * @see docs/04-tareas/000-Generalidades/TR-001-layouts-grilla.md
 */

import { useState, useEffect, useCallback } from 'react';
import * as gridLayoutService from '../services/gridLayout.service';
import type { GridLayoutItem } from '../services/gridLayout.service';

export interface UseGridLayoutOptions {
  proceso: string;
  gridId?: string;
  enabled?: boolean;
}

export interface UseGridLayoutReturn {
  layouts: GridLayoutItem[];
  selectedLayout: GridLayoutItem | null;
  loading: boolean;
  error: string | null;
  loadLayouts: () => Promise<void>;
  loadLastUsed: () => Promise<Record<string, unknown> | null>;
  setSelectedLayout: (layout: GridLayoutItem | null) => void;
  saveLayout: (layoutData: Record<string, unknown>) => Promise<GridLayoutItem>;
  saveLayoutAs: (layoutName: string, layoutData: Record<string, unknown>) => Promise<GridLayoutItem>;
  deleteLayout: (id: number) => Promise<void>;
  markAsUsed: (id: number) => Promise<void>;
}

export function useGridLayout({
  proceso,
  gridId = 'default',
  enabled = true,
}: UseGridLayoutOptions): UseGridLayoutReturn {
  const [layouts, setLayouts] = useState<GridLayoutItem[]>([]);
  const [selectedLayout, setSelectedLayout] = useState<GridLayoutItem | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const loadLayouts = useCallback(async () => {
    if (!enabled) return;
    setLoading(true);
    setError(null);
    try {
      const items = await gridLayoutService.listLayouts(proceso, gridId);
      setLayouts(items);
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Error al cargar layouts');
      setLayouts([]);
    } finally {
      setLoading(false);
    }
  }, [proceso, gridId, enabled]);

  const loadLastUsed = useCallback(async (): Promise<Record<string, unknown> | null> => {
    if (!enabled) return null;
    try {
      const layout = await gridLayoutService.getLastUsed(proceso, gridId);
      return layout?.layoutData ?? null;
    } catch {
      return null;
    }
  }, [proceso, gridId, enabled]);

  const saveLayout = useCallback(
    async (layoutData: Record<string, unknown>): Promise<GridLayoutItem> => {
      if (!selectedLayout) {
        throw new Error('Seleccione un layout para guardar o use Guardar como...');
      }
      const updated = await gridLayoutService.updateLayout(selectedLayout.id, { layoutData });
      setLayouts((prev) => prev.map((l) => (l.id === updated.id ? updated : l)));
      setSelectedLayout(updated);
      return updated;
    },
    [selectedLayout]
  );

  const saveLayoutAs = useCallback(
    async (layoutName: string, layoutData: Record<string, unknown>): Promise<GridLayoutItem> => {
      const created = await gridLayoutService.createLayout({
        proceso,
        gridId,
        layoutName,
        layoutData,
      });
      setLayouts((prev) => [...prev, created]);
      setSelectedLayout(created);
      return created;
    },
    [proceso, gridId]
  );

  const deleteLayout = useCallback(async (id: number) => {
    await gridLayoutService.deleteLayout(id);
    setLayouts((prev) => prev.filter((l) => l.id !== id));
    if (selectedLayout?.id === id) {
      setSelectedLayout(null);
    }
  }, [selectedLayout]);

  const markAsUsed = useCallback(async (id: number) => {
    await gridLayoutService.markLayoutAsUsed(id);
  }, []);

  useEffect(() => {
    loadLayouts();
  }, [loadLayouts]);

  return {
    layouts,
    selectedLayout,
    loading,
    error,
    loadLayouts,
    loadLastUsed,
    setSelectedLayout,
    saveLayout,
    saveLayoutAs,
    deleteLayout,
    markAsUsed,
  };
}
