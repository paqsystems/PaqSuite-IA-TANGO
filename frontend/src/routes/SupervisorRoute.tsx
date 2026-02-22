/**
 * Component: SupervisorRoute
 *
 * Protege rutas que solo pueden ver supervisores.
 * Redirige a / si el usuario no es supervisor.
 *
 * @see TR-034(MH)-visualización-de-lista-de-todas-las-tareas-supervisor.md
 */

import React from 'react';
import { Navigate } from 'react-router-dom';
import { isAuthenticated, getUserData } from '../shared/utils/tokenStorage';

interface SupervisorRouteProps {
  children: React.ReactNode;
}

/**
 * Protege una ruta, permitiendo acceso solo a supervisores
 */
export function SupervisorRoute({ children }: SupervisorRouteProps): React.ReactElement {
  if (!isAuthenticated()) {
    return <Navigate to="/login" replace />;
  }

  const user = getUserData();
  if (!user || !user.esSupervisor) {
    return <Navigate to="/" replace />;
  }

  return <>{children}</>;
}

export default SupervisorRoute;
