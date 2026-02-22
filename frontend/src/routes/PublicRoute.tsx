/**
 * Component: PublicRoute
 * 
 * Componente para rutas públicas que redirige al dashboard si el usuario
 * ya está autenticado (ej: página de login).
 * 
 * @see TR-001(MH)-login-de-empleado.md
 */

import React from 'react';
import { Navigate } from 'react-router-dom';
import { isAuthenticated } from '../shared/utils/tokenStorage';

interface PublicRouteProps {
  children: React.ReactNode;
}

/**
 * Ruta pública que redirige a home si ya hay autenticación
 */
export function PublicRoute({ children }: PublicRouteProps): React.ReactElement {
  if (isAuthenticated()) {
    // Ya autenticado, redirigir al dashboard
    return <Navigate to="/" replace />;
  }
  
  return <>{children}</>;
}

export default PublicRoute;
