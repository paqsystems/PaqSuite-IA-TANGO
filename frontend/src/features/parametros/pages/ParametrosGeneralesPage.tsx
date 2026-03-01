/**
 * Página de parámetros generales por módulo.
 *
 * @see docs/04-tareas/000-Generalidades/TR-007-Parametros-generales.md
 */

import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { listParametros, updateParametro, type ParametroItem } from '../../../shared/services/parametrosGral.service';
import './ParametrosGeneralesPage.css';

function getValorEditable(item: ParametroItem): string | number | boolean {
  switch (item.tipoValor) {
    case 'I':
      return item.valorInt ?? '';
    case 'B':
      return item.valorBool ?? false;
    case 'N':
      return item.valorDecimal ?? '';
    case 'D':
      return item.valorDateTime ? item.valorDateTime.slice(0, 19) : '';
    case 'T':
      return item.valorText ?? '';
    default:
      return item.valorString ?? '';
  }
}

export function ParametrosGeneralesPage(): React.ReactElement {
  const { programa } = useParams<{ programa: string }>();
  const [items, setItems] = useState<ParametroItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [edits, setEdits] = useState<Record<string, string | number | boolean>>({});
  const [saving, setSaving] = useState<string | null>(null);

  useEffect(() => {
    if (!programa) {
      setError('Programa no especificado');
      setLoading(false);
      return;
    }
    listParametros(programa)
      .then((data) => {
        setItems(data);
        const initial: Record<string, string | number | boolean> = {};
        data.forEach((it) => {
          initial[it.clave] = getValorEditable(it);
        });
        setEdits(initial);
      })
      .catch((e) => setError(e instanceof Error ? e.message : 'Error al cargar'))
      .finally(() => setLoading(false));
  }, [programa]);

  const handleChange = (clave: string, value: string | number | boolean) => {
    setEdits((prev) => ({ ...prev, [clave]: value }));
  };

  const handleSave = async (item: ParametroItem) => {
    const valor = edits[item.clave];
    if (valor === undefined) return;
    setSaving(item.clave);
    try {
      const updated = await updateParametro(item.programa, item.clave, valor);
      setItems((prev) => prev.map((p) => (p.clave === updated.clave ? updated : p)));
      setEdits((prev) => ({ ...prev, [item.clave]: getValorEditable(updated) }));
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Error al guardar');
    } finally {
      setSaving(null);
    }
  };

  if (!programa) {
    return (
      <div className="parametros-page" data-testid="parametrosGral">
        <h1>Parámetros generales</h1>
        <p>Programa no especificado.</p>
      </div>
    );
  }

  if (loading) {
    return (
      <div className="parametros-page" data-testid="parametrosGral">
        <h1>Parámetros generales - {programa}</h1>
        <p>Cargando...</p>
      </div>
    );
  }

  if (error) {
    return (
      <div className="parametros-page" data-testid="parametrosGral">
        <h1>Parámetros generales - {programa}</h1>
        <div className="parametros-error" role="alert">
          {error}
        </div>
      </div>
    );
  }

  return (
    <div className="parametros-page" data-testid="parametrosGral">
      <h1>Parámetros generales - {programa}</h1>
      <div className="parametros-grid" data-testid="parametrosGral.grid">
        <table>
          <thead>
            <tr>
              <th>Clave</th>
              <th>Valor</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            {items.map((item) => (
              <tr key={item.clave}>
                <td>{item.clave}</td>
                <td>
                  {item.tipoValor === 'B' ? (
                    <input
                      type="checkbox"
                      checked={Boolean(edits[item.clave])}
                      onChange={(e) => handleChange(item.clave, e.target.checked)}
                      data-testid={`parametrosGral.valor.${item.clave}`}
                    />
                  ) : item.tipoValor === 'D' ? (
                    <input
                      type="datetime-local"
                      value={String(edits[item.clave] ?? '').slice(0, 16)}
                      onChange={(e) => handleChange(item.clave, e.target.value)}
                      data-testid={`parametrosGral.valor.${item.clave}`}
                    />
                  ) : item.tipoValor === 'T' ? (
                    <textarea
                      value={String(edits[item.clave] ?? '')}
                      onChange={(e) => handleChange(item.clave, e.target.value)}
                      rows={3}
                      data-testid={`parametrosGral.valor.${item.clave}`}
                    />
                  ) : (
                    <input
                      type={item.tipoValor === 'I' ? 'number' : item.tipoValor === 'N' ? 'number' : 'text'}
                      value={String(edits[item.clave] ?? '')}
                      onChange={(e) => {
                        const v = e.target.value;
                        if (item.tipoValor === 'I') handleChange(item.clave, v === '' ? 0 : parseInt(v, 10));
                        else if (item.tipoValor === 'N') handleChange(item.clave, v === '' ? 0 : parseFloat(v));
                        else handleChange(item.clave, v);
                      }}
                      data-testid={`parametrosGral.valor.${item.clave}`}
                    />
                  )}
                </td>
                <td>
                  <button
                    type="button"
                    onClick={() => handleSave(item)}
                    disabled={saving === item.clave}
                  >
                    {saving === item.clave ? 'Guardando...' : 'Guardar'}
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      {items.length === 0 && (
        <p className="parametros-empty">No hay parámetros definidos para este módulo.</p>
      )}
    </div>
  );
}
