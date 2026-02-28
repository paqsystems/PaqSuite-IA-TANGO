/**
 * Component: Dashboard
 *
 * Página principal después del login.
 */

import React from 'react';
import { getUserData } from '../shared/utils/tokenStorage';
import './Dashboard.css';

export function Dashboard(): React.ReactElement {
  const user = getUserData();

  if (!user) {
    return (
      <div className="dashboard-loading" data-testid="dashboard.loading">
        Cargando...
      </div>
    );
  }

  return (
    <div className="dashboard-container" data-testid="dashboard.container">
      <div className="dashboard-main">
        <div className="welcome-card">
          <h2>Bienvenido, {user.nombre}</h2>
          <p>Código de usuario: <strong>{user.userCode}</strong></p>
        </div>
      </div>
    </div>
  );
}

export default Dashboard;
