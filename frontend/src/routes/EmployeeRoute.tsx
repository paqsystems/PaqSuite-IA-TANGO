/**
 * Component: EmployeeRoute
 * 
 * Componente de orden superior que protege rutas que requieren ser empleado.
 * Redirige a /login si no hay autenticación o a / si el usuario es cliente.
 * 
 * Solo los usuarios con tipoUsuario === 'usuario' pueden acceder.
 * 
 * @see TR-028(MH)-carga-de-tarea-diaria.md
 */

import React from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { isAuthenticated, getUserData } from '../shared/utils/tokenStorage';

interface EmployeeRouteProps {
  children: React.ReactNode;
}

/**
 * Protege una ruta, permitiendo acceso solo a empleados
 */
export function EmployeeRoute({ children }: EmployeeRouteProps): React.ReactElement {
  const location = useLocation();
  
  if (!isAuthenticated()) {
    // Guardar la ubicación actual para redirigir después del login
    return <Navigate to="/login" state={{ from: location }} replace />;
  }
  
  const user = getUserData();
  
  // Si no hay datos de usuario o no es empleado, redirigir al dashboard
  if (!user || user.tipoUsuario !== 'usuario') {
    return <Navigate to="/" replace />;
  }
  
  return <>{children}</>;
}

export default EmployeeRoute;
