/**
 * AppLayout – PaqSystems Main Shell
 *
 * Layout según diseño Figma "PaqSystems UI – Main Shell":
 * Header oscuro, sidebar claro, contenido principal, footer oscuro.
 *
 * @see docs/design/paqsystems-main-shell-design.md
 */

import React, { useState } from 'react';
import { Outlet, useNavigate, useLocation } from 'react-router-dom';
import { getUserData } from '../shared/utils/tokenStorage';
import { logout } from '../features/auth/services/auth.service';
import { getEmpresa } from '../config/sessionContext';
import { appVersion } from '../config/appVersion';
import { t } from '../shared/i18n';
import { LanguageSelector } from '../shared/components/LanguageSelector';
import { Sidebar } from './Sidebar';
import './AppLayout.css';

function getAvatarInitials(nombre: string, userCode: string): string {
  const parts = nombre.trim().split(/\s+/);
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase().slice(0, 2);
  }
  return userCode.slice(0, 2).toUpperCase();
}

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

  const avatarInitials = getAvatarInitials(user.nombre, user.userCode);

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
          <div className="app-layout-brand">
            <div className="app-layout-logo" aria-hidden>
              PQ
            </div>
            <h1 className="app-layout-title">{t('app.layout.title', 'PaqSystems')}</h1>
            <span className="app-layout-company">{getEmpresa()}</span>
          </div>
          <button
            type="button"
            onClick={handleVolver}
            className="app-layout-volver"
            data-testid="app.volverButton"
            aria-label={t('app.layout.volverAria', 'Volver al panel')}
          >
            {isPanel ? t('app.layout.panel', 'Panel') : t('app.layout.volver', 'Volver')}
          </button>
        </div>
        <div className="app-layout-user-info">
          <LanguageSelector />
          <span className="app-layout-user-name">{user.nombre}</span>
          <div className="app-layout-user-avatar" aria-hidden>
            {avatarInitials}
          </div>
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
      <footer className="app-layout-footer" role="contentinfo">
        <span className="app-layout-footer-role">
          {user.esSupervisor ? t('app.layout.roleSupervisor', 'A SUPERVISOR') : t('app.layout.roleUser', 'A USUARIO')}
        </span>
        <span className="app-layout-footer-version">v{appVersion}</span>
      </footer>
    </div>
  );
}
