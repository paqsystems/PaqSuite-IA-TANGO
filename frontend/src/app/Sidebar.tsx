/**
 * Component: Sidebar
 *
 * Menú lateral de navegación.
 */

import React from 'react';
import { NavLink } from 'react-router-dom';
import { getUserData } from '../shared/utils/tokenStorage';
import './Sidebar.css';

export interface SidebarProps {
  isOpen: boolean;
  onClose?: () => void;
  openInNewTab?: boolean;
}

function SidebarContent({ onClose, openInNewTab }: { onClose?: () => void; openInNewTab?: boolean }): React.ReactElement {
  const user = getUserData();

  if (!user) {
    return <nav className="sidebar-nav" aria-label="Navegación principal" />;
  }

  const link = (
    to: string,
    label: string,
    testId: string,
    end?: boolean
  ) => {
    if (openInNewTab) {
      return (
        <a
          href={to}
          className="sidebar-link"
          data-testid={testId}
          onClick={onClose}
          target="_blank"
          rel="noopener noreferrer"
        >
          {label}
        </a>
      );
    }
    return (
      <NavLink
        to={to}
        end={end}
        className={({ isActive }) =>
          'sidebar-link' + (isActive ? ' sidebar-link-active' : '')
        }
        data-testid={testId}
        onClick={onClose}
      >
        {label}
      </NavLink>
    );
  };

  const esAdmin = user.esAdmin ?? user.esSupervisor;

  return (
    <nav className="sidebar-nav" aria-label="Navegación principal">
      <ul className="sidebar-list">
        <li>{link('/', 'Inicio', 'app.sidebar.inicio', true)}</li>
        <li>{link('/perfil', 'Perfil', 'app.profileLink', true)}</li>
        {esAdmin && (
          <>
            <li className="sidebar-section">Administración</li>
            <li>{link('/admin/usuarios', 'Usuarios', 'app.sidebar.admin.usuarios', true)}</li>
            <li>{link('/admin/empresas', 'Empresas', 'app.sidebar.admin.empresas', true)}</li>
            <li>{link('/admin/grupos-empresarios', 'Grupos empresarios', 'app.sidebar.admin.grupos-empresarios', true)}</li>
            <li>{link('/admin/roles', 'Roles', 'app.sidebar.admin.roles', true)}</li>
            <li>{link('/admin/permisos', 'Permisos', 'app.sidebar.admin.permisos', true)}</li>
          </>
        )}
      </ul>
    </nav>
  );
}

export function Sidebar({ isOpen, onClose, openInNewTab }: SidebarProps): React.ReactElement {
  return (
    <>
      <div
        className={'sidebar-overlay' + (isOpen ? ' sidebar-overlay-visible' : '')}
        onClick={onClose}
        onKeyDown={(e) => e.key === 'Escape' && onClose?.()}
        role="button"
        tabIndex={-1}
        aria-hidden={!isOpen}
      />
      <aside
        className={'sidebar' + (isOpen ? ' sidebar-open' : '')}
        data-testid="app.sidebar"
        aria-label="Menú lateral"
      >
        <SidebarContent onClose={onClose} openInNewTab={openInNewTab} />
      </aside>
    </>
  );
}
