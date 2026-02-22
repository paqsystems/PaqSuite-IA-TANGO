/**
 * TiposTareaDetallePage – Detalle de tipo de tarea. TR-027(SH).
 * Muestra datos del tipo y, si no es genérico, clientes asociados. Acciones Editar y Eliminar.
 */
import React, { useState, useEffect, useCallback } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { getTipoTareaConClientes, deleteTipoTarea, ERROR_EN_USO } from '../services/tipoTarea.service';
import type { TipoTareaListItem } from '../services/tipoTarea.service';
import './TiposTareaPage.css';

interface TipoConClientes extends TipoTareaListItem {
  clientes?: { id: number; code: string; nombre: string }[];
}

export function TiposTareaDetallePage(): React.ReactElement {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const tipoId = id ? parseInt(id, 10) : NaN;
  const [tipo, setTipo] = useState<TipoConClientes | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [deleteLoading, setDeleteLoading] = useState(false);
  const [deleteError, setDeleteError] = useState('');

  const loadTipo = useCallback(async (tid: number) => {
    setLoading(true);
    setError('');
    const result = await getTipoTareaConClientes(tid);
    setLoading(false);
    if (result.success && result.data) {
      setTipo(result.data as TipoConClientes);
    } else {
      setError(result.errorMessage ?? 'Error al cargar tipo de tarea');
    }
  }, []);

  useEffect(() => {
    if (Number.isNaN(tipoId)) {
      setError('ID inválido');
      setLoading(false);
      return;
    }
    loadTipo(tipoId);
  }, [tipoId, loadTipo]);

  const handleDeleteConfirm = async () => {
    if (!tipo) return;
    setDeleteLoading(true);
    setDeleteError('');
    const result = await deleteTipoTarea(tipo.id);
    setDeleteLoading(false);
    if (result.success) {
      setShowDeleteModal(false);
      navigate('/tipos-tarea');
    } else {
      setDeleteError(
        result.errorCode === ERROR_EN_USO
          ? 'No se puede eliminar el tipo de tarea porque está en uso (tareas o clientes asociados).'
          : (result.errorMessage ?? 'Error al eliminar')
      );
    }
  };

  if (loading) {
    return (
      <div className="tipos-tarea-page" data-testid="tipoTareaDetalle.container">
        <p className="tipos-tarea-page-loading">Cargando...</p>
      </div>
    );
  }

  if (error || !tipo) {
    return (
      <div className="tipos-tarea-page" data-testid="tipoTareaDetalle.container">
        <p className="tipos-tarea-page-error">{error || 'Tipo no encontrado'}</p>
        <button type="button" className="tipos-tarea-btn-cancel" onClick={() => navigate('/tipos-tarea')}>
          Volver al listado
        </button>
      </div>
    );
  }

  return (
    <div className="tipos-tarea-page" data-testid="tipoTareaDetalle.container">
      <header className="tipos-tarea-page-header">
        <h1 className="tipos-tarea-page-title">Detalle del tipo de tarea</h1>
        <div style={{ display: 'flex', gap: '0.5rem' }}>
          <button
            type="button"
            className="tipos-tarea-page-btn-edit"
            onClick={() => navigate(`/tipos-tarea/${tipo.id}/editar`)}
            data-testid="tipoTareaDetalle.editar"
            aria-label="Editar tipo de tarea"
          >
            Editar
          </button>
          <button
            type="button"
            className="tipos-tarea-page-btn-delete"
            onClick={() => { setShowDeleteModal(true); setDeleteError(''); }}
            data-testid="tipoTareaDetalle.eliminar"
            aria-label="Eliminar tipo de tarea"
          >
            Eliminar
          </button>
        </div>
      </header>

      <section className="tipos-tarea-detalle-section">
        <h3>Datos del tipo</h3>
        <dl className="tipos-tarea-detalle-dl">
          <dt>Código</dt>
          <dd>{tipo.code}</dd>
          <dt>Descripción</dt>
          <dd>{tipo.descripcion}</dd>
          <dt>Genérico</dt>
          <dd>{tipo.is_generico ? 'Sí' : 'No'}</dd>
          <dt>Por defecto</dt>
          <dd>{tipo.is_default ? 'Sí' : 'No'}</dd>
          <dt>Estado</dt>
          <dd>{tipo.activo ? 'Activo' : 'Inactivo'}</dd>
          <dt>Inhabilitado</dt>
          <dd>{tipo.inhabilitado ? 'Sí' : 'No'}</dd>
        </dl>
      </section>

      {!tipo.is_generico && tipo.clientes !== undefined && (
        <section className="tipos-tarea-detalle-section" data-testid="tipoTareaDetalle.clientesAsociados">
          <h3>Clientes asociados</h3>
          {tipo.clientes.length === 0 ? (
            <p>Ningún cliente tiene asignado este tipo de tarea.</p>
          ) : (
            <ul className="tipos-tarea-detalle-clientes-list">
              {tipo.clientes.map((c) => (
                <li key={c.id}>
                  {c.code} – {c.nombre}
                </li>
              ))}
            </ul>
          )}
        </section>
      )}

      {showDeleteModal && (
        <div className="tipos-tarea-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="tipo-tarea-detalle-delete-title">
          <div className="tipos-tarea-modal">
            <h2 id="tipo-tarea-detalle-delete-title">Confirmar eliminación</h2>
            <p>
              ¿Eliminar el tipo de tarea <strong>{tipo.code}</strong> – {tipo.descripcion}?
            </p>
            {deleteError && <p className="tipos-tarea-modal-error" role="alert">{deleteError}</p>}
            <div className="tipos-tarea-modal-actions">
              <button type="button" className="tipos-tarea-btn-cancel" onClick={() => { setShowDeleteModal(false); setDeleteError(''); }} disabled={deleteLoading}>
                Cancelar
              </button>
              <button type="button" className="tipos-tarea-btn-confirm-delete" onClick={handleDeleteConfirm} disabled={deleteLoading}>
                {deleteLoading ? 'Eliminando...' : 'Eliminar'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
