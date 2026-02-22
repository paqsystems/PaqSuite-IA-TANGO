/**
 * Component: AppLayout
 *
 * Layout con header común y menú lateral (TR-056) para todas las pantallas autenticadas.
 *
 * @see docs/frontend/frontend-specifications.md (Layout general y navegación)
 * @see docs/hu-historias/HU-056(SH)-menú-lateral-de-navegación.md
 */

import React, { useState } from 'react';
import { Outlet, useNavigate, useLocation } from 'react-router-dom';
import { getUserData } from '../shared/utils/tokenStorage';
import { logout } from '../features/auth/services/auth.service';
import { t } from '../shared/i18n';
import { LanguageSelector } from '../shared/components/LanguageSelector';
import { Sidebar } from './Sidebar';
import './AppLayout.css';

export function AppLayout(): React.ReactElement {
  const navigate = useNavigate();
  const location = useLocation();
  const user = getUserData();
  const [isLoggingOut, setIsLoggingOut] = useState(false);
  const [isSidebarOpen, setIsSidebarOpen] = useState(false);

  const handleLogout = async () => {
    setIsLoggingOut(true);
    try {
      await logout();
      navigate('/login');
    } finally {
      setIsLoggingOut(false);
    }
  };

  const handleVolver = () => {
    navigate('/');
  };

  const isPanel = location.pathname === '/';

  if (!user) {
    return <div className="app-layout-loading">{t('app.layout.loading', 'Cargando...')}</div>;
  }

  return (
    <div className="app-layout" data-testid="app.layout">
      <header className="app-layout-header" role="banner">
        <div className="app-layout-header-left">
          <button
            type="button"
            className="app-layout-menu-toggle"
            onClick={() => setIsSidebarOpen((v) => !v)}
            data-testid="app.sidebarToggle"
            aria-label={t('app.layout.menuAria', 'Abrir o cerrar menú lateral')}
            aria-expanded={isSidebarOpen}
          >
            <span className="app-layout-menu-icon" aria-hidden>☰</span>
          </button>
          <h1 className="app-layout-title">{t('app.layout.title', 'Sistema de Registro de Tareas')}</h1>
          <button
            type="button"
            onClick={handleVolver}
            className="app-layout-volver"
            data-testid="app.volverButton"
            aria-label={t('app.layout.volverAria', 'Volver al panel del usuario')}
          >
            {isPanel ? t('app.layout.panel', 'Panel') : t('app.layout.volver', 'Volver')}
          </button>
        </div>
        <div className="app-layout-user-info">
          <LanguageSelector />
          <span className="app-layout-user-name">{user.nombre}</span>
          {user.esSupervisor && (
            <span className="app-layout-supervisor-badge" data-testid="app.supervisorBadge">
              {t('app.layout.supervisor', 'Supervisor')}
            </span>
          )}
          <button
            type="button"
            onClick={handleLogout}
            className="app-layout-logout"
            data-testid="app.logoutButton"
            disabled={isLoggingOut}
            aria-label={t('app.layout.logoutAria', 'Cerrar sesión')}
          >
            {isLoggingOut ? t('app.layout.loggingOut', 'Cerrando...') : t('app.layout.logout', 'Cerrar Sesión')}
          </button>
        </div>
      </header>
      <div className="app-layout-body">
        <Sidebar isOpen={isSidebarOpen} onClose={() => setIsSidebarOpen(false)} />
        <main className="app-layout-main">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
