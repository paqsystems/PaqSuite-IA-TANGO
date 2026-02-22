/**
 * Component: ClienteDetallePage
 *
 * Pantalla de detalle de cliente (solo supervisores). TR-013(SH).
 * Muestra datos del cliente, tipos de tarea asignados y acciones Editar / Eliminar.
 *
 * @see TR-013(SH)-visualización-de-detalle-de-cliente.md
 */

import React, { useEffect, useState, useCallback } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import {
  getCliente,
  getTiposTareaCliente,
  deleteCliente,
  ClienteDetalleItem,
  TipoTareaItem,
  ERROR_TIENE_TAREAS,
} from '../services/client.service';
import './ClienteDetallePage.css';

export function ClienteDetallePage(): React.ReactElement {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const clienteId = id ? parseInt(id, 10) : NaN;

  const [cliente, setCliente] = useState<ClienteDetalleItem | null>(null);
  const [tiposTarea, setTiposTarea] = useState<TipoTareaItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [deleteModal, setDeleteModal] = useState(false);
  const [deleteLoading, setDeleteLoading] = useState(false);
  const [deleteError, setDeleteError] = useState('');

  const loadData = useCallback(async (cid: number) => {
    setLoading(true);
    setError('');
    const [clienteRes, tiposRes] = await Promise.all([
      getCliente(cid),
      getTiposTareaCliente(cid),
    ]);
    setLoading(false);
    if (clienteRes.success && clienteRes.data) {
      setCliente(clienteRes.data);
      if (tiposRes.success && tiposRes.data) {
        setTiposTarea(tiposRes.data);
      } else {
        setTiposTarea([]);
      }
    } else {
      setError(clienteRes.errorMessage ?? 'Error al cargar cliente');
      setCliente(null);
    }
  }, []);

  useEffect(() => {
    if (Number.isNaN(clienteId)) {
      setError('Identificador de cliente inválido');
      setLoading(false);
      return;
    }
    loadData(clienteId);
  }, [clienteId, loadData]);

  const handleDeleteClick = () => setDeleteModal(true);
  const handleDeleteCancel = () => {
    setDeleteModal(false);
    setDeleteError('');
  };

  const handleDeleteConfirm = async () => {
    if (!cliente) return;
    setDeleteLoading(true);
    setDeleteError('');
    const result = await deleteCliente(cliente.id);
    setDeleteLoading(false);
    if (result.success) {
      setDeleteModal(false);
      navigate('/clientes');
    } else {
      setDeleteError(result.errorMessage ?? 'Error al eliminar');
      if (result.errorCode === ERROR_TIENE_TAREAS) {
        setDeleteError('No se puede eliminar el cliente porque tiene tareas registradas.');
      }
    }
  };

  if (loading) {
    return (
      <div className="cliente-detalle-page" data-testid="clienteDetalle.container">
        <p className="cliente-detalle-loading" role="status">Cargando...</p>
      </div>
    );
  }

  if (error || !cliente) {
    return (
      <div className="cliente-detalle-page" data-testid="clienteDetalle.container">
        <p className="cliente-detalle-error" role="alert">{error || 'Cliente no encontrado'}</p>
        <button type="button" className="cliente-detalle-btn-back" onClick={() => navigate('/clientes')}>
          Volver al listado
        </button>
      </div>
    );
  }

  return (
    <div className="cliente-detalle-page" data-testid="clienteDetalle.container">
      <header className="cliente-detalle-header">
        <h1 className="cliente-detalle-title">Detalle del cliente</h1>
        <div className="cliente-detalle-actions">
          <button
            type="button"
            className="cliente-detalle-btn-editar"
            onClick={() => navigate(`/clientes/${cliente.id}/editar`)}
            data-testid="clienteDetalle.editar"
            aria-label="Editar cliente"
          >
            Editar
          </button>
          <button
            type="button"
            className="cliente-detalle-btn-eliminar"
            onClick={handleDeleteClick}
            data-testid="clienteDetalle.eliminar"
            aria-label="Eliminar cliente"
          >
            Eliminar
          </button>
        </div>
      </header>

      <section className="cliente-detalle-datos" aria-label="Datos del cliente">
        <dl className="cliente-detalle-dl">
          <dt>Código</dt>
          <dd>{cliente.code}</dd>
          <dt>Nombre</dt>
          <dd>{cliente.nombre}</dd>
          <dt>Tipo de cliente</dt>
          <dd>{cliente.tipo_cliente?.descripcion ?? '—'}</dd>
          <dt>Email</dt>
          <dd>{cliente.email ?? '—'}</dd>
          <dt>Estado</dt>
          <dd>{cliente.activo ? 'Activo' : 'Inactivo'}</dd>
          <dt>Inhabilitado</dt>
          <dd>{cliente.inhabilitado ? 'Sí' : 'No'}</dd>
          {cliente.created_at && (
            <>
              <dt>Fecha de creación</dt>
              <dd>{new Date(cliente.created_at).toLocaleString()}</dd>
            </>
          )}
          {cliente.updated_at && (
            <>
              <dt>Última actualización</dt>
              <dd>{new Date(cliente.updated_at).toLocaleString()}</dd>
            </>
          )}
        </dl>
      </section>

      <section className="cliente-detalle-tipos-tarea" aria-label="Tipos de tarea asignados">
        <h2 className="cliente-detalle-subtitle">Tipos de tarea asignados</h2>
        <ul className="cliente-detalle-tipos-list" data-testid="clienteDetalle.tiposTarea">
          {tiposTarea.length === 0 ? (
            <li className="cliente-detalle-tipos-empty">Ningún tipo de tarea asignado (se usan los genéricos).</li>
          ) : (
            tiposTarea.map((t) => (
              <li key={t.id}>
                {t.code} – {t.descripcion}
              </li>
            ))
          )}
        </ul>
      </section>

      <p className="cliente-detalle-back">
        <button type="button" className="cliente-detalle-btn-back" onClick={() => navigate('/clientes')}>
          Volver al listado
        </button>
      </p>

      {deleteModal && (
        <div className="cliente-detalle-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
          <div className="cliente-detalle-modal">
            <h2 id="delete-modal-title">Confirmar eliminación</h2>
            <p>
              ¿Eliminar el cliente <strong>{cliente.nombre}</strong> ({cliente.code})? Esta acción no se puede deshacer.
            </p>
            {deleteError && <p className="cliente-detalle-modal-error" role="alert">{deleteError}</p>}
            <div className="cliente-detalle-modal-actions">
              <button type="button" className="cliente-detalle-btn-cancel" onClick={handleDeleteCancel} disabled={deleteLoading}>
                Cancelar
              </button>
              <button
                type="button"
                className="cliente-detalle-btn-confirm-delete"
                onClick={handleDeleteConfirm}
                disabled={deleteLoading}
                data-testid="clienteDetalle.deleteConfirm"
              >
                {deleteLoading ? 'Eliminando...' : 'Eliminar'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
