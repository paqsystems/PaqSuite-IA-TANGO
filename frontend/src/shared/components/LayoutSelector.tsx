/**
 * Selector de layouts persistentes para grillas DevExtreme.
 *
 * @see docs/04-tareas/000-Generalidades/TR-001-layouts-grilla.md
 */

import React, { useState } from 'react';
import type { GridLayoutItem } from '../services/gridLayout.service';
import type { UseGridLayoutReturn } from '../hooks/useGridLayout';

export interface LayoutSelectorProps {
  proceso: string;
  gridId?: string;
  testIdBase: string;
  layout: UseGridLayoutReturn;
  getGridState: () => Record<string, unknown> | null;
  /** Aplica un layout al grid (cuando el usuario selecciona uno). */
  applyState?: (state: Record<string, unknown>) => void;
}

export function LayoutSelector({
  proceso: _proceso,
  gridId: _gridId = 'default',
  testIdBase,
  layout,
  getGridState,
  applyState,
}: LayoutSelectorProps): React.ReactElement {
  const [saveAsName, setSaveAsName] = useState('');
  const [showSaveAs, setShowSaveAs] = useState(false);
  const [actionError, setActionError] = useState<string | null>(null);

  const handleSelectLayout = async (item: GridLayoutItem) => {
    layout.setSelectedLayout(item);
    setActionError(null);
    if (item.layoutData && applyState) {
      applyState(item.layoutData);
    }
    try {
      await layout.markAsUsed(item.id);
    } catch {
      setActionError('No se pudo registrar el uso');
    }
  };

  const handleSave = async () => {
    setActionError(null);
    const state = getGridState();
    if (!state) {
      setActionError('No se pudo obtener el estado de la grilla');
      return;
    }
    try {
      if (layout.selectedLayout) {
        await layout.saveLayout(state);
      } else {
        setShowSaveAs(true);
        return;
      }
    } catch (e) {
      setActionError(e instanceof Error ? e.message : 'Error al guardar');
    }
  };

  const handleSaveAs = async () => {
    setActionError(null);
    const name = saveAsName.trim();
    if (!name) {
      setActionError('Ingrese un nombre');
      return;
    }
    const state = getGridState();
    if (!state) {
      setActionError('No se pudo obtener el estado de la grilla');
      return;
    }
    try {
      await layout.saveLayoutAs(name, state);
      setSaveAsName('');
      setShowSaveAs(false);
    } catch (e) {
      setActionError(e instanceof Error ? e.message : 'Error al guardar');
    }
  };

  const handleDelete = async () => {
    if (!layout.selectedLayout?.isOwner) return;
    setActionError(null);
    try {
      await layout.deleteLayout(layout.selectedLayout.id);
    } catch (e) {
      setActionError(e instanceof Error ? e.message : 'Error al eliminar');
    }
  };

  const layoutSelectorId = `${testIdBase}.layoutSelector`;

  return (
    <div className="layout-selector" data-testid={layoutSelectorId}>
      <select
        data-testid={`${layoutSelectorId}.select`}
        value={layout.selectedLayout?.id ?? ''}
        onChange={(e) => {
          const id = e.target.value ? Number(e.target.value) : null;
          const item = id ? layout.layouts.find((l) => l.id === id) ?? null : null;
          if (item) handleSelectLayout(item);
          else layout.setSelectedLayout(null);
        }}
        disabled={layout.loading}
      >
        <option value="">-- Seleccionar layout --</option>
        {layout.layouts.map((l) => (
          <option
            key={l.id}
            value={l.id}
            data-testid={`${layoutSelectorId}.option.${l.layoutName.replace(/\s/g, '_')}`}
          >
            {l.layoutName} {l.isOwner ? '(mío)' : ''}
          </option>
        ))}
      </select>
      <button
        type="button"
        data-testid={`${layoutSelectorId}.save`}
        onClick={handleSave}
        disabled={layout.loading}
      >
        Guardar
      </button>
      <button
        type="button"
        data-testid={`${layoutSelectorId}.saveAs`}
        onClick={() => setShowSaveAs(true)}
        disabled={layout.loading}
      >
        Guardar como...
      </button>
      {layout.selectedLayout?.isOwner && (
        <button
          type="button"
          data-testid={`${layoutSelectorId}.delete`}
          onClick={handleDelete}
          disabled={layout.loading}
        >
          Eliminar
        </button>
      )}
      {showSaveAs && (
        <div className="layout-selector-save-as">
          <input
            type="text"
            placeholder="Nombre del layout"
            value={saveAsName}
            onChange={(e) => setSaveAsName(e.target.value)}
            data-testid={`${layoutSelectorId}.saveAs.input`}
          />
          <button type="button" onClick={handleSaveAs} data-testid={`${layoutSelectorId}.saveAs.confirm`}>
            Crear
          </button>
          <button type="button" onClick={() => { setShowSaveAs(false); setSaveAsName(''); }}>
            Cancelar
          </button>
        </div>
      )}
      {actionError && (
        <span className="layout-selector-error" role="alert">
          {actionError}
        </span>
      )}
    </div>
  );
}
