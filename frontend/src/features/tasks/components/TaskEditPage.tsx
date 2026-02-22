/**
 * Component: TaskEditPage
 *
 * Página de edición de tarea. Obtiene el id de la URL y renderiza TaskForm en modo edición.
 *
 * @see TR-029(MH)-edición-de-tarea-propia.md
 */

import React from 'react';
import { useParams, Navigate } from 'react-router-dom';
import { TaskForm } from './TaskForm';

export function TaskEditPage(): React.ReactElement {
  const { id } = useParams<{ id: string }>();
  const taskId = id != null ? parseInt(id, 10) : NaN;
  if (Number.isNaN(taskId) || taskId <= 0) {
    return <Navigate to="/tareas" replace />;
  }
  return <TaskForm taskId={taskId} />;
}

export default TaskEditPage;
