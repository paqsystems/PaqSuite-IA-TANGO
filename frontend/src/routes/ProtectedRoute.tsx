/**
 * Component: ProtectedRoute
 * 
 * Componente de orden superior que protege rutas que requieren autenticación.
 * Redirige a /login si no hay token de autenticación.
 * 
 * @see TR-001(MH)-login-de-empleado.md
 */

import React from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { isAuthenticated } from '../shared/utils/tokenStorage';

interface ProtectedRouteProps {
  children: React.ReactNode;
}

/**
 * Protege una ruta, redirigiendo a /login si no hay autenticación
 */
export function ProtectedRoute({ children }: ProtectedRouteProps): React.ReactElement {
  const location = useLocation();
  
  if (!isAuthenticated()) {
    // Guardar la ubicación actual para redirigir después del login
    return <Navigate to="/login" state={{ from: location }} replace />;
  }
  
  return <>{children}</>;
}

export default ProtectedRoute;
