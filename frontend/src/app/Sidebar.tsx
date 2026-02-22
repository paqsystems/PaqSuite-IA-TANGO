/**
 * Component: Sidebar
 *
 * Menú lateral de navegación (TR-056 / HU-056).
 * Visible en todas las pantallas autenticadas; ítems por rol (empleado, supervisor, cliente).
 * Orden: Inicio, Perfil | Archivos | Partes | Informes (según docs/hu-historias/HU-056).
 *
 * @see docs/hu-historias/HU-056(SH)-menú-lateral-de-navegación.md
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
  const isSupervisor = user?.esSupervisor === true;
  const isEmpleado = user?.tipoUsuario === 'usuario';

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

      {isSupervisor && (
        <>
          <div className="sidebar-separator" role="presentation" />
          <div className="sidebar-group-label">Archivos</div>
          <ul className="sidebar-list">
            <li>{link('/clientes', 'Clientes', 'app.clientesLink')}</li>
            <li>{link('/empleados', 'Empleados', 'app.empleadosLink')}</li>
            <li>{link('/tipos-cliente', 'Tipos de Cliente', 'app.tiposClienteLink')}</li>
            <li>{link('/tipos-tarea', 'Tipos de Tarea', 'app.tiposTareaLink')}</li>
          </ul>
        </>
      )}

      {(isEmpleado || isSupervisor) && (
        <>
          <div className="sidebar-separator" role="presentation" />
          <div className="sidebar-group-label">Partes</div>
          <ul className="sidebar-list">
            {isEmpleado && (
              <>
                <li>{link('/tareas/nueva', 'Cargar Tarea', 'app.createTaskLink', true)}</li>
                <li>{link('/tareas', 'Mis Tareas', 'app.myTasksLink', true)}</li>
              </>
            )}
            {isSupervisor && (
              <>
                <li>{link('/tareas/todas', 'Todas las Tareas', 'app.todasTareasLink', true)}</li>
                <li>{link('/tareas/proceso-masivo', 'Proceso Masivo', 'app.procesoMasivoLink', true)}</li>
              </>
            )}
          </ul>
        </>
      )}

      <div className="sidebar-separator" role="presentation" />
      <div className="sidebar-group-label">Informes</div>
      <ul className="sidebar-list">
        <li>{link('/informes/consulta-detallada', 'Consulta Detallada', 'app.consultaDetalladaLink', true)}</li>
        <li>{link('/informes/tareas-por-cliente', 'Tareas por Cliente', 'app.tareasPorClienteLink', true)}</li>
        <li>{link('/informes/tareas-por-fecha', 'Tareas por Fecha', 'app.tareasPorFechaLink', true)}</li>
        {isSupervisor && (
          <>
            <li>{link('/informes/tareas-por-empleado', 'Tareas por Empleado', 'app.tareasPorEmpleadoLink', true)}</li>
            <li>{link('/informes/tareas-por-tipo', 'Tareas por Tipo', 'app.tareasPorTipoLink', true)}</li>
          </>
        )}
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
