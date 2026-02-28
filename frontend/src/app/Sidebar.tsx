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
}

function SidebarContent({ onClose }: { onClose?: () => void }): React.ReactElement {
  const user = getUserData();

  if (!user) {
    return <nav className="sidebar-nav" aria-label="Navegación principal" />;
  }

  const link = (
    to: string,
    label: string,
    testId: string,
    end?: boolean
  ) => (
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

  return (
    <nav className="sidebar-nav" aria-label="Navegación principal">
      <ul className="sidebar-list">
        <li>{link('/', 'Inicio', 'app.sidebar.inicio', true)}</li>
        <li>{link('/perfil', 'Perfil', 'app.profileLink', true)}</li>
      </ul>
    </nav>
  );
}

export function Sidebar({ isOpen, onClose }: SidebarProps): React.ReactElement {
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
        <SidebarContent onClose={onClose} />
      </aside>
    </>
  );
}
