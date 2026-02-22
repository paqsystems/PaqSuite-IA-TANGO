/**
 * Component: EmpleadosDetallePage
 *
 * Vista de detalle de un empleado (solo supervisores). TR-022(SH).
 * Muestra datos del empleado, estadísticas opcionales (total tareas) y fechas.
 * Acciones: Editar (navega a edición) y Eliminar (modal de confirmación).
 *
 * @see TR-022(SH)-visualización-de-detalle-de-empleado.md
 */

import React, { useCallback, useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { getEmpleadoDetalle, deleteEmpleado, EmpleadoDetalleItem } from '../services/empleado.service';
import './EmpleadosDetallePage.css';

type PageState = 'loading' | 'error' | 'success';

function formatOptionalDate(iso?: string): string {
  if (!iso) return '—';
  try {
    const d = new Date(iso);
    return Number.isNaN(d.getTime()) ? '—' : d.toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' });
  } catch {
    return '—';
  }
}

export function EmpleadosDetallePage(): React.ReactElement {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const empleadoId = id ? parseInt(id, 10) : NaN;

  const [empleado, setEmpleado] = useState<EmpleadoDetalleItem | null>(null);
  const [pageState, setPageState] = useState<PageState>('loading');
  const [errorMessage, setErrorMessage] = useState('');
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [deleteLoading, setDeleteLoading] = useState(false);
  const [deleteError, setDeleteError] = useState('');

  const loadDetalle = useCallback(async (eid: number) => {
    setPageState('loading');
    setErrorMessage('');
    const result = await getEmpleadoDetalle(eid);
    if (result.success && result.data) {
      setEmpleado(result.data);
      setPageState('success');
    } else {
      setPageState('error');
      setErrorMessage(result.errorMessage ?? 'Error al cargar el empleado');
    }
  }, []);

  useEffect(() => {
    if (!Number.isNaN(empleadoId) && empleadoId > 0) {
      loadDetalle(empleadoId);
    } else {
      setPageState('error');
      setErrorMessage('ID de empleado inválido');
    }
  }, [empleadoId, loadDetalle]);

  const handleEditar = () => {
    if (empleadoId > 0) navigate(`/empleados/${empleadoId}/editar`);
  };

  const handleEliminarClick = () => {
    setShowDeleteModal(true);
    setDeleteError('');
  };

  const handleDeleteCancel = () => {
    setShowDeleteModal(false);
    setDeleteError('');
  };

  const handleDeleteConfirm = async () => {
    if (Number.isNaN(empleadoId) || empleadoId <= 0) return;
    setDeleteLoading(true);
    setDeleteError('');
    const result = await deleteEmpleado(empleadoId);
    setDeleteLoading(false);
    if (result.success) {
      setShowDeleteModal(false);
      navigate('/empleados');
    } else {
      setDeleteError(result.errorMessage ?? 'Error al eliminar empleado');
    }
  };

  const handleVolver = () => {
    navigate('/empleados');
  };

  if (pageState === 'loading') {
    return (
      <div className="empleados-detalle-page" data-testid="empleados.detail.container">
        <div className="empleados-detalle-loading" data-testid="empleados.detail.loading" role="status">
          Cargando empleado...
        </div>
      </div>
    );
  }

  if (pageState === 'error' || !empleado) {
    return (
      <div className="empleados-detalle-page" data-testid="empleados.detail.container">
        <div className="empleados-detalle-error" role="alert" data-testid="empleados.detail.error">
          {errorMessage}
        </div>
        <button
          type="button"
          className="empleados-detalle-btn-back"
          onClick={handleVolver}
          data-testid="empleados.detail.back"
        >
          Volver al listado
        </button>
      </div>
    );
  }

  return (
    <div className="empleados-detalle-page" data-testid="empleados.detail.container">
      <header className="empleados-detalle-header">
        <h1 className="empleados-detalle-title">Detalle del empleado</h1>
        <div className="empleados-detalle-actions">
          <button
            type="button"
            className="empleados-detalle-btn-edit"
            onClick={handleEditar}
            data-testid="empleados.detail.edit"
            aria-label="Editar empleado"
          >
            Editar
          </button>
          <button
            type="button"
            className="empleados-detalle-btn-delete"
            onClick={handleEliminarClick}
            data-testid="empleados.detail.delete"
            aria-label="Eliminar empleado"
          >
            Eliminar
          </button>
          <button
            type="button"
            className="empleados-detalle-btn-back"
            onClick={handleVolver}
            data-testid="empleados.detail.backList"
          >
            Volver al listado
          </button>
        </div>
      </header>

      <section className="empleados-detalle-card" aria-label="Datos del empleado">
        <h2 className="empleados-detalle-section-title">Datos del empleado</h2>
        <dl className="empleados-detalle-dl">
          <div className="empleados-detalle-row">
            <dt>Código</dt>
            <dd data-testid="empleados.detail.code">{empleado.code}</dd>
          </div>
          <div className="empleados-detalle-row">
            <dt>Nombre</dt>
            <dd data-testid="empleados.detail.nombre">{empleado.nombre}</dd>
          </div>
          <div className="empleados-detalle-row">
            <dt>Email</dt>
            <dd data-testid="empleados.detail.email">{empleado.email ?? '—'}</dd>
          </div>
          <div className="empleados-detalle-row">
            <dt>Supervisor</dt>
            <dd data-testid="empleados.detail.supervisor">{empleado.supervisor ? 'Sí' : 'No'}</dd>
          </div>
          <div className="empleados-detalle-row">
            <dt>Estado</dt>
            <dd data-testid="empleados.detail.activo">{empleado.activo ? 'Activo' : 'Inactivo'}</dd>
          </div>
          <div className="empleados-detalle-row">
            <dt>Inhabilitado</dt>
            <dd data-testid="empleados.detail.inhabilitado">{empleado.inhabilitado ? 'Sí' : 'No'}</dd>
          </div>
          {empleado.total_tareas !== undefined && (
            <div className="empleados-detalle-row">
              <dt>Total de tareas registradas</dt>
              <dd data-testid="empleados.detail.totalTareas">{empleado.total_tareas}</dd>
            </div>
          )}
          {(empleado.created_at !== undefined || empleado.updated_at !== undefined) && (
            <>
              <div className="empleados-detalle-row">
                <dt>Fecha de creación</dt>
                <dd data-testid="empleados.detail.createdAt">{formatOptionalDate(empleado.created_at)}</dd>
              </div>
              <div className="empleados-detalle-row">
                <dt>Última actualización</dt>
                <dd data-testid="empleados.detail.updatedAt">{formatOptionalDate(empleado.updated_at)}</dd>
              </div>
            </>
          )}
        </dl>
      </section>

      {showDeleteModal && (
        <div
          className="empleados-delete-modal-overlay"
          role="dialog"
          aria-modal="true"
          aria-labelledby="empleados-detail-delete-modal-title"
          data-testid="empleados.detail.delete.modal"
        >
          <div className="empleados-delete-modal">
            <h2 id="empleados-detail-delete-modal-title" className="empleados-delete-modal-title">
              Eliminar empleado
            </h2>
            <p className="empleados-delete-modal-text">
              ¿Está seguro de eliminar el empleado <strong data-testid="empleados.detail.delete.code">{empleado.code}</strong> – <strong data-testid="empleados.detail.delete.nombre">{empleado.nombre}</strong>?
            </p>
            {deleteError && (
              <div className="empleados-delete-modal-error" role="alert" data-testid="empleados.detail.delete.error">
                {deleteError}
              </div>
            )}
            <div className="empleados-delete-modal-actions">
              <button
                type="button"
                className="empleados-delete-modal-btn-cancel"
                onClick={handleDeleteCancel}
                disabled={deleteLoading}
                data-testid="empleados.detail.delete.cancel"
              >
                Cancelar
              </button>
              <button
                type="button"
                className="empleados-page-btn-delete"
                onClick={handleDeleteConfirm}
                disabled={deleteLoading}
                data-testid="empleados.detail.delete.confirm"
                aria-busy={deleteLoading}
              >
                {deleteLoading ? 'Eliminando...' : 'Confirmar'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default EmpleadosDetallePage;
